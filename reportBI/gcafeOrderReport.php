<?php
include('_header.php');
require_once APP_PATH . '/library/gcafe_report.php';

try {

    $allow_ip = array(
        '103.239.121.116',
    );

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = '103.239.121.116';
    }

    if (!in_array($ip, $allow_ip)) {
        throw new Exception('Not have permission.');
    }

    // $report = new gcafe_report();
    // $report->createIndex('log_url', 'IDX_LOG_URL_URL_ID', 'url_id');
    // die();


    $report = new gcafe_report();
    $reportDate = time() - 86400;
    $reportDate1 = $reportDate - 86400;
    $reportDate7 = $reportDate - 7 * 86400;

    $data = $report->getOrderReport($reportDate);
    $dataConfirm = $report->getOrderConfirmedReport(date('Y-m-d', $reportDate - 86400), date('Y-m-d', $reportDate));
    $data1 = $report->getOrderConfirmedReport(date('Y-m-d', $reportDate1 - 86400), date('Y-m-d', $reportDate1));
    $data7 = $report->getOrderConfirmedReport(date('Y-m-d', $reportDate7 - 86400), date('Y-m-d', $reportDate7));
    if(date('d', time()) == 1 || date('d', time()) == 31){
        $dataAll = $report->getOrderConfirmedReport(date('Y-m-t', time() - 32 * 86400), date('Y-m-d', $reportDate));
    }else{
        $dataAll = $report->getOrderConfirmedReport(date('Y-m-t', strtotime('last month')), date('Y-m-d', $reportDate));
    }

    $orderData = prepareData($data, $dataConfirm, $data1, $data7, $dataAll);
    $groupData = groupRegion($orderData);

    $from = 'GcafeShop Report <noreply@ved.com.vn>';
    $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
    $subject = '[GcafeShop Report] Báo cáo đơn hàng theo tỉnh ngày ' . date('d-m', $reportDate);
    $body = "Báo cáo ngày " . date('d-m-Y', $reportDate) . '<br/>';

    $body .= 'Report: ' . '<br/>';

    $body .= prepareTable($groupData);

    $mail = new PHPMailer;

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'noreplyved@gmail.com';                 // SMTP username
    $mail->Password = 'noreplyved!@#$';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;

    $mail->setFrom('noreply@ved.com.vn', 'GcafeShop Report');
    $mail->addAddress('linh.dt@teko.vn');     // Add a recipient
    $mail->addAddress('binh@teko.vn');
    $mail->addAddress('nhung.nt@teko.vn');
    $mail->addAddress('hiep.pn@teko.vn');
    $mail->addAddress('tuan.na@teko.vn');
    $mail->addAddress('dung.cc@teko.vn');
    $mail->addAddress('binh.nt@teko.vn');
    $mail->addAddress('vuong.nhx@teko.vn');
    $mail->addAddress('long.lk@teko.vn');
    $mail->addAddress('lien.ntt@teko.vn');
    $mail->addAddress('anh.pq@teko.vn');
    $mail->addAddress('long.nd@teko.vn');
    $mail->addAddress('thach.nh@teko.vn');
    $mail->addAddress('quan.thm@teko.vn');
    $mail->addAddress('tuyen.nv@teko.vn');
    $mail->addAddress('hung.nq@teko.vn');
    $mail->addAddress('nguyen.tp@teko.vn');
    $mail->addAddress('phuong.ht@teko.vn');
    $mail->addAddress('luan.dd@teko.vn');
    $mail->addAddress('thong.ln@teko.vn');
    $mail->addAddress('dong.nh@teko.vn');
    $mail->addAddress('tin.tt@teko.vn');
    $mail->addAddress('tung.nq@teko.vn');
    $mail->addAddress('hien.lt@teko.vn');
    $mail->addAddress('yen.nth@teko.vn');
    $mail->addAddress('thuong.nth@teko.vn');
    $mail->addAddress('trinh.nk@phongvu.vn');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;
    $mail->Body = $body;

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent at ' . date('d-m-Y H:i:s', time());
    }

    //Bao cao theo cat
//    $reportDate = time() - 86400;
//    $reportDate1 = $reportDate - 86400;
//    $reportDate7 = $reportDate - 7 * 86400;
//
//    $data = $report->getOrderReportByCat($reportDate);
//    $data1 = $report->getOrderReportByCat($reportDate1);
//    $data7 = $report->getOrderReportByCat($reportDate7);
//    $dataAll = $report->getCulmutiveCatReport($reportDate);
//
//    $orderData = prepareData($data, $data1, $data7, $dataAll);
//
//    $from = 'GcafeShop Report <noreply@ved.com.vn>';
//    $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
//    $subject = '[GcafeShop Report] Báo cáo đơn hàng theo CAT ngày ' . date('d-m', $reportDate);
//    $body = "Báo cáo ngày " . date('d-m-Y', $reportDate) . '<br/>';
//
//    $body .= 'Report: ' . '<br/>';
//
//    $body .= prepareTableByCat($orderData);
//
//    $mail = new PHPMailer;
//
//    $mail->isSMTP();                                      // Set mailer to use SMTP
//    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
//    $mail->SMTPAuth = true;                               // Enable SMTP authentication
//    $mail->Username = 'noreplyved@gmail.com';                 // SMTP username
//    $mail->Password = 'noreplyved!@#$';                           // SMTP password
//    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
//    $mail->Port = 587;
//
//    $mail->setFrom('noreply@ved.com.vn', 'GcafeShop Report');
//    $mail->addAddress('linh.dt@teko.vn');     // Add a recipient
//    $mail->addAddress('binh@teko.vn');
//    $mail->addAddress('nhung.nt@teko.vn');
//    $mail->addAddress('hiep.pn@teko.vn');
//    $mail->addAddress('tuan.na@teko.vn');
//    $mail->addAddress('dung.cc@teko.vn');
//    $mail->addAddress('binh.nt@teko.vn');
//    $mail->addAddress('vuong.nhx@teko.vn');
//    $mail->addAddress('long.lk@teko.vn');
//    $mail->addAddress('lien.ntt@teko.vn');
//    $mail->addAddress('anh.pq@teko.vn');
//    $mail->addAddress('long.nd@teko.vn');
//    $mail->addAddress('thach.nh@teko.vn');
//    $mail->addAddress('quan.thm@teko.vn');
//    $mail->addAddress('tuyen.nv@teko.vn');
//    $mail->addAddress('hung.nq@teko.vn');
//    $mail->addAddress('nguyen.tp@teko.vn');
//    $mail->addAddress('phuong.ht@teko.vn');
//    $mail->addAddress('luan.dd@teko.vn');
//    $mail->addAddress('thong.ln@teko.vn');
//    $mail->addAddress('dong.nh@teko.vn');
//    $mail->addAddress('tin.tt@teko.vn');
//    $mail->addAddress('tung.nq@teko.vn');
//    $mail->addAddress('hien.lt@teko.vn');
//    $mail->addAddress('yen.nth@teko.vn');
//
//    $mail->CharSet = 'UTF-8';
//    $mail->isHTML(true);                                  // Set email format to HTML
//
//    $mail->Subject = $subject;
//    $mail->Body = $body;
//
//    if (!$mail->send()) {
//        echo 'Message could not be sent.';
//        echo 'Mailer Error: ' . $mail->ErrorInfo;
//    } else {
//        echo 'Message has been sent at ' . date('d-m-Y H:i:s', time());
//    }


