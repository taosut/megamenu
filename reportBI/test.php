<?php
include('_header.php');
require_once APP_PATH.'/library/gcafe_report.php'; 

try {

  $report = new gcafe_report();

  $reportDate = time() - 86400;
  $reportDate1 = $reportDate - 86400;
  $reportDate7 = $reportDate - 7*86400;

  // $data = $report->getOrderReport($reportDate,'Store Đà Nẵng');
  // $data1 = $report->getOrderReport($reportDate1,'Store Đà Nẵng');
  // $data7 = $report->getOrderReport($reportDate7,'Store Đà Nẵng');

  // $orderData = prepareData($data,$data1,$data7);
  // //var_dump($orderData);die();


  $from = 'Gcafe Report <noreply@ved.com.vn>';
  $to = 'tranlinh.do@ved.com.vn, linhdt86@gmail.com';
  $subject = '[Gcafe Report] Báo cáo đơn hàng ngày ' . date('d-m',$reportDate);
  $body = "Báo cáo ngày " . date('d-m-Y',$reportDate) . '<br/>';

  $body.= 'Hà Nội: ' . '<br/>';

  // $body.= prepareTable($orderData);

  // $data = $report->getOrderReport($reportDate,'Store Đà Nẵng');
  // $data1 = $report->getOrderReport($reportDate1,'Store Đà Nẵng');
  // $data7 = $report->getOrderReport($reportDate7,'Store Đà Nẵng');

  // $orderData = prepareData($data,$data1,$data7);

  $body.= '<br/><br/><br/>';
  $body.= 'Đà Nẵng: ' . '<br/>';

  // $body.= prepareTable($orderData);


  $headers = array(
      'From' => $from,
      'To' => $to,
      'Subject' => $subject,
      'MIME-Version' => 1,
      'Content-type' => 'text/html;charset=utf-8'
  );
	echo "linh";
	
	$mail = new PHPMailer;
	
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'noreplyved@gmail.com';                 // SMTP username
	$mail->Password = 'noreplyved!@#$';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;   

	$mail->setFrom('noreply@ved.com.vn', 'Gcafe Report');
	$mail->addAddress('tranlinh.do@ved.com.vn');     // Add a recipient
	$mail->addAddress('linhdt86@gmail.com');               // Name is optional
	$mail->CharSet = 'UTF-8';
	$mail->isHTML(true);                                  // Set email format to HTML

	$mail->Subject = $subject;
	$mail->Body    = $body;

	if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
			echo 'Message has been sent';
	}	


  // $smtp = Mail::factory('smtp', array(
          // 'host' => 'smtp.gmail.com',
          // 'port' => '465',
          // 'auth' => true,
          // 'username' => 'noreplyved@gmail.com',
          // 'password' => 'noreplyved!@#$',
      // ));

  // $mail = $smtp->send($to, $headers, $body);

  // if (PEAR::isError($mail)) {
      // echo('<p>' . $mail->getMessage() . '</p>');
  // } else {
      // echo('<p>Message successfully sent!</p>');
  // }
  
  
  
  

}
catch (Exception $e) {
  echo 1;
  var_dump($e) ;die();
}
?>



