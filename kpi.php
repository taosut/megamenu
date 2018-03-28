<?php
require_once('app/Mage.php');
require_once('vendor/autoload.php');
session_start();
$OAUTH2_CLIENT_ID = '825913221537-vkjocvv9ftch4tr175ubbnu95oars83u.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = '1s4EJHj5U0vvwzwDtOzLgU9o';
$GA_ID = "ga:143270307";
try {
    $client = new Google_Client();
    putenv('GOOGLE_APPLICATION_CREDENTIALS=google_serivice_key.json');
    $client->useApplicationDefaultCredentials();
    $client->setScopes('https://www.googleapis.com/auth/analytics');
    $analytics = new Google_Service_Analytics($client);
    /**
     * @var  Google_Service_Analytics_RealtimeData $data
     */
    $data = $analytics->data_realtime->get($GA_ID, 'rt:activeUsers');
    $htmlBody = $data->getTotalsForAllResults()['rt:activeUsers'];
} catch (Exception $e) {
    var_dump($e->getMessage());
    die();
}

try {
    Mage::app();
    $read = Mage::getSingleton('core/resource')->getConnection('core_read');
    /* Get daily order summary */
    $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
    $date = new DateTime('now', $timeZone);
    $to_daily = date('Y-m-d H:i:s', $date->getTimestamp()); //Plus 7 hour GMT+7
    $date->setTime("00", "00", "00");
    $from_daily = date('Y-m-d H:i:s', $date->getTimestamp());
    $from_2w_ago = date('Y-m-d H:i:s', strtotime("-2 week"));
    /**
     * @var Mage_Sales_Model_Resource_Order_Collection $daily_orders ;
     */

    $query = "select count(1) total_order from sales_flat_order a JOIN
                    (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                    and created_at > DATE_SUB('$from_daily',INTERVAL 1 month) and created_at < '$to_daily'
                    group by parent_id) b on a.entity_id = b.parent_id
                    where b.created_at > '$from_daily' and b.created_at < '$to_daily'
                    and a.status not in ('canceled', 'delivery_failed', 'no_product', 'reject', 'pending', 'stock_empty', 'stock_transfer', 'waiting_from_supplier')
                    and a.store_id in (20,21,23)
                      ";

    $total_daily_orders = round($read->fetchOne($query));


    //Pending confirmation
    $total_pending_amount = 0;

    $query = "select sum(grand_total) from sales_flat_order 
              where state = 'new' and store_id in (20,21,23)
              and entity_id not in (select parent_id from sales_flat_order_status_history where status = 'telephone_confirm' and created_at > '$from_2w_ago')";

    $total_pending_amount = round($read->fetchOne($query));


    /* End get daily order summary */
    /* Get monthly order summary */
    $date->modify('last day of this month');
    $date->setTime(23, 59, 59);
    $to_monthly = date('Y-m-d H:i:s', $date->getTimestamp());
    $date->modify('first day of this month');
    $date->setTime(23, 59, 59);
    $date->setTime(0, 0, 0);
    $from_monthly = date('Y-m-d H:i:s', $date->getTimestamp());

    $query = "select count(1) total_order from sales_flat_order a JOIN
                    (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                    and created_at > DATE_SUB('$from_monthly',INTERVAL 1 month) and created_at < '$to_monthly'
                    group by parent_id) b on a.entity_id = b.parent_id
                    where b.created_at > '$from_monthly' and b.created_at < '$to_monthly'
                    and a.status not in ('canceled', 'delivery_failed', 'no_product', 'reject', 'pending', 'stock_empty', 'stock_transfer', 'waiting_from_supplier')
                    and a.store_id in (20,21,23)
                      ";

    $total_monthly_orders = round($read->fetchOne($query));

    $query = "select sum(grand_total) total_order from sales_flat_order a JOIN
                    (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                    and created_at > DATE_SUB('$from_monthly',INTERVAL 1 month) and created_at < '$to_monthly'
                    group by parent_id) b on a.entity_id = b.parent_id
                    where b.created_at > '$from_monthly' and b.created_at < '$to_monthly' and a.affiliate_code is not null
                    and a.status not in ('canceled', 'delivery_failed', 'reject', 'pending', 'stock_transfer')
                    and a.store_id in (20,21,23)
                      ";

    $total_monthly_revenue = round($read->fetchOne($query));


    $query = "select sum(grand_total) total_order from sales_flat_order a JOIN
                    (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                    and created_at > DATE_SUB('$from_monthly',INTERVAL 1 month) and created_at < '$to_monthly'
                    group by parent_id) b on a.entity_id = b.parent_id
                    where b.created_at > '$from_monthly' and b.created_at < '$to_monthly' and a.affiliate_code is null
                    and a.status not in ('canceled', 'delivery_failed', 'reject', 'pending', 'stock_transfer')
                    and a.store_id in (20,21,23)
                      ";

    $total2_monthly_revenue = round($read->fetchOne($query));

    /* End get monthly order summary */

    /* Get target monthly orders and revenue */
    $target_monthly_orders = Mage::getModel('core/variable')->loadByCode('monthly_orders')->getValue('html');
    $target_monthly_revenue = Mage::getModel('core/variable')->loadByCode('monthly_revenue')->getValue('html');
    $target2_monthly_revenue = Mage::getModel('core/variable')->loadByCode('monthly_mkt_revenue')->getValue('html');
    /* End get target... */

} catch (Exception $e) {
    var_dump($e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Dashboard KPI</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60"/>
    <link rel="stylesheet"
          href="/skin/frontend/base/default/css/bootstrap.min.css">
    <script src="/skin/frontend/base/default/js/jquery.min.js"></script>
    <script src="/skin/frontend/base/default/js/bootstrap.min.js"></script>

    <style>
        .center {
            width: 100%;
            margin: 0 auto;
        }

        .table tbody > tr > td {
            vertical-align: middle;
            text-align: center;
        }

        .dk-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 44px;
        }

        .dk-table {
            height: 87vh;
        }

        .dk-order-daily {
            width: 33%;
            height: 50%;
            /*background: #D4D4FF;*/
            color: #70AD47;
        }

        .dk-order-daily-title, .dk-visit-title, .dk-order-pending-title {
            font-size: 40px;
        }

        .dk-order-daily-value, .dk-visit-value, .dk-order-pending-value {
            font-size: 120px;
        }

        .dk-order-pending {
            width: 33%;
            height: 50%;
            background: #FFCC00;
            color: #FF0000;
        }

        .dk-visit-current {
            background: #4285F3;
            color: #FFF;
        }

        .dk-value-monthly {
            background: #FFF2CC;
            height: 100%;
        }

        .dk-value-mtk-monthly {
            background: #c4eceb;
            height: 100%;
        }

        .dk-order-monthly-title {
            font-size: 40px;
            margin-left: 30px;
            color: #70AD47;
        }

        .dk-revenue-monthly-title {
            font-size: 40px;
            margin-left: 30px;
            color: #FF0000;
        }

        .dk-achieved-orders {
            font-size: 120px;
            color: #70AD47;
        }

        .dk-achieved-revenue {
            font-size: 120px;
            color: #FF0000;
        }

        .dk-required-orders, .dk-required-revenue {
            font-size: 80px;
            margin-left: 20px;
        }
    </style>

    <script>
        var aMillion = Math.pow(10, 6);
        var aBillion = Math.pow(10, 9);

        $(document).ready(function () {
            $("#dk-achieved-revenue").text(convertRevenue(<?php echo $total_monthly_revenue ?>));
            $("#dk-required-revenue").text('/ ' + convertRevenue(<?php echo $target_monthly_revenue ?>));
            $(".dk-order-pending-value").text(convertRevenue(<?php echo $total_pending_amount ?>));

            $("#dk2-achieved-revenue").text(convertRevenue(<?php echo $total2_monthly_revenue ?>));
            $("#dk2-required-revenue").text('/ ' + convertRevenue(<?php echo $target2_monthly_revenue ?>));
//            $(".dk2-order-pending-value").text(convertRevenue(<?php //echo $total_pending_amount ?>//));
        });

        function convertRevenue(revenue) {
            if (revenue < aBillion) {
                return Math.round(revenue /
                        aMillion) + ' tr';
            }
            else {
                return Math.round(revenue / aBillion * 1000) / 1000 + ' tỷ';
            }
        }
    </script>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T9QWVDV"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="center">
    <h2 class="dk-title">Sales Dashboard KPI</h2>
    <table class="table table-bordered dk-table">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td class="dk-order-daily">
                <p class="dk-order-daily-title">Số đơn trong ngày</p>
                <p class="dk-order-daily-value"><?php echo $total_daily_orders ?></p>
            </td>
            <td class="dk-order-pending">
                <p class="dk-order-pending-title">Số tiền chưa xác nhận</p>
                <p class="dk-order-pending-value"></p>
            </td>
            <td class="dk-visit-current">
                <p class="dk-visit-title">Số lượng visitor</p>
                <p class="dk-visit-value"><?= $htmlBody ?></p>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <table style="width:100%; height:100%">
                    <tr>
                        <td class="dk-value-monthly">
                            <div class="dk-revenue-monthly">
                                <p class="dk-revenue-monthly-title">Doanh số team Sale trong tháng</p>
                                <p>
                                    <span id="dk-achieved-revenue" class="dk-achieved-revenue"></span>
                                    <span id="dk-required-revenue" class="dk-required-revenue"></span>
                                </p>
                            </div>
                        </td>
                        <td class="dk-value-mtk-monthly">
                            <div class="dk-revenue-mkt-monthly">
                                <p class="dk-revenue-monthly-title">Doanh số team MKT trong tháng</p>
                                <p>
                                    <span id="dk2-achieved-revenue" class="dk-achieved-revenue"></span>
                                    <span id="dk2-required-revenue" class="dk-required-revenue"></span>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>

            </td>

        </tr>
        </tbody>
    </table>
</div>


</body>
</html>