//    //Bao cao theo cat Tekshop
//    $reportDate = time() - 86400;
//    $reportDate1 = $reportDate - 86400;
//    $reportDate7 = $reportDate - 7 * 86400;
//
//    $data = $report->getSKUReportTekshopByCat($reportDate);
//    $data1 = $report->getSKUReportTekshopByCat($reportDate1);
//    $data7 = $report->getSKUReportTekshopByCat($reportDate7);
//    $dataAll = $report->getCulmutiveSKUCatTekshopReport($reportDate);
//
//    $orderData = prepareData($data, $data1, $data7, $dataAll);
//
//    $from = 'Tekshop Report <noreply@ved.com.vn>';
//    $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
//    $subject = '[Tekshop Report] Báo cáo ordered SKUs theo CAT ngày ' . date('d-m', $reportDate);
//    $body = "Báo cáo ngày " . date('d-m-Y', $reportDate) . '<br/>';
//
//    $body .= 'Report: ' . '<br/>';
//
//    $body .= prepareTableByCat($orderData);
//
//    $mail = new PHPMailer;
//
//    $mail->isSMTP();                                      // Set mailer to use SMTP
//    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
//    $mail->SMTPAuth = true;                               // Enable SMTP authentication
//    $mail->Username = 'noreplyved@gmail.com';                 // SMTP username
//    $mail->Password = 'noreplyved!@#$';                           // SMTP password
//    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
//    $mail->Port = 587;
//
//    $mail->setFrom('noreply@ved.com.vn', 'TekShop Report');
//    $mail->addAddress('linh.dt@teko.vn');     // Add a recipient
//    $mail->addAddress('binh@teko.vn');
//    $mail->addAddress('nhung.nt@teko.vn');
//    $mail->addAddress('hiep.pn@teko.vn');
//    $mail->addAddress('tuan.na@teko.vn');
//    $mail->addAddress('dung.cc@teko.vn');
//    $mail->addAddress('binh.nt@teko.vn');
//    $mail->addAddress('vuong.nhx@teko.vn');
//    $mail->addAddress('long.lk@teko.vn');
//    $mail->addAddress('lien.ntt@teko.vn');
//    $mail->addAddress('anh.pq@teko.vn');
//    $mail->addAddress('long.nd@teko.vn');
//    $mail->addAddress('thach.nh@teko.vn');
//    $mail->addAddress('quan.thm@teko.vn');
//    $mail->addAddress('tuyen.nv@teko.vn');
//    $mail->addAddress('hung.nq@teko.vn');
//    $mail->addAddress('nguyen.tp@teko.vn');
//    $mail->addAddress('phuong.ht@teko.vn');
//    $mail->addAddress('luan.dd@teko.vn');
//    $mail->addAddress('thong.ln@teko.vn');
//    $mail->addAddress('dong.nh@teko.vn');
//
//    $mail->CharSet = 'UTF-8';
//    $mail->isHTML(true);                                  // Set email format to HTML
//
//    $mail->Subject = $subject;
//    $mail->Body = $body;
//
//    if (!$mail->send()) {
//        echo 'Message could not be sent.';
//        echo 'Mailer Error: ' . $mail->ErrorInfo;
//    } else {
//        echo 'Message has been sent at ' . date('d-m-Y H:i:s', time());
//    }
//
//
//
//
//    //Bao cao don theo cat Tekshop
//    $reportDate = time() - 86400;
//    $reportDate1 = $reportDate - 86400;
//    $reportDate7 = $reportDate - 7 * 86400;
//
//    $data = $report->getOrderReportTekshopByCat($reportDate);
//    $dataConfirmed = $report->getOrderConfirmedReportTekshopByCat($reportDate);
//    $data1 = $report->getOrderConfirmedReportTekshopByCat($reportDate1);
//    $data7 = $report->getOrderConfirmedReportTekshopByCat($reportDate7);
//    $dataAll = $report->getCulmutiveCatTekshopReport($reportDate);
//
//    $orderData = prepareDataTekshop($data, $dataConfirmed, $data1, $data7, $dataAll);
//
//    $from = 'Tekshop Report <noreply@ved.com.vn>';
//    $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
//    $subject = '[Tekshop Report] Báo cáo đơn hàng theo CAT ngày ' . date('d-m', $reportDate);
//    $body = "Báo cáo ngày " . date('d-m-Y', $reportDate) . '<br/>';
//
//    $body .= 'Report: ' . '<br/>';
//
//    $body .= prepareTekOrderTableByCat($orderData);
//
//    $mail = new PHPMailer;
//
//    $mail->isSMTP();                                      // Set mailer to use SMTP
//    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
//    $mail->SMTPAuth = true;                               // Enable SMTP authentication
//    $mail->Username = 'noreplyved@gmail.com';                 // SMTP username
//    $mail->Password = 'noreplyved!@#$';                           // SMTP password
//    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
//    $mail->Port = 587;
//
//    $mail->setFrom('noreply@ved.com.vn', 'TekShop Report');
//    $mail->addAddress('linh.dt@teko.vn');     // Add a recipient
//    $mail->addAddress('binh@teko.vn');
//    $mail->addAddress('nhung.nt@teko.vn');
//    $mail->addAddress('hiep.pn@teko.vn');
//    $mail->addAddress('tuan.na@teko.vn');
//    $mail->addAddress('dung.cc@teko.vn');
//    $mail->addAddress('binh.nt@teko.vn');
//    $mail->addAddress('vuong.nhx@teko.vn');
//    $mail->addAddress('long.lk@teko.vn');
//    $mail->addAddress('lien.ntt@teko.vn');
//    $mail->addAddress('anh.pq@teko.vn');
//    $mail->addAddress('long.nd@teko.vn');
//    $mail->addAddress('thach.nh@teko.vn');
//    $mail->addAddress('quan.thm@teko.vn');
//    $mail->addAddress('tuyen.nv@teko.vn');
//    $mail->addAddress('hung.nq@teko.vn');
//    $mail->addAddress('nguyen.tp@teko.vn');
//    $mail->addAddress('phuong.ht@teko.vn');
//    $mail->addAddress('luan.dd@teko.vn');
//    $mail->addAddress('thong.ln@teko.vn');
//    $mail->addAddress('dong.nh@teko.vn');
//
//    $mail->CharSet = 'UTF-8';
//    $mail->isHTML(true);                                  // Set email format to HTML
//
//    $mail->Subject = $subject;
//    $mail->Body = $body;
//
//    if (!$mail->send()) {
//        echo 'Message could not be sent.';
//        echo 'Mailer Error: ' . $mail->ErrorInfo;
//    } else {
//        echo 'Message has been sent at ' . date('d-m-Y H:i:s', time());
//    }




    $reportDate = time() - 86400;
    $reportDate1 = $reportDate - 86400;
    $reportDate7 = $reportDate - 7 * 86400;

    $data = $report->getOrderExportReport($reportDate);
    $data1 = $report->getOrderExportReport($reportDate1);
    $data7 = $report->getOrderExportReport($reportDate7);
    $dataAll = $report->getCulmutiveOrderExportReport($reportDate);

    $orderData = prepareOrderExportData($data, $data1, $data7, $dataAll);
    $groupData = groupOrderExportRegion($orderData);

    $from = 'GcafeShop Report <noreply@ved.com.vn>';
    $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
    $subject = '[GcafeShop Report] Báo cáo đơn hàng xuất kho theo tỉnh ngày ' . date('d-m', $reportDate);
    $body = "Báo cáo đơn xuất kho ngày " . date('d-m-Y', $reportDate) . '<br/>';

    $body .= 'Report: ' . '<br/>';

    $body .= prepareOrderExportTable($groupData);

    $mail = new PHPMailer;

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'noreplyved@gmail.com';                 // SMTP username
    $mail->Password = 'noreplyved!@#$';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;

    $mail->setFrom('noreply@ved.com.vn', 'GcafeShop Report');
    $mail->addAddress('linh.dt@teko.vn');     // Add a recipient
    $mail->addAddress('phuong.ht@teko.vn');


    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;
    $mail->Body = $body;

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent at ' . date('d-m-Y H:i:s', time());
    }


