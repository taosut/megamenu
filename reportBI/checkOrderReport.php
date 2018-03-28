<?php
include('_header.php');
try{
    $db = new project_control();
    $orders = $db->get_allrows_sql("SELECT entity_id FROM `sales_flat_order` WHERE state = 'new' AND (time_to_sec( timediff('" . date('Y-m-d H:i:s', time()) ."' ,`updated_at`) ) / 3600 - 7) > 12");
    if (count($orders) > 0) {
        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreplyved@gmail.com';
        $mail->Password = 'noreplyved!@#$';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        $mail->Subject = "Cảnh báo đơn hàng chưa xử lý";

        $mail->Body    = "Hiện đang có " . count($orders) . " đơn hàng quá hạn xử lý";

        $mail->setFrom('noreply@ved.com.vn', 'Gcafe Warning');
        $mail->addAddress('hiep1996tb@gmail.com');
//        $mail->addAddress('tranlinh.do@ved.com.vn');

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }
}catch(Exception $e){
    print_r($e->getMessage());
}