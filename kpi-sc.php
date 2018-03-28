<?php
require_once('app/Mage.php');
require_once('vendor/autoload.php');


try {
    Mage::app();
    /* Get daily order summary */
    $time_zone = new DateTimeZone('Asia/Ho_Chi_Minh');
    $date = new DateTime('now', $time_zone);
    $to_daily = date('Y-m-d H:i:s', $date->getTimestamp()); //Plus 7 hour GMT+7
    $date->setTime("00", "00", "00");
    $from_daily = date('Y-m-d H:i:s',$date->getTimestamp());
    $from_2w_ago = date('Y-m-d H:i:s', strtotime("-2 week"));
    /**
     * @var Mage_Sales_Model_Resource_Order_Collection $daily_orders ;
     */

    /* End get daily order summary */
    /* Get monthly order summary */
    $date->modify('last day of this month');
    $date->setTime(23,59,59);
    $to_monthly = date('Y-m-d H:i:s', $date->getTimestamp());
    $date->modify('first day of this month');
    $date->setTime(23,59,59);
    $date->setTime(0,0,0);
    $from_monthly = date('Y-m-d H:i:s', $date->getTimestamp());

    
    $monthly_orders = Mage::getModel('sales/order')
        ->getCollection()
//        ->addFieldToFilter('status', 'canceled')
        ->addFieldToFilter('created_at', array('from' => $from_monthly, 'to' => $to_monthly))
        ->addFieldToFilter('store_id', array('in' => array(20, 21)))
        ->getSize();


    $monthly_canceled_orders = Mage::getModel('sales/order')
        ->getCollection()
        ->addFieldToFilter('status', 'canceled')
        ->addFieldToFilter('created_at', array('from' => $from_monthly, 'to' => $to_monthly))
        ->addFieldToFilter('store_id', array('in' => array(20, 21)))
        ->getSize();


    $monthly_confirmed_orders = Mage::getModel('sales/order')
        ->getCollection()
        ->addFieldToFilter('status', array(
                'nin' => array(
                    'canceled', 'delivery_failed', 'no_product', 'reject', 'pending', 'stock_empty', 'stock_transfer', 'waiting_from_supplier'
                )
            )
        )
        ->addFieldToFilter('created_at', array('from' => $from_monthly, 'to' => $to_monthly))
        ->addFieldToFilter('store_id', array('in' => array(20, 21)))
        ->getSize();

    $total_monthly_revenue = 0;
    $total_monthly_orders = 0;
    foreach ($monthly_orders as $order) {
        $itemTotal = $order['grand_total'];
        if ($itemTotal > 0) {
            $total_monthly_revenue += $itemTotal;
            $total_monthly_orders++;
        }
    }
    /* End get monthly order summary */

    /* Get target monthly orders and revenue */
    $target_monthly_orders = Mage::getModel('core/variable')->loadByCode('monthly_orders')->getValue('html');
    $target_monthly_revenue = Mage::getModel('core/variable')->loadByCode('monthly_revenue')->getValue('html');
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
        }

        .dk-order-monthly-title {
            float: left;
            font-size: 40px;
            margin-left: 30px;
            color: #70AD47;
        }

        .dk-revenue-monthly-title {
            float: left;
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
            $(".dk-achieved-revenue").text(convertRevenue(<?php echo $total_monthly_revenue ?>));
            $(".dk-required-revenue").text('/ ' + convertRevenue(<?php echo $target_monthly_revenue ?>));
            $(".dk-order-pending-value").text(convertRevenue(<?php echo $total_pending_amount ?>));
        });

        function convertRevenue(revenue) {
            if (revenue < aBillion) {
                return Math.round(revenue /
                        aMillion) + ' tr';
            }
            else {
                return Math.round(revenue / aBillion * 100) / 100 + ' tỷ';
            }
        }
    </script>
</head>
<body>

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
            <td colspan="3" class="dk-value-monthly">
                <div class="dk-order-monthly">
                    <span class="dk-order-monthly-title text-left">Số đơn trong tháng</span>
                    <span class="dk-achieved-orders"><?php echo $total_monthly_orders ?></span>
                    <span class="dk-required-orders">/ <?php echo $target_monthly_orders ?></span>
                </div>
                <div class="dk-revenue-monthly">
                    <span class="dk-revenue-monthly-title text-left">Doanh số trong tháng</span>
                    <span class="dk-achieved-revenue"></span>
                    <span class="dk-required-revenue"></span>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>


</body>
</html>