// Bao cao san pham@ved	
    // $province = array('Gcafe Hà Nội', 'Gcafe Đà Nẵng');
    // $tableData = array();
    // foreach ($province as $value) {
    // $data = $report->getProductReport($reportDate, $value);
    // $data1 = $report->getProductReport($reportDate1, $value);
    // $data7 = $report->getProductReport($reportDate7, $value);

    // $tableData = prepareProductRowData($tableData, $value, $data,$data1,$data7);

    // }
    // $from = 'GcafeShop Report <noreply@ved.com.vn>';
    // $subject = '[GcafeShop Report] Báo cáo SKU ngày ' . date('d-m',$reportDate);
    $body = "Báo cáo ngày " . date('d-m-Y', $reportDate) . '<br/><br/><br/>';
    // $body.= prepareTableProductReport($tableData);


} catch (Exception $e) {
    echo $e->getMessage();
}
?>


<?php
function openRow($style = '')
{
    return '<tr style="' . $style . '">';
}

function writeString($string, $style = '', $colSpan = 0)
{
    return '<td ' . ($colSpan ? 'colspan="' . $colSpan . '"' : '') . ' style="text-align:center;' . $style . '">' . $string . '</td>';
}

function writeStringRight($string, $style = '', $colSpan = 0)
{
    return '<td ' . ($colSpan ? 'colspan="' . $colSpan . '"' : '') . ' style="text-align:right; padding-right:2px;' . $style . '">' . $string . '</td>';
}

function writeStringLeft($string, $style = '', $colSpan = 0)
{
    return '<td ' . ($colSpan ? 'colspan="' . $colSpan . '"' : '') . ' style="text-align:left;' . $style . '">' . $string . '</td>';
}

function closeRow()
{
    return '</tr>';
}

function printNum($value)
{
    return number_format($value, 0, ',', '.');
}

function printOrder($value)
{
    return $value ? number_format($value, 1, ',', '.') : 0;
}