<?php
  function openRow($style=''){
    return '<tr style="'. $style . '">';
  }
  
  function writeString($string,$style='',$colSpan=0){
    return '<td '. ($colSpan?'colspan="'. $colSpan .'"':'') .' style="text-align:center;' . $style . '">' . $string . '</td>';
  }
  
  function writeStringRight($string,$style='',$colSpan=0){
    return '<td '. ($colSpan?'colspan="'. $colSpan .'"':'') .' style="text-align:right;' . $style . '">' . $string . '</td>';
  }
  
  function writeStringLeft($string,$style='',$colSpan=0){
   return '<td '. ($colSpan?'colspan="'. $colSpan .'"':'') .' style="text-align:left;' . $style . '">' . $string . '</td>';
  }
  function closeRow(){
    return '</tr>';
  }
  
  function printNum($value){
    return number_format($value, 0, ',', '.');
  }

  function prepareData($data, $data1, $data7){
    $result = array();
    $total = array();
    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;
    
    foreach ($data as $key => $value) {
      $result[$key]['cat'] = $value['value']?$value['value']:0;
      $result[$key]['user'] = $value['unique_users']?$value['unique_users']:0;
      $result[$key]['view'] = $value['views']?$value['views']:0;
      $result[$key]['order'] = $value['orders']?$value['orders']:0;
      $result[$key]['abs'] = $value['abs']?$value['abs']:0;
      $result[$key]['gmv'] = $value['gmv']?$value['gmv']:0;
      $tUser+= ($value['unique_users']?$value['unique_users']:0);
      $tView += ($value['views']?$value['views']:0);
      $tOrder += ( $value['orders']?$value['orders']:0);
      $tGMV +=( $value['gmv']?$value['gmv']:0);
    }
    $total['cat'] = 'Total';
    $total['user'] = $tUser;
    $total['view'] = $tView;
    $total['order'] = $tOrder;
    $total['abs'] = (int) $tGMV/($tOrder?$tOrder:1);
    $total['gmv'] = $tGMV;

    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;

    foreach ($data1 as $key => $value) {
      $result[$key]['user1'] = $value['unique_users']?$value['unique_users']:0;
      $result[$key]['view1'] = $value['views']?$value['views']:0;
      $result[$key]['order1'] = $value['orders']?$value['orders']:0;
      $result[$key]['abs1'] = $value['abs']?$value['abs']:0;
      $result[$key]['gmv1'] = $value['gmv']?$value['gmv']:0;
      $tUser+= ($value['unique_users']?$value['unique_users']:0);
      $tView += ($value['views']?$value['views']:0);
      $tOrder += ( $value['orders']?$value['orders']:0);
      $tGMV +=( $value['gmv']?$value['gmv']:0);
    }


    $total['user1'] = $tUser;
    $total['view1'] = $tView;
    $total['order1'] = $tOrder;
    $total['abs1'] = (int) $tGMV/($tOrder?$tOrder:1);
    $total['gmv1'] = $tGMV;

    $tUser = 0;
    $tView = 0;
    $tOrder = 0;
    $tGMV = 0;
    foreach ($data7 as $key => $value) {
      $result[$key]['user7'] = $value['unique_users']?$value['unique_users']:0;
      $result[$key]['view7'] = $value['views']?$value['views']:0;
      $result[$key]['order7'] = $value['orders']?$value['orders']:0;
      $result[$key]['abs7'] = $value['abs']?$value['abs']:0;
      $result[$key]['gmv7'] = $value['gmv']?$value['gmv']:0;
      $tUser+= ($value['unique_users']?$value['unique_users']:0);
      $tView += ($value['views']?$value['views']:0);
      $tOrder += ( $value['orders']?$value['orders']:0);
      $tGMV +=( $value['gmv']?$value['gmv']:0);
    }

    $total['user7'] = $tUser;
    $total['view7'] = $tView;
    $total['order7'] = $tOrder;
    $total['abs7'] = (int) $tGMV/($tOrder?$tOrder:1);
    $total['gmv7'] = $tGMV;

    array_unshift($result, $total);
    return $result;
  }

  function prepareTable($orderData){
    $body.='<table border="1" style="margin-bottom:0px;width:100%; border-collapse: collapse;" width="100%">
  <tbody>
  <tr>
    <td rowspan=3 style="font-weight:bold; text-align:center">Cat</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Unique Visitors</td>
    <td colspan=3 style="font-weight:bold; text-align:center">Page views </td>
    <td colspan=9 style="font-weight:bold; text-align:center">Orders </td>
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
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
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
   
 foreach($orderData as $value){       
      if($value['cat'] == 'Total'){           
        $body.=openRow("background:#CCCCCC");
        $body.=writeStringLeft($value['cat'],"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['user'])?$value['user']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['user1'])?$value['user1']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['user7'])?$value['user7']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['view'])?$value['view']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['view1'])?$value['view1']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['view7'])?$value['view7']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['order'])?$value['order']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['abs'])?$value['abs']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['gmv'])?$value['gmv']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['order1'])?$value['order1']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['abs1'])?$value['abs1']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['gmv1'])?$value['gmv1']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['order7'])?$value['order7']:0),"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['abs7'])?$value['abs7']:0),"font-weight:bold;font-size:100%");
        $body.=writeStringRight(printNum(isset($value['gmv7'])?$value['gmv7']:0),"font-weight:bold;font-size:100%;"); 
        $body.=closeRow();
      }       
      else{
        $body.=openRow();
        $body.=writeStringLeft($value['cat'],"font-weight:bold;font-size:100%;");
        $body.=writeStringRight(printNum(isset($value['user'])?$value['user']:0),"");
        $body.=writeStringRight(printNum(isset($value['user1'])?$value['user1']:0),"");
        $body.=writeStringRight(printNum(isset($value['user7'])?$value['user7']:0),"");
        $body.=writeStringRight(printNum(isset($value['view'])?$value['view']:0),"");
        $body.=writeStringRight(printNum(isset($value['view1'])?$value['view1']:0),"");
        $body.=writeStringRight(printNum(isset($value['view7'])?$value['view7']:0),"");
        $body.=writeStringRight(printNum(isset($value['order'])?$value['order']:0),"background:#CCCCCC");
        $body.=writeStringRight(printNum(isset($value['abs'])?$value['abs']:0),"");
        $body.=writeStringRight(printNum(isset($value['gmv'])?$value['gmv']:0),"");
        $body.=writeStringRight(printNum(isset($value['order1'])?$value['order1']:0),"background:#CCCCCC");
        $body.=writeStringRight(printNum(isset($value['abs1'])?$value['abs1']:0),"");
        $body.=writeStringRight(printNum(isset($value['gmv1'])?$value['gmv1']:0),"");
        $body.=writeStringRight(printNum(isset($value['order7'])?$value['order7']:0),"background:#CCCCCC");
        $body.=writeStringRight(printNum(isset($value['abs7'])?$value['abs7']:0),"");
        $body.=writeStringRight(printNum(isset($value['gmv7'])?$value['gmv7']:0),"");            
        $body.=closeRow(); 
      }    
  }
    
  $body.='</tbody></table>';
  return $body;
  }

?>