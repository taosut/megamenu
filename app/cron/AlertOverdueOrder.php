<?php

/**
 * Cronjob that send email to admin to alert overdue
 * @author hoangpt
 */
class AlertOverdueOrder
{

    /**
     * Do 2 thing: check overdue and send email
     * @author hoangpt
     */
    public function run()
    {

        //get from custom variable
        $jsonEmail = Mage::getModel('core/variable')->loadByCode('overdue_region_emails')->getValue('plain');
        $emails = json_decode($jsonEmail, true);

        foreach ($emails as $regionId => $emailList) {
            //apply rule 2 days for orders that do not handle
            $sql = "SELECT
                    main_table.entity_id,
                    oi.state,
                    oi.`status`,
                    main_table.store_id,
                    main_table.store_name,
                    main_table.customer_id,
                    main_table.grand_total,
                    main_table.shipping_name,
                    main_table.billing_name,
                    main_table.created_at,
                    main_table.updated_at,
                    oi.total_qty_ordered AS qty_ordered,
                    oi.deposit_amount,
                    shipping.telephone AS shipping_telephone,
                    concat(shipping.street,\",\",shipping.city,\",\",shipping.region) AS shipping_address, 
                    shipping.city AS shipping_city,
                    shipping.region AS shipping_region,
                    billing.telephone AS billing_telephone
                    FROM
                    sales_flat_order_grid AS main_table
                    INNER JOIN sales_flat_order AS oi ON oi.entity_id = main_table.entity_id
                    INNER JOIN sales_flat_order_address AS shipping ON main_table.entity_id = shipping.parent_id AND shipping.address_type = \"shipping\"
                    INNER JOIN sales_flat_order_address AS billing ON main_table.entity_id = billing.parent_id AND billing.address_type = \"billing\"
                    WHERE
                    ((oi.updated_at <= Now() - 2*24*3600 AND oi.state = \"new\") 
                    OR (oi.state = \"processing\" And oi.updated_at <= Now() - 7*24*3600)) 
                    AND
                    main_table.store_id = $regionId
                    ORDER BY main_table.updated_at DESC;";

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $results = $readConnection->fetchAll($sql);

            $file = $this->_generateExcel($results, $regionId);

            $this->_sendExcelToRecipents($file, $emailList);

        }

        //log to see any failed email
        //Mage::log($file, null, 'overdue_order_report');

    }


    private function _generateExcel($results, $regionId)
    {
        //generate excel file
        require_once Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $headerArr = array("Order ID", "State", "Status", "Store ID", "Store Name", "Customer ID",
            "Grand Total", "Shipping name", "Billing name", "Order DateCreated", "Order DateUpdated",
            "Quantity", "Deposit", "Shipping telephone", "Shipping address", "Shipping city", "Shipping region", "Billing Telephone"
        );
        $objPHPExcel->getActiveSheet()->fromArray($headerArr, NULL, 'A1');

        $row = 2;
        foreach ($results as $result) {
            $rowData = array_values($result);
            $objPHPExcel->getActiveSheet()->fromArray($rowData, NULL, 'A' . $row);
            $row++;
        }

        //config excel
        $objPHPExcel->getDefaultStyle()
            ->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $file = 'var/excel/overdue/Overdue_' . date('Y-m-d') . '_' . $regionId . '.xlsx';
        $objWriter->save($file);

        return $file;
    }

    private function _sendExcelToRecipents($file, $emailList)
    {
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

        $mail->isHTML(true);
        $mail->Subject = "Warning overdue order. Please support or some corp else will take care them for you.";
        $mail->Body = "Please find below attachment for further info";
        $mail->setFrom('noreply@ved.com.vn', 'Tekshop Warning');

        foreach ($emailList as $email) {
            $mail->addAddress($email);
        }

        $mail->AddAttachment($file);

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }
}