function prepareData($data, $dataConfirm, $data1, $data7, $dataAll)
{
    $result = array();
    $total = array();
    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;

    foreach ($data as $key => $value) {
        $result[$key]['cat'] = $value['province'] ? $value['province'] : '';
        $result[$key]['order'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tUser += ($value['unique_users'] ? $value['unique_users'] : 0);
        $tView += ($value['views'] ? $value['views'] : 0);
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }
    $total['cat'] = 'Total';
    $total['user'] = $tUser;
    $total['view'] = $tView;
    $total['order'] = $tOrder;
    $total['abs'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv'] = $tGMV;

    $tOrder = 0;
    $tGMV = 0;

    foreach ($dataConfirm as $key => $value) {
        $result[$key]['orderc'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['absc'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmvc'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }
    $total['orderc'] = $tOrder;
    $total['absc'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmvc'] = $tGMV;

    $tOrder = 0;
    $tGMV = 0;

    foreach ($data1 as $key => $value) {
        $result[$key]['order1'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs1'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv1'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['order1'] = $tOrder;
    $total['abs1'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv1'] = $tGMV;

    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;
    foreach ($data7 as $key => $value) {
        $result[$key]['order7'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs7'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv7'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }

    $total['order7'] = $tOrder;
    $total['abs7'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv7'] = $tGMV;

    #cumulative
    $tOrder = 0;
    $tGMV = 0;

    foreach ($dataAll as $value) {
        foreach ($result as $key => $check) {
            if ($check['cat'] == $value['province']) {
                $result[$key]['orderAll'] = $value['orders'] ? $value['orders'] : 0;
                $result[$key]['absAll'] = $value['abs'] ? $value['abs'] / 1000 : 0;
                $result[$key]['gmvAll'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
            }
        }
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['orderAll'] = $tOrder;
    $total['absAll'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmvAll'] = $tGMV;


    array_unshift($result, $total);
    return $result;
}

function prepareTable($orderData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
  <tbody>
  <tr>
    <td rowspan=3 style="font-weight:bold; text-align:center">Province</td>
    <td colspan=15 style="font-weight:bold; text-align:center">Orders </td>
  </tr>
  <tr>
    <td colspan=3 style="font-weight:bold; text-align:center">D</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D (confirm)</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-1 (confirmed) </td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-7 (confirmed) </td>
	<td colspan=3 style="font-weight:bold; text-align:center">Lũy kế </td>
  </tr>
  <tr>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
	<td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
  </tr>
  ';

    foreach ($orderData as $value) {
        if ($value['cat'] == 'Total') {
            $body .= openRow("background:#CCCCCC");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderc']) ? $value['orderc'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absc']) ? $value['absc'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvc']) ? $value['gmvc'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else if ($value['cat'] == 'Miền Bắc' || $value['cat'] == 'Miền Trung' || $value['cat'] == 'Miền Nam') {
            $body .= openRow("background:#DDD");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderc']) ? $value['orderc'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absc']) ? $value['absc'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvc']) ? $value['gmvc'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else {
            $stores = array('Hà Nội', 'Bắc Giang', 'Bắc Ninh', 'Bình Dương', 'Hải Phòng', 'Tp. Hồ Chí Minh', 'Thanh Hóa', 'Vĩnh Phúc', 'Đà Nẵng');
            if (in_array($value['cat'], $stores)) {
                $body .= openRow("background:#bdd6ee");
            } else {
                $body .= openRow();
            }
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderc']) ? $value['orderc'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absc']) ? $value['absc'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmvc']) ? $value['gmvc'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; ");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; ");
            $body .= closeRow();
        }
    }

    $body .= '</tbody></table>';
    return $body;
}

function prepareProductReport($productData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
		<tbody>
		<tr>
			<td rowspan=2 style="font-weight:bold; text-align:center">Tỉnh/Cat</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Bàn phím</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Chuột </td>
			<td colspan=3 style="font-weight:bold; text-align:center">Tai nghe</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Màn hình</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Linh kiện </td>
			<td colspan=3 style="font-weight:bold; text-align:center">Khác </td>
		</tr>
		<tr>			
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>			
		</tr>
		';
    foreach ($productData as $key => $value) {
        $body .= openRow();
        $body .= writeStringLeft($key, "font-weight:bold;font-size:100%;");
        $body .= writeStringRight(printNum(isset($value['BP']) ? $value['BP'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['BP1']) ? $value['BP1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['BP7']) ? $value['BP7'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['C']) ? $value['C'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['C1']) ? $value['C1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['C7']) ? $value['C7'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['TN']) ? $value['TN'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['TN1']) ? $value['TN1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['TN7']) ? $value['TN7'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['MH']) ? $value['MH'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['MH1']) ? $value['MH1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['MH7']) ? $value['MH7'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['LK']) ? $value['LK'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['LK1']) ? $value['LK1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['LK7']) ? $value['LK7'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['K']) ? $value['K'] : 0), "background:#CCCCCC");
        $body .= writeStringRight(printNum(isset($value['K1']) ? $value['K1'] : 0), "");
        $body .= writeStringRight(printNum(isset($value['K7']) ? $value['K7'] : 0), "");
        $body .= closeRow();

    }
    $body .= '</tbody></table>';
    return $body;
}

function prepareProductRowData1($data, $data1, $data7)
{
    $result = array();
    foreach ($data as $key => $value) {
        switch (strtolower($value['name'])) {
            case 'bàn phím':
                $result['BP'] = $value['sku'] ? $value['sku'] : 0;
                $result['BP1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['BP7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
            case 'chuột':
                $result['C'] = $value['sku'] ? $value['sku'] : 0;
                $result['C1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['C7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
            case 'tai nghe':
                $result['TN'] = $value['sku'] ? $value['sku'] : 0;
                $result['TN1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['TN7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
            case 'màn hình':
                $result['MH'] = $value['sku'] ? $value['sku'] : 0;
                $result['MH1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['MH7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
            case 'linh kiện máy tính':
                $result['LK'] = $value['sku'] ? $value['sku'] : 0;
                $result['LK1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['LK7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
            case 'sản phẩm khác':
                $result['K'] = $value['sku'] ? $value['sku'] : 0;
                $result['K1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
                $result['K7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;
                break;
        }
    }
    return $result;
}

function prepareProductRowData($result, $province, $data, $data1, $data7)
{
    foreach ($data as $key => $value) {
        $result[strtolower($value['name'])][$province]['sku'] = $value['sku'] ? $value['sku'] : 0;
        $result[strtolower($value['name'])][$province]['sku1'] = $data1[$key]['sku'] ? $data1[$key]['sku'] : 0;
        $result[strtolower($value['name'])][$province]['sku7'] = $data7[$key]['sku'] ? $data7[$key]['sku'] : 0;

    }
    return $result;
}

function prepareTableProductReport($productData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
		<tbody>
		<tr>
			<td rowspan=2 style="font-weight:bold; text-align:center">Cat/Tinh</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Hà Nội</td>
			<td colspan=3 style="font-weight:bold; text-align:center">Đà Nẵng </td>
		</tr>
		<tr>			
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
			<td  style="font-weight:bold; text-align:center">D</td>
			<td  style="font-weight:bold; text-align:center">D-1</td>
			<td  style="font-weight:bold; text-align:center">D-7</td>
					
		</tr>
		';
    foreach ($productData as $key => $value) {
        $body .= openRow();
        $body .= writeStringLeft($key, "font-weight:bold;font-size:100%;");
        foreach ($value as $data) {
            $body .= writeStringRight(printNum(isset($data['sku']) ? $data['sku'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($data['sku1']) ? $data['sku1'] : 0));
            $body .= writeStringRight(printNum(isset($data['sku7']) ? $data['sku7'] : 0));
        }
        $body .= closeRow();
    }
    $body .= '</tbody></table>';
    return $body;
}

function prepareTableByCat($orderData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
  <tbody>
  <tr>
    <td rowspan=3 style="font-weight:bold; text-align:center">Cat</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Unique Visitors</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Page views </td>
    <td colspan=12 style="font-weight:bold; text-align:center">Orders </td>
  </tr>
  <tr>
    
    <td  style="font-weight:bold; text-align:center">D</td>
    <td  style="font-weight:bold; text-align:center">D-1</td>
    <td  style="font-weight:bold; text-align:center">D-7</td>
    <td  style="font-weight:bold; text-align:center">D</td>
    <td  style="font-weight:bold; text-align:center">D-1</td>
    <td  style="font-weight:bold; text-align:center">D-7</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-1 </td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-7 </td>
    <td colspan=3 style="font-weight:bold; text-align:center">Lũy kế </td>
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td  style="font-weight:bold; text-align:center">SKUs</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">SKUs</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">SKUs</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">SKUs</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
  </tr>
  ';

    foreach ($orderData as $value) {
        if ($value['cat'] == 'Total') {
            $body .= openRow("background:#CCCCCC");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['user']) ? $value['user'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['user1']) ? $value['user1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['user7']) ? $value['user7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['view']) ? $value['view'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['view1']) ? $value['view1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['view7']) ? $value['view7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else {
            if ((!isset($value['user']) || $value['user'] == 0) && (!isset($value['user1']) || $value['user1'] == 0) && (!isset($value['user7']) || $value['user7'] == 0))
                continue;
            $body .= openRow();
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['user']) ? $value['user'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['user1']) ? $value['user1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['user7']) ? $value['user7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['view']) ? $value['view'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['view1']) ? $value['view1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['view7']) ? $value['view7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red;");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red;");
            $body .= closeRow();
        }
    }

    $body .= '</tbody></table>';
    return $body;
}



function prepareTekOrderTableByCat($orderData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
  <tbody>
  <tr>
    <td rowspan=3 style="font-weight:bold; text-align:center">Cat</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Unique Visitors</td>
    <td colspan=15 style="font-weight:bold; text-align:center">Orders </td>
  </tr>
  <tr>
    <td  style="font-weight:bold; text-align:center">D</td>
    <td  style="font-weight:bold; text-align:center">D-1</td>
    <td  style="font-weight:bold; text-align:center">D-7</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D (All)</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D (Confirmed)</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-1 (Confirmed)</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-7 (Confirmed)</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Lũy kế </td>
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td  style="font-weight:bold; text-align:center">Orders</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Orders</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Orders</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Orders</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Orders</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
  </tr>
  ';

    foreach ($orderData as $value) {
        if ($value['cat'] == 'Total') {
            $body .= openRow("background:#CCCCCC");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['user']) ? $value['user'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['user1']) ? $value['user1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['user7']) ? $value['user7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderC']) ? $value['orderC'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absC']) ? $value['absC'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvC']) ? $value['gmvC'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else {
            if ((!isset($value['user']) || $value['user'] == 0) && (!isset($value['user1']) || $value['user1'] == 0) && (!isset($value['user7']) || $value['user7'] == 0))
                continue;
            $body .= openRow();
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['user']) ? $value['user'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['user1']) ? $value['user1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['user7']) ? $value['user7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderC']) ? $value['orderC'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absC']) ? $value['absC'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmvC']) ? $value['gmvC'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red;");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red;");
            $body .= closeRow();
        }
    }

    $body .= '</tbody></table>';
    return $body;
}


function groupRegion($orderData)
{
    $regions = array(
        'Miền Bắc' => array(
            'Hà Nội' => array('name' => 'Hà Nội'),
            'Hà Tây' => array('name' => 'Hà Tây'),
            'Hải Phòng' => array('name' => 'Hải Phòng'),
            'Bắc Ninh' => array('name' => 'Bắc Ninh'),
            'Thanh Hóa' => array('name' => 'Thanh Hóa'),
            'Vĩnh Phúc' => array('name' => 'Vĩnh Phúc'),
            'Thái Bình' => array('name' => 'Thái Bình'),
            'Hải Dương' => array('name' => 'Hải Dương'),
            'Nam Định' => array('name' => 'Nam Định'),
            'Nghệ An' => array('name' => 'Nghệ An'),
            'Quảng Ninh' => array('name' => 'Quảng Ninh'),
            'Bắc Giang' => array('name' => 'Bắc Giang'),
            'Hưng Yên' => array('name' => 'Hưng Yên'),
            'Thái Nguyên' => array('name' => 'Thái Nguyên'),
            'Sơn La' => array('name' => 'Sơn La'),
            'Hà Tĩnh' => array('name' => 'Hà Tĩnh'),
            'Phú Thọ' => array('name' => 'Phú Thọ'),
            'Ninh Bình' => array('name' => 'Ninh Bình'),
            'Hà Nam' => array('name' => 'Hà Nam'),
            'Tuyên Quang' => array('name' => 'Tuyên Quang'),
            'Hòa Bình' => array('name' => 'Hòa Bình'),
            'Lạng Sơn' => array('name' => 'Lạng Sơn'),
            'Yên Bái' => array('name' => 'Yên Bái'),
            'Lào Cai' => array('name' => 'Lào Cai'),
            'Hà Giang' => array('name' => 'Hà Giang'),
            'Lai Châu' => array('name' => 'Lai Châu'),
            'Cao Bằng' => array('name' => 'Cao Bằng'),
            'Điện Biên' => array('name' => 'Điện Biên'),
            'Bắc Kạn' => array('name' => 'Bắc Kạn')),
        'Miền Trung' => array(
            'Đăk Lăk' => array('name' => 'Đăk Lăk'),
            'Đà Nẵng' => array('name' => 'Đà Nẵng'),
            'Lâm Đồng' => array('name' => 'Lâm Đồng'),
            'Quảng Nam' => array('name' => 'Quảng Nam'),
            'Thừa Thiên-Huế' => array('name' => 'Thừa Thiên-Huế'),
            'Gia Lai' => array('name' => 'Gia Lai'),
            'Khánh Hòa' => array('name' => 'Khánh Hòa'),
            'Bình Thuận' => array('name' => 'Bình Thuận'),
            'Quảng Ngãi' => array('name' => 'Quảng Ngãi'),
            'Đăk Nông' => array('name' => 'Đăk Nông'),
            'Bình Định' => array('name' => 'Bình Định'),
            'Phú Yên' => array('name' => 'Phú Yên'),
            'Ninh Thuận' => array('name' => 'Ninh Thuận'),
            'Quảng Bình' => array('name' => 'Quảng Bình'),
            'Kon Tum' => array('name' => 'Kon Tum'),
            'Quảng Trị' => array('name' => 'Quảng Trị')),
        'Miền Nam' => array(
            'Tp. Hồ Chí Minh' => array('name' => 'Tp. Hồ Chí Minh'),
            'Bình Dương' => array('name' => 'Bình Dương'),
            'Đồng Nai' => array('name' => 'Đồng Nai'),
            'Bà Rịa - Vũng Tàu' => array('name' => 'Bà Rịa - Vũng Tàu'),
            'Cần Thơ' => array('name' => 'Cần Thơ'),
            'Đồng Tháp' => array('name' => 'Đồng Tháp'),
            'An Giang' => array('name' => 'An Giang'),
            'Tây Ninh' => array('name' => 'Tây Ninh'),
            'Long An' => array('name' => 'Long An'),
            'Tiền Giang' => array('name' => 'Tiền Giang'),
            'Kiên Giang' => array('name' => 'Kiên Giang'),
            'Bến Tre' => array('name' => 'Bến Tre'),
            'Vĩnh Long' => array('name' => 'Vĩnh Long'),
            'Bình Phước' => array('name' => 'Bình Phước'),
            'Cà Mau' => array('name' => 'Cà Mau'),
            'Bạc Liêu' => array('name' => 'Bạc Liêu'),
            'Hậu Giang' => array('name' => 'Hậu Giang'),
            'Sóc Trăng' => array('name' => 'Sóc Trăng'),
            'Trà Vinh' => array('name' => 'Trà Vinh'))
    );

    $groupOrderData = array();
    $groupOrderData[] = $orderData[0];

    foreach ($regions as $region => $provinces) {
        $groupOrderData[$region] = array('cat' => $region);
        foreach ($provinces as $key => $province) {
            foreach ($orderData as $value) {
                if ($key == $value['cat']) {
                    $groupOrderData[] = $value;
                    $groupOrderData[$region]['order'] = isset($groupOrderData[$region]['order']) ? $groupOrderData[$region]['order'] + ($value['order'] ? $value['order'] : 0) : ($value['order'] ? $value['order'] : 0);
                    $groupOrderData[$region]['gmv'] = isset($groupOrderData[$region]['gmv']) ? $groupOrderData[$region]['gmv'] + ($value['gmv'] ? $value['gmv'] : 0) : ($value['gmv'] ? $value['gmv'] : 0);
                    $groupOrderData[$region]['abs'] = isset($groupOrderData[$region]['gmv']) && isset($groupOrderData[$region]['order']) && $groupOrderData[$region]['order'] > 0 ? $groupOrderData[$region]['gmv'] / $groupOrderData[$region]['order'] : 0;

                    $groupOrderData[$region]['orderc'] = isset($groupOrderData[$region]['orderc']) ? $groupOrderData[$region]['orderc'] + ($value['orderc'] ? $value['orderc'] : 0) : ($value['orderc'] ? $value['orderc'] : 0);
                    $groupOrderData[$region]['gmvc'] = isset($groupOrderData[$region]['gmvc']) ? $groupOrderData[$region]['gmvc'] + ($value['gmvc'] ? $value['gmvc'] : 0) : ($value['gmvc'] ? $value['gmvc'] : 0);
                    $groupOrderData[$region]['absc'] = isset($groupOrderData[$region]['gmvc']) && isset($groupOrderData[$region]['orderc']) && $groupOrderData[$region]['orderc'] > 0 ? $groupOrderData[$region]['gmvc'] / $groupOrderData[$region]['orderc'] : 0;

                    $groupOrderData[$region]['order1'] = isset($groupOrderData[$region]['order1']) ? $groupOrderData[$region]['order1'] + ($value['order1'] ? $value['order1'] : 0) : ($value['order1'] ? $value['order1'] : 0);
                    $groupOrderData[$region]['gmv1'] = isset($groupOrderData[$region]['gmv1']) ? $groupOrderData[$region]['gmv1'] + ($value['gmv1'] ? $value['gmv1'] : 0) : ($value['gmv1'] ? $value['gmv1'] : 0);
                    $groupOrderData[$region]['abs1'] = isset($groupOrderData[$region]['gmv1']) && isset($groupOrderData[$region]['order1']) && $groupOrderData[$region]['order1'] > 0 ? $groupOrderData[$region]['gmv1'] / $groupOrderData[$region]['order1'] : 0;

                    $groupOrderData[$region]['order7'] = isset($groupOrderData[$region]['order7']) ? $groupOrderData[$region]['order7'] + ($value['order7'] ? $value['order7'] : 0) : ($value['order7'] ? $value['order7'] : 0);
                    $groupOrderData[$region]['gmv7'] = isset($groupOrderData[$region]['gmv7']) ? $groupOrderData[$region]['gmv7'] + ($value['gmv7'] ? $value['gmv7'] : 0) : ($value['gmv7'] ? $value['gmv7'] : 0);
                    $groupOrderData[$region]['abs7'] = isset($groupOrderData[$region]['gmv7']) && isset($groupOrderData[$region]['order7']) && $groupOrderData[$region]['order7'] > 0 ? $groupOrderData[$region]['gmv7'] / $groupOrderData[$region]['order7'] : 0;

                    $groupOrderData[$region]['orderAll'] = isset($groupOrderData[$region]['orderAll']) ? $groupOrderData[$region]['orderAll'] + ($value['orderAll'] ? $value['orderAll'] : 0) : ($value['orderAll'] ? $value['orderAll'] : 0);
                    $groupOrderData[$region]['gmvAll'] = isset($groupOrderData[$region]['gmvAll']) ? $groupOrderData[$region]['gmvAll'] + ($value['gmvAll'] ? $value['gmvAll'] : 0) : ($value['gmvAll'] ? $value['gmvAll'] : 0);
                    $groupOrderData[$region]['absAll'] = isset($groupOrderData[$region]['gmvAll']) && isset($groupOrderData[$region]['orderAll']) && $groupOrderData[$region]['orderAll'] > 0 ? $groupOrderData[$region]['gmvAll'] / $groupOrderData[$region]['orderAll'] : 0;
                    break;
                }
            }
        }
    }
    return $groupOrderData;

}

function prepareOrderExportData($data, $data1, $data7, $dataAll)
{
    $result = array();
    $total = array();
    $tOrder = 0;
    $tGMV = 0;

    foreach ($data as $key => $value) {
        $result[$key]['cat'] = $value['province'] ? $value['province'] : '';
        $result[$key]['order'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }
    $total['cat'] = 'Total';
    $total['order'] = $tOrder;
    $total['abs'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv'] = $tGMV;

    $tOrder = 0;
    $tGMV = 0;

    foreach ($data1 as $key => $value) {
        $result[$key]['order1'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs1'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv1'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['order1'] = $tOrder;
    $total['abs1'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv1'] = $tGMV;

    $tOrder = 0;
    $tGMV = 0;
    foreach ($data7 as $key => $value) {
        $result[$key]['order7'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs7'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv7'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['order7'] = $tOrder;
    $total['abs7'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv7'] = $tGMV;

    #cumulative
    $tOrder = 0;
    $tGMV = 0;

    foreach ($dataAll as $value) {
        foreach ($result as $key => $check) {
            if ($check['cat'] == $value['province']) {
                $result[$key]['orderAll'] = $value['orders'] ? $value['orders'] : 0;
                $result[$key]['absAll'] = $value['abs'] ? $value['abs'] / 1000 : 0;
                $result[$key]['gmvAll'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
            }
        }
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['orderAll'] = $tOrder;
    $total['absAll'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmvAll'] = $tGMV;


    array_unshift($result, $total);
    return $result;
}

function prepareOrderExportTable($orderData)
{
    $body = '<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
  <tbody>
  <tr>
    <td rowspan=3 style="font-weight:bold; text-align:center">Province</td>
    <td colspan=12 style="font-weight:bold; text-align:center">Orders </td>
  </tr>
  <tr>
    <td colspan=3 style="font-weight:bold; text-align:center">D</td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-1 </td>
    <td colspan=3 style="font-weight:bold; text-align:center">D-7 </td>
	<td colspan=3 style="font-weight:bold; text-align:center">Lũy kế </td>
  </tr>
  <tr>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
    <td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
	<td  style="font-weight:bold; text-align:center">Order</td>
    <td  style="font-weight:bold; text-align:center">ABS(K)</td>
    <td  style="font-weight:bold; text-align:center">GMV(K)</td>
  </tr>
  ';

    foreach ($orderData as $value) {
        if ($value['cat'] == 'Total') {
            $body .= openRow("background:#CCCCCC");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else if ($value['cat'] == 'Miền Bắc' || $value['cat'] == 'Miền Trung' || $value['cat'] == 'Miền Nam') {
            $body .= openRow("background:#DDD");
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; font-weight:bold;font-size:100%");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; font-weight:bold;font-size:100%;");
            $body .= closeRow();
        } else {
            $stores = array('Hà Nội', 'Bắc Giang', 'Bắc Ninh', 'Bình Dương', 'Hải Phòng', 'Tp. Hồ Chí Minh', 'Thanh Hóa', 'Vĩnh Phúc', 'Đà Nẵng');
            if (in_array($value['cat'], $stores)) {
                $body .= openRow("background:#bdd6ee");
            } else {
                $body .= openRow();
            }
            $body .= writeStringLeft($value['cat'], "font-weight:bold;font-size:100%;");
            $body .= writeStringRight(printNum(isset($value['order']) ? $value['order'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs']) ? $value['abs'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv']) ? $value['gmv'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order1']) ? $value['order1'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs1']) ? $value['abs1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv1']) ? $value['gmv1'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['order7']) ? $value['order7'] : 0), "background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['abs7']) ? $value['abs7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['gmv7']) ? $value['gmv7'] : 0), "");
            $body .= writeStringRight(printNum(isset($value['orderAll']) ? $value['orderAll'] : 0), "color:red; background:#CCCCCC");
            $body .= writeStringRight(printNum(isset($value['absAll']) ? $value['absAll'] : 0), "color:red; ");
            $body .= writeStringRight(printNum(isset($value['gmvAll']) ? $value['gmvAll'] : 0), "color:red; ");
            $body .= closeRow();
        }
    }

    $body .= '</tbody></table>';
    return $body;
}


function groupOrderExportRegion($orderData)
{
    $regions = array(
        'Miền Bắc' => array(
            'Hà Nội' => array('name' => 'Hà Nội'),
            'Hà Tây' => array('name' => 'Hà Tây'),
            'Hải Phòng' => array('name' => 'Hải Phòng'),
            'Bắc Ninh' => array('name' => 'Bắc Ninh'),
            'Thanh Hóa' => array('name' => 'Thanh Hóa'),
            'Vĩnh Phúc' => array('name' => 'Vĩnh Phúc'),
            'Thái Bình' => array('name' => 'Thái Bình'),
            'Hải Dương' => array('name' => 'Hải Dương'),
            'Nam Định' => array('name' => 'Nam Định'),
            'Nghệ An' => array('name' => 'Nghệ An'),
            'Quảng Ninh' => array('name' => 'Quảng Ninh'),
            'Bắc Giang' => array('name' => 'Bắc Giang'),
            'Hưng Yên' => array('name' => 'Hưng Yên'),
            'Thái Nguyên' => array('name' => 'Thái Nguyên'),
            'Sơn La' => array('name' => 'Sơn La'),
            'Hà Tĩnh' => array('name' => 'Hà Tĩnh'),
            'Phú Thọ' => array('name' => 'Phú Thọ'),
            'Ninh Bình' => array('name' => 'Ninh Bình'),
            'Hà Nam' => array('name' => 'Hà Nam'),
            'Tuyên Quang' => array('name' => 'Tuyên Quang'),
            'Hòa Bình' => array('name' => 'Hòa Bình'),
            'Lạng Sơn' => array('name' => 'Lạng Sơn'),
            'Yên Bái' => array('name' => 'Yên Bái'),
            'Lào Cai' => array('name' => 'Lào Cai'),
            'Hà Giang' => array('name' => 'Hà Giang'),
            'Lai Châu' => array('name' => 'Lai Châu'),
            'Cao Bằng' => array('name' => 'Cao Bằng'),
            'Điện Biên' => array('name' => 'Điện Biên'),
            'Bắc Kạn' => array('name' => 'Bắc Kạn')),
        'Miền Trung' => array(
            'Đăk Lăk' => array('name' => 'Đăk Lăk'),
            'Đà Nẵng' => array('name' => 'Đà Nẵng'),
            'Lâm Đồng' => array('name' => 'Lâm Đồng'),
            'Quảng Nam' => array('name' => 'Quảng Nam'),
            'Thừa Thiên-Huế' => array('name' => 'Thừa Thiên-Huế'),
            'Gia Lai' => array('name' => 'Gia Lai'),
            'Khánh Hòa' => array('name' => 'Khánh Hòa'),
            'Bình Thuận' => array('name' => 'Bình Thuận'),
            'Quảng Ngãi' => array('name' => 'Quảng Ngãi'),
            'Đăk Nông' => array('name' => 'Đăk Nông'),
            'Bình Định' => array('name' => 'Bình Định'),
            'Phú Yên' => array('name' => 'Phú Yên'),
            'Ninh Thuận' => array('name' => 'Ninh Thuận'),
            'Quảng Bình' => array('name' => 'Quảng Bình'),
            'Kon Tum' => array('name' => 'Kon Tum'),
            'Quảng Trị' => array('name' => 'Quảng Trị')),
        'Miền Nam' => array(
            'Tp. Hồ Chí Minh' => array('name' => 'Tp. Hồ Chí Minh'),
            'Bình Dương' => array('name' => 'Bình Dương'),
            'Đồng Nai' => array('name' => 'Đồng Nai'),
            'Bà Rịa - Vũng Tàu' => array('name' => 'Bà Rịa - Vũng Tàu'),
            'Cần Thơ' => array('name' => 'Cần Thơ'),
            'Đồng Tháp' => array('name' => 'Đồng Tháp'),
            'An Giang' => array('name' => 'An Giang'),
            'Tây Ninh' => array('name' => 'Tây Ninh'),
            'Long An' => array('name' => 'Long An'),
            'Tiền Giang' => array('name' => 'Tiền Giang'),
            'Kiên Giang' => array('name' => 'Kiên Giang'),
            'Bến Tre' => array('name' => 'Bến Tre'),
            'Vĩnh Long' => array('name' => 'Vĩnh Long'),
            'Bình Phước' => array('name' => 'Bình Phước'),
            'Cà Mau' => array('name' => 'Cà Mau'),
            'Bạc Liêu' => array('name' => 'Bạc Liêu'),
            'Hậu Giang' => array('name' => 'Hậu Giang'),
            'Sóc Trăng' => array('name' => 'Sóc Trăng'),
            'Trà Vinh' => array('name' => 'Trà Vinh'))
    );

    $groupOrderData = array();
    $groupOrderData[] = $orderData[0];

    foreach ($regions as $region => $provinces) {
        $groupOrderData[$region] = array('cat' => $region);
        foreach ($provinces as $key => $province) {
            foreach ($orderData as $value) {
                if ($key == $value['cat']) {
                    $groupOrderData[] = $value;
                    $groupOrderData[$region]['order'] = isset($groupOrderData[$region]['order']) ? $groupOrderData[$region]['order'] + ($value['order'] ? $value['order'] : 0) : ($value['order'] ? $value['order'] : 0);
                    $groupOrderData[$region]['gmv'] = isset($groupOrderData[$region]['gmv']) ? $groupOrderData[$region]['gmv'] + ($value['gmv'] ? $value['gmv'] : 0) : ($value['gmv'] ? $value['gmv'] : 0);
                    $groupOrderData[$region]['abs'] = isset($groupOrderData[$region]['gmv']) && isset($groupOrderData[$region]['order']) && $groupOrderData[$region]['order'] > 0 ? $groupOrderData[$region]['gmv'] / $groupOrderData[$region]['order'] : 0;

                    $groupOrderData[$region]['order1'] = isset($groupOrderData[$region]['order1']) ? $groupOrderData[$region]['order1'] + ($value['order1'] ? $value['order1'] : 0) : ($value['order1'] ? $value['order1'] : 0);
                    $groupOrderData[$region]['gmv1'] = isset($groupOrderData[$region]['gmv1']) ? $groupOrderData[$region]['gmv1'] + ($value['gmv1'] ? $value['gmv1'] : 0) : ($value['gmv1'] ? $value['gmv1'] : 0);
                    $groupOrderData[$region]['abs1'] = isset($groupOrderData[$region]['gmv1']) && isset($groupOrderData[$region]['order1']) && $groupOrderData[$region]['order1'] > 0 ? $groupOrderData[$region]['gmv1'] / $groupOrderData[$region]['order1'] : 0;

                    $groupOrderData[$region]['order7'] = isset($groupOrderData[$region]['order7']) ? $groupOrderData[$region]['order7'] + ($value['order7'] ? $value['order7'] : 0) : ($value['order7'] ? $value['order7'] : 0);
                    $groupOrderData[$region]['gmv7'] = isset($groupOrderData[$region]['gmv7']) ? $groupOrderData[$region]['gmv7'] + ($value['gmv7'] ? $value['gmv7'] : 0) : ($value['gmv7'] ? $value['gmv7'] : 0);
                    $groupOrderData[$region]['abs7'] = isset($groupOrderData[$region]['gmv7']) && isset($groupOrderData[$region]['order7']) && $groupOrderData[$region]['order7'] > 0 ? $groupOrderData[$region]['gmv7'] / $groupOrderData[$region]['order7'] : 0;

                    $groupOrderData[$region]['orderAll'] = isset($groupOrderData[$region]['orderAll']) ? $groupOrderData[$region]['orderAll'] + ($value['orderAll'] ? $value['orderAll'] : 0) : ($value['orderAll'] ? $value['orderAll'] : 0);
                    $groupOrderData[$region]['gmvAll'] = isset($groupOrderData[$region]['gmvAll']) ? $groupOrderData[$region]['gmvAll'] + ($value['gmvAll'] ? $value['gmvAll'] : 0) : ($value['gmvAll'] ? $value['gmvAll'] : 0);
                    $groupOrderData[$region]['absAll'] = isset($groupOrderData[$region]['gmvAll']) && isset($groupOrderData[$region]['orderAll']) && $groupOrderData[$region]['orderAll'] > 0 ? $groupOrderData[$region]['gmvAll'] / $groupOrderData[$region]['orderAll'] : 0;
                    break;
                }
            }
        }
    }
    return $groupOrderData;

}

function prepareDataTekshop($data, $dataConfirmed, $data1, $data7, $dataAll)
{
    $result = array();
    $total = array();
    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;

    foreach ($data as $key => $value) {
        $result[$key]['cat'] = $value['province'] ? $value['province'] : '';
        $result[$key]['user'] = $value['unique_users'] ? $value['unique_users'] : 0;
        $result[$key]['view'] = $value['views'] ? $value['views'] : 0;
        $result[$key]['order'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tUser += ($value['unique_users'] ? $value['unique_users'] : 0);
        $tView += ($value['views'] ? $value['views'] : 0);
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }
    $total['cat'] = 'Total';
    $total['user'] = $tUser;
    $total['view'] = $tView;
    $total['order'] = $tOrder;
    $total['abs'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv'] = $tGMV;

    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;

    foreach ($dataConfirmed as $key => $value) {
        $result[$key]['orderC'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['absC'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmvC'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }

    $total['orderC'] = $tOrder;
    $total['absC'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmvC'] = $tGMV;

    foreach ($data1 as $key => $value) {
        $result[$key]['user1'] = $value['unique_users'] ? $value['unique_users'] : 0;
        $result[$key]['view1'] = $value['views'] ? $value['views'] : 0;
        $result[$key]['order1'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs1'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv1'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tUser += ($value['unique_users'] ? $value['unique_users'] : 0);
        $tView += ($value['views'] ? $value['views'] : 0);
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['user1'] = $tUser;
    $total['view1'] = $tView;
    $total['order1'] = $tOrder;
    $total['abs1'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv1'] = $tGMV;

    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;
    foreach ($data7 as $key => $value) {
        $result[$key]['user7'] = $value['unique_users'] ? $value['unique_users'] : 0;
        $result[$key]['view7'] = $value['views'] ? $value['views'] : 0;
        $result[$key]['order7'] = $value['orders'] ? $value['orders'] : 0;
        $result[$key]['abs7'] = $value['abs'] ? $value['abs'] / 1000 : 0;
        $result[$key]['gmv7'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
        $tUser += ($value['unique_users'] ? $value['unique_users'] : 0);
        $tView += ($value['views'] ? $value['views'] : 0);
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }

    $total['user7'] = $tUser;
    $total['view7'] = $tView;
    $total['order7'] = $tOrder;
    $total['abs7'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmv7'] = $tGMV;

    #cumulative
    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;

    foreach ($dataAll as $value) {
        foreach ($result as $key => $check) {
            if ($check['cat'] == $value['province']) {
                $result[$key]['orderAll'] = $value['orders'] ? $value['orders'] : 0;
                $result[$key]['absAll'] = $value['abs'] ? $value['abs'] / 1000 : 0;
                $result[$key]['gmvAll'] = $value['gmv'] ? $value['gmv'] / 1000 : 0;
            }
        }
        $tOrder += ($value['orders'] ? $value['orders'] : 0);
        $tGMV += ($value['gmv'] ? $value['gmv'] / 1000 : 0);
    }


    $total['orderAll'] = $tOrder;
    $total['absAll'] = (int)$tGMV / ($tOrder ? $tOrder : 1);
    $total['gmvAll'] = $tGMV;


    array_unshift($result, $total);
    return $result;
}


?>