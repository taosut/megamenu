<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 2/1/2018
 * Time: 3:23 PM
 */
class StockRequestEmail {

    private function sendEmail($attachmentFile, $filename)
    {
        $now = Mage::getModel('core/date')->timestamp(time());
        $mailBody = "Chi tiết yêu cầu hàng ngày: <b>" . date('d/m/Y', $now) . "</b><br />Xem tệp đính kèm để biết thêm chi tiết";

        $includePath = Mage::getBaseDir() . "/lib/Mail/";
        require_once($includePath . "PHPMailerAutoload.php");

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreplyved@gmail.com';
        $mail->Password = 'noreplyved!@#$';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "[Phong Vũ] Yêu cầu hàng ngày " . date('d/m/Y', $now);
        $mail->Body = $mailBody;

        $mail->isHTML(true);
        $mail->setFrom('noreply@ved.com.vn', 'Phong Vũ');
        $emails = json_decode(Mage::getModel('core/variable')->loadByCode('stock_request_emails')->getValue('plain'))->list;
        foreach ($emails as $email) {
            $mail->addAddress($email);
        }
        $mail->AddAttachment($attachmentFile);

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }

    public function run()
    {
        if (!file_exists('media/stock_request/')) {
            mkdir('media/stock_request/', 0777, true);
        }

        $now = Mage::getModel('core/date')->timestamp(time());
        $dateStart = date('Y-m-d' . ' 00:00:00', $now);
        $dateEnd = date('Y-m-d' . ' 23:59:59', $now);

        $stockRequestCollection = Mage::getModel('ved_stockrequest/stockrequest')
            ->getCollection()
            ->addFieldToFilter('created_at', array('from' => $dateStart, 'to' => $dateEnd));

        $filename = date('Y-m-d', $now) . '_yeu_cau_hang.xlsx';
        include Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->setTitle("Yêu cầu hàng");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Yêu cầu hàng ngày ' . date('d/m/Y', $now))
            ->setCellValue('A3', 'ID')
            ->setCellValue('B3', 'Tên người yêu cầu')
            ->setCellValue('C3', 'Số điện thoại')
            ->setCellValue('D3', 'Nội dung')
            ->setCellValue('E3', 'Tên sản phẩm')
            ->setCellValue('F3', 'Mã kho');
        $row = 4;

        foreach ($stockRequestCollection as $stockRequest) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$row, $stockRequest->getId())
                ->setCellValue('B'.$row, $stockRequest->getUserName())
                ->setCellValue('C'.$row, "'".$stockRequest->getPhoneNumber())
                ->setCellValue('D'.$row, $stockRequest->getRequestContent())
                ->setCellValue('E'.$row, $stockRequest->getProductName())
                ->setCellValue('F'.$row, Mage::getModel('catalog/product')->load($stockRequest->getProductId())->getWarehouseSku());
            $row++;
        }

        /** For total product */
        $stockRequestCollection
            ->getSelect()
            ->columns('COUNT(*) AS total')
            ->group('product_id');
        $data = $stockRequestCollection->getData();

        $objWorkSheet = $objPHPExcel->createSheet(1);
        $objWorkSheet->setTitle("Tổng yêu cầu");
        $objWorkSheet->setCellValue('A1', 'Tổng yêu cầu ' . date('d/m/Y', $now))
            ->setCellValue('A3', 'STT')
            ->setCellValue('B3', 'Mã kho')
            ->setCellValue('C3', 'Số lượng');

        $row = 4;
        foreach ($data as $product) {
            $objWorkSheet->setCellValue('A'.$row, $row - 3)
                ->setCellValue('B'.$row, Mage::getModel('catalog/product')->load($product['product_id'])->getWarehouseSku())
                ->setCellValue('C'.$row, $product['total']);
            $row++;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('media/stock_request/'.$filename);
        $this->sendEmail('media/stock_request/'.$filename, $filename);
    }
}