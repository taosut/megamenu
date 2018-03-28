<?php

class Ved_Crosscheck_Helper_Data extends Mage_Core_Helper_Abstract
{


    private function readExcelFile($file)
    {
        $includePath = Mage::getBaseDir() . "/lib/Excel/";
        set_include_path(get_include_path() . PS . $includePath . PS . $includePath . 'PhpExcel/');
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        return $objReader->load($file);
    }

    public function generateStockOutput($orderIds)
    {
        if (isset($orderIds) && is_array($orderIds) && count($orderIds) > 0) {
            $filepath = Mage::getBaseDir() . '/media/stock/template/stock_output.xlsx';
            $objPHPExcel = $this->readExcelFile($filepath);
            //$order =  Mage::getModel('sales/order')->load($orderIds[0]);
            $objPHPExcel = $this->renderStockOutput($objPHPExcel, 0, $orderIds);

            return $objPHPExcel;
        }
    }

    private function renderStockOutput($objPHPExcel, $sheet, $orderIds)
    {
        $order = Mage::getModel('sales/order')->load($orderIds[0]);
        $now = getdate();
        $activeSheet = $objPHPExcel->setActiveSheetIndex($sheet);
        $activeSheet
            ->setCellValue('A7', 'Ngày ' . $now["mday"] . ' tháng ' . $now["mon"] . ' năm ' . $now["year"])
            ->setCellValue('C15', "Hoàng Hoa Thám")
            ->setCellValue('C14', Mage::helper('sales')->__("Export for %s", $order->getIncrementId()));

        $shipping_address = $order->getShippingAddress();
        $billing_address = $order->getBillingAddress();
        $receiver = $shipping_address->getName();
        $shipping_address = $shipping_address->getStreetFull() . "," . $shipping_address->getCity() . "," . $shipping_address->getRegion();
        $receiver_phonenumber = $order->getBillingAddress()->getTelephone();
        $total_value = $order->getGrandTotal();
        //$total_value = Mage::helper('core')->currency($total_value, true, false);

        $taxID = $billing_address->getVatId();

        if (isset($receiver)) {
            $activeSheet->setCellValue('C10', $receiver);
        } else {
            $activeSheet->setCellValue('C10', ' ');
        }

        if (isset($receiver_phonenumber)) {
            $activeSheet->setCellValue('C11', $receiver_phonenumber);
        } else {
            $activeSheet->setCellValue('C11', ' ');
        }

        if (isset($shipping_address)) {
            $activeSheet->setCellValue('C12', $shipping_address);
        } else {
            $activeSheet->setCellValue('C12', ' ');
        }

        if (isset($taxID)) {
            $activeSheet->setCellValue('C13', $taxID);
        } else {
            $activeSheet->setCellValue('C13', ' ');
        }


        $i = 18;
        $stt = 1;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $shipping_address = $order->getShippingAddress();
            if ($receiver == $shipping_address->getName() && $receiver_phonenumber == $order->getBillingAddress()->getTelephone()) {
                $items = $order->getAllVisibleItems();
                foreach ($items as $item) {
                    //$ProductfullData = $this->_product->findById($row['product_id']);
                    $activeSheet
                        ->setCellValue('A' . $i, $stt)
                        ->setCellValue('B' . $i, $item->getSku())
                        ->setCellValue('C' . $i, $item->getName())
                        ->setCellValue('D' . $i, $item->getQtyOrdered())
                        ->setCellValue('E' . $i, $item->getPrice())
                        ->setCellValue('F' . $i, $item->getRowTotalInclTax())
                        ->setCellValue('G' . $i, $order->getIncrementId());

                    $i++;
                    $stt++;
                }
            }
        }


        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $styleArrayCenter = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $styleArrayLeft = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
        );

        $mer = $i + 2;

        $activeSheet
            ->mergeCells("A$mer:E$mer")
            ->setCellValue('A' . $mer, 'Tổng cộng thanh toán : ')
            ->setCellValue("F$mer", $total_value)
            ->setCellValue("C" . ($mer + 1), 'Bằng chữ:')
            ->setCellValue("D" . ($mer + 1), $this->VndText((int)$total_value))
            ->mergeCells("A" . ($mer + 4) . ":" . "B" . ($mer + 4))
            ->setCellValue('A' . ($mer + 4), 'Người nhận hàng')
            ->mergeCells("C" . ($mer + 4) . ":" . "D" . ($mer + 4))
            ->setCellValue('C' . ($mer + 4), 'Người giao hàng')
            ->setCellValue('E' . ($mer + 4), 'Thủ kho')
            ->setCellValue('G' . ($mer + 4), 'Quản lý')
            ->mergeCells("A" . ($mer + 5) . ":" . "B" . ($mer + 5))
            ->setCellValue('A' . ($mer + 5), '(Ký, họ tên)')
            ->mergeCells("C" . ($mer + 5) . ":" . "D" . ($mer + 5))
            ->setCellValue('C' . ($mer + 5), '(Ký, họ tên)')
            ->setCellValue('E' . ($mer + 5), '(Ký, họ tên)')
            ->setCellValue('G' . ($mer + 5), '(Ký, đóng dấu)')
            ->setCellValue('A' . ($mer + 11), '*Bằng việc ký vào biên bản này, người nhận hàng xác nhận mọi thông tin trên là chính xác.');

        $activeSheet->getStyle('A' . $mer)->applyFromArray($styleArray);
        $activeSheet->getStyle('F' . $mer)->applyFromArray($styleArray);
        $activeSheet->getStyle('C' . ($mer + 1))->applyFromArray($styleArray);
        $activeSheet->getStyle('D' . ($mer + 1))->applyFromArray($styleArrayLeft);
        $activeSheet->getStyle('A' . ($mer + 4))->applyFromArray($styleArray);
        $activeSheet->getStyle('C' . ($mer + 4))->applyFromArray($styleArray);
        $activeSheet->getStyle('E' . ($mer + 4))->applyFromArray($styleArray);
        $activeSheet->getStyle('G' . ($mer + 4))->applyFromArray($styleArray);
        $activeSheet->getStyle('A' . ($mer + 5))->applyFromArray($styleArrayCenter);
        $activeSheet->getStyle('C' . ($mer + 5))->applyFromArray($styleArrayCenter);
        $activeSheet->getStyle('E' . ($mer + 5))->applyFromArray($styleArrayCenter);
        $activeSheet->getStyle('G' . ($mer + 5))->applyFromArray($styleArrayCenter);
        $activeSheet->getStyle('A' . ($mer + 11))->applyFromArray(array(
            'font' => array(
                'bold' => false,
                'italic' => true,
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
        ));

        $activeSheet->getStyle("A18:G" . ($mer))->applyFromArray(array(
            'borders' => array(
                'outline' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                    'size' => 1,
                ),
                'inside' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                    'size' => 1,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ),
        ));
        return $objPHPExcel;
    }

    private function VndText($amount)
    {
        if ($amount <= 0) {
            return $textnumber = "Tiền phải là số nguyên dương lớn hơn số 0";
        }
        $Text = array("không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín");
        $TextLuythua = array("", "nghìn", "triệu", "tỷ", "ngàn tỷ", "triệu tỷ", "tỷ tỷ");
        $textnumber = "";
        $length = strlen($amount);

        for ($i = 0; $i < $length; $i++)
            $unread[$i] = 0;

        for ($i = 0; $i < $length; $i++) {
            $so = substr($amount, $length - $i - 1, 1);

            if (($so == 0) && ($i % 3 == 0) && ($unread[$i] == 0)) {
                for ($j = $i + 1; $j < $length; $j++) {
                    $so1 = substr($amount, $length - $j - 1, 1);
                    if ($so1 != 0)
                        break;
                }

                if (intval(($j - $i) / 3) > 0) {
                    for ($k = $i; $k < intval(($j - $i) / 3) * 3 + $i; $k++)
                        $unread[$k] = 1;
                }
            }
        }

        for ($i = 0; $i < $length; $i++) {
            $so = substr($amount, $length - $i - 1, 1);
            if ($unread[$i] == 1)
                continue;

            if (($i % 3 == 0) && ($i > 0))
                $textnumber = $TextLuythua[$i / 3] . " " . $textnumber;

            if ($i % 3 == 2)
                $textnumber = 'trăm ' . $textnumber;

            if ($i % 3 == 1)
                $textnumber = 'mươi ' . $textnumber;


            $textnumber = $Text[$so] . " " . $textnumber;
        }

        //Phai de cac ham replace theo dung thu tu nhu the nay
        $textnumber = str_replace("không mươi", "lẻ", $textnumber);
        $textnumber = str_replace("lẻ không", "", $textnumber);
        $textnumber = str_replace("mươi không", "mươi", $textnumber);
        $textnumber = str_replace("một mươi", "mười", $textnumber);
        $textnumber = str_replace("mươi năm", "mươi lăm", $textnumber);
        $textnumber = str_replace("mươi một", "mươi mốt", $textnumber);
        $textnumber = str_replace("mười năm", "mười lăm", $textnumber);

        return ucfirst($textnumber . " đồng chẵn");
    }

    public function stockOutput(Request $request)
    {
        $re = $request->get('id');
        if (isset($re)) {
            $filepath = storage_path('media/stock/template/stock_output.xlsx');
            $fileType = $inputFileType = \PHPExcel_IOFactory::identify($filepath);
            $objReader = \PHPExcel_IOFactory::createReader($fileType);
            $objPHPExcel = $objReader->load($filepath);

            $StockOutputfullData = $this->_stockoutput->findById($re);
            $WarehousefullData = $this->_warehouse->findById($StockOutputfullData['warehouse_id']);
            $StockOutputItemfullData = $this->_stockOutputItem->find(['stock_output_id' => $StockOutputfullData['id']]);

            $now = getdate();
            $setCell = $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A7', 'Ngày ' . $now["mday"] . ' tháng ' . $now["mon"] . ' năm ' . $now["year"])
                ->setCellValue('C15', $WarehousefullData['address'])
                ->setCellValue('C14', $StockOutputfullData['description']);

            if (isset($StockOutputfullData['name'])) {
                $setCell->setCellValue('C10', $StockOutputfullData['name']);
            } else {
                $setCell->setCellValue('C10', ' ');
            }

            if (isset($StockOutputfullData['phone'])) {
                $setCell->setCellValue('C11', $StockOutputfullData['phone']);
            } else {
                $setCell->setCellValue('C11', ' ');
            }

            if (isset($StockOutputfullData['address'])) {
                $setCell->setCellValue('C12', $StockOutputfullData['address']);
            } else {
                $setCell->setCellValue('C12', ' ');
            }

            if (isset($StockOutputfullData['taxID'])) {
                $setCell->setCellValue('C13', $StockOutputfullData['taxID']);
            } else {
                $setCell->setCellValue('C13', ' ');
            }


            $i = 18;
            $stt = 1;
            $sumprice = 0;
            foreach ($StockOutputItemfullData as $row) {
                $ProductfullData = $this->_product->findById($row['product_id']);
                $setCell
                    ->setCellValue('A' . $i, $stt)
                    ->setCellValue('B' . $i, $ProductfullData['sku'])
                    ->setCellValue('C' . $i, $ProductfullData['name'])
                    ->setCellValue('D' . $i, $row['stock_qty'])
                    ->setCellValue('E' . $i, $row['input_price'])
                    ->setCellValue('F' . $i, $row['output_price']);
                if ($row['item_status'] == 0) {
                    $setCell->setCellValue('G' . $i, 'Mới 100%');
                } else {
                    $setCell->setCellValue('G' . $i, '');
                }
                $i++;
                $sumprice = $sumprice + $row['input_price'];
                $stt++;
            }

            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            );

            $styleArrayCenter = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            );

            $styleArrayLeft = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
            );

            $mer = $i + 2;

            $setCell
                ->mergeCells("A$mer:F$mer")
                ->setCellValue('A' . $mer, 'Tổng cộng thanh toán : ')
                ->setCellValue("G$mer", $sumprice)
                ->setCellValue("C" . ($mer + 1), 'Bằng chữ:')
                ->setCellValue("D" . ($mer + 1), $this->VndText($sumprice))
                ->mergeCells("A" . ($mer + 4) . ":" . "B" . ($mer + 4))
                ->setCellValue('A' . ($mer + 4), 'Người nhận hàng')
                ->mergeCells("C" . ($mer + 4) . ":" . "D" . ($mer + 4))
                ->setCellValue('C' . ($mer + 4), 'Người giao hàng')
                ->setCellValue('E' . ($mer + 4), 'Thủ kho')
                ->setCellValue('G' . ($mer + 4), 'Quản lý')
                ->mergeCells("A" . ($mer + 5) . ":" . "B" . ($mer + 5))
                ->setCellValue('A' . ($mer + 5), '(Ký, họ tên)')
                ->mergeCells("C" . ($mer + 5) . ":" . "D" . ($mer + 5))
                ->setCellValue('C' . ($mer + 5), '(Ký, họ tên)')
                ->setCellValue('E' . ($mer + 5), '(Ký, họ tên)')
                ->setCellValue('G' . ($mer + 5), '(Ký, đóng dấu)')
                ->setCellValue('A' . ($mer + 11), '*Bằng việc ký vào biên bản này, người nhận hàng xác nhận mọi thông tin trên là chính xác.');

            $setCell->getStyle('A' . $mer)->applyFromArray($styleArray);
            $setCell->getStyle('G' . $mer)->applyFromArray($styleArray);
            $setCell->getStyle('C' . ($mer + 1))->applyFromArray($styleArray);
            $setCell->getStyle('D' . ($mer + 1))->applyFromArray($styleArrayLeft);
            $setCell->getStyle('A' . ($mer + 4))->applyFromArray($styleArray);
            $setCell->getStyle('C' . ($mer + 4))->applyFromArray($styleArray);
            $setCell->getStyle('E' . ($mer + 4))->applyFromArray($styleArray);
            $setCell->getStyle('G' . ($mer + 4))->applyFromArray($styleArray);
            $setCell->getStyle('A' . ($mer + 5))->applyFromArray($styleArrayCenter);
            $setCell->getStyle('C' . ($mer + 5))->applyFromArray($styleArrayCenter);
            $setCell->getStyle('E' . ($mer + 5))->applyFromArray($styleArrayCenter);
            $setCell->getStyle('G' . ($mer + 5))->applyFromArray($styleArrayCenter);
            $setCell->getStyle('A' . ($mer + 11))->applyFromArray(array(
                'font' => array(
                    'bold' => false,
                    'italic' => true,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
            ));

            $setCell->getStyle("A18:G" . ($mer))->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                        'size' => 1,
                    ),
                    'inside' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                        'size' => 1,
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                ),
            ));

            if (!is_dir(base_path('public/export_file'))) {
                mkdir(base_path('public/export_file'));
            }
            if (!is_dir(base_path('public/export_file/stockOutput'))) {
                mkdir(base_path('public/export_file/stockOutput'));
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $full_path = base_path('public/export_file/stockOutput/' . time() . 'stockoutput.xlsx');
            $path = '/export_file/stockOutput/' . time() . 'stockoutput.xlsx';
            $objWriter->save($full_path);
            $response = [
                'status' => 'sussces',
                'path' => $path,
            ];
            return $response;
        } else {
            return api_response()->errorBadRequest();
        }
    }

    //Export non cancel orders

    public function generateNonCancelOrders($orders)
    {
        $result = $this->array2csv($orders);

        return $result;
    }

    private function array2csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

    //Update shipment status
    public function getSheetNames($file)
    {
        $objPHPExcel = $this->readExcelFile($file);
        $sheets = array();
        foreach ($objPHPExcel->getAllSheets() as $index => $sheet) {
            $sheets[] = array("id" => $index, "name" => $sheet->getTitle());
        }
        return $sheets;
    }

    public function getSheetData($file, $sheet, $ignore)
    {
        $objPHPExcel = $this->readExcelFile($file);
        $activeSheet = $objPHPExcel->setActiveSheetIndex($sheet);
        $highestCol = $activeSheet->getHighestColumn();
        $header = $activeSheet->rangeToArray("A1:$highestCol" . "1", "", true, true, true);
        $data = $activeSheet->rangeToArray("A" . ($ignore + 1) . ":" . $highestCol . ($ignore + 10), "", true, true, true);
        return array("row_num" => $activeSheet->getHighestRow(), "headers" => $header, "sample_data" => $data);
    }

    public function importData($input)
    {
        $currentPayment = Mage::getModel('ved_crosscheck/paymentcrosscheck')->load($input->crosscheck_id);
        $collection = Mage::getModel('ved_crosscheck/paymentcrosscheck')->getCollection()
            ->addFieldToFilter('status', 2)
            ->addFieldToFilter('store_id', $currentPayment->getStoreId())
            ->addFieldToFilter('id', array('neq' => $input->crosscheck_id));
        $collection->massUpdate(array('status' => 3));

        $currentPayment->setStatus(2);
        $currentPayment->save();

        $objPHPExcel = $this->readExcelFile($input->file);
        $activeSheet = $objPHPExcel->setActiveSheetIndex($input->sheet);
        $highestCol = $activeSheet->getHighestColumn();
        $highestRow = $activeSheet->getHighestRow();
        if ($highestRow < $input->from) {
            return array("done" => 1);
        }
        $to = $input->from + 9 < $highestRow ? $input->from + 9 : $highestRow;
        $rows = $activeSheet->rangeToArray("A" . $input->from . ":" . $highestCol . $to, "", true, true, true);
        $orders = array();
        foreach ($rows as $rowNo => $row) {
            $row = array_filter($row);
            if (count($row) == 0) continue;
            $order = array();
            foreach ($input->attrs as $attr) {
                $code = $attr["matched"];
                $col = $attr["col"];
                $order[$code] = $row[$col];
            }
            $orders[$rowNo] = array_filter($order);
        }

        $result = $this->saveOrder($orders,$input->crosscheck_id,$input->file_id);
        $result->total = count($rows);
        $result->detail[] = "Update : " . ($to - 1) . " rows of " . ($highestRow - 1);
        return $result;
    }

    private function saveCrosscheckItem($user, $orderId, $orderAmount, $crosscheckId, $fileId){
        $item = Mage::getModel("ved_crosscheck/paymentCrosscheckItem");
        try{
            $item->setPaymentCrosscheckId($crosscheckId)
                ->setOrderIncrementId($orderId)
                ->setOrderAmount($orderAmount)
                ->setStatus(1)
                ->setFileUploadId($fileId)
                ->setCreatedAt(date('Y-m-d H:i:s',time()))
                ->setCreatedBy($user)
                ->save();
            return true;
        }catch (Exception $e){
            var_dump($e->getMessage());
            return false;
        }

    }

    private function checkAllowAmount($crosscheckId, $orderAmount){
        try{
            $currentPayment = Mage::getModel('ved_crosscheck/paymentcrosscheck')->load($crosscheckId);
            $allowAmount = $currentPayment->getTotalAmount() - $currentPayment->getTotalImportedAmount();
            $data = Mage::getModel("ved_crosscheck/paymentcrosscheck")->getCollection()
                ->addFieldToFilter('status', array('in' => array(2,3)))
                ->addFieldToFilter('store_id', $currentPayment->getStoreId())
                ->addExpressionFieldToSelect('total_allow_amount',
                    new Zend_Db_Expr('sum(total_amount - total_imported_amount)'),
                    [])
                ->getData();
            if(isset($data[0]) && isset($data[0]['total_allow_amount'])){
                if(isset($data[0]['total_allow_amount']) && $currentPayment->getStatus() == 1){
                    $allowAmount += $data[0]['total_allow_amount'];
                }else{
                    $allowAmount = $data[0]['total_allow_amount'];
                }
            }
            if($orderAmount > $allowAmount)
                return false;
            return true;
        }catch (Exception $e){
            var_dump($e->getMessage());die();
            return false;
        }
    }

    private function updatePaymentImportedAmount($crosscheckId, $orderAmount){
        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $resource = Mage::getResourceModel('ved_crosscheck/paymentcrosscheck');
            $connection->update(
                $resource->getMainTable(),
                array('total_imported_amount' => new Zend_Db_Expr('total_imported_amount + ' . $orderAmount)),
                array('id = ?' => (int) $crosscheckId));
            return true;
        }catch (Exception $e){
            var_dump($orderAmount, $e->getMessage());
            return false;
        }

    }

    private function saveOrder($orders, $crosscheckId, $fileId)
    {
        $result = (object)array("update" => 0, "detail" => array(), "fail" => array(), "done" => 0, "imported_amount" => 0);
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserName = $admin_user_session->getUser()->getUsername();
        $adminuserId = $admin_user_session->getUser()->getUserId();

        foreach ($orders as $row => $value) {
            if (isset($value['increment_id'])) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($value['increment_id']);
                if (!$order || !$order->getId()) {
                    $result->fail[] = "Row $row has incorrect data";
                } else {
                    if($order->getStatus() == 'canceled' || $order->getStatus() == 'reject'){
                        $result->fail[] = "Row $row was canceled or rejected";
                        continue;
                    }else if($order->getStatus() == 'complete'){
                        $result->fail[] = "Row $row has been completed";
                        continue;
                    }else{
                        try {
                            if($this->checkAllowAmount($crosscheckId, $order->getGrandTotal())){
                                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                                $connection->beginTransaction();
                                if($this->saveCrosscheckItem($adminuserId, $value['increment_id'], $order->getGrandTotal(), $crosscheckId, $fileId)
                                    && $this->updatePaymentImportedAmount($crosscheckId,$order->getGrandTotal())
                                ){
                                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, '<b>' . $adminUserName . '</b><br/> Import complete status', false);
                                    //$order->addStatusHistoryComment('<b>' . $adminUserName . '</b><br/> Import complete status');

                                    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                                        ->setStatus('complete');

                                    if ($order->save()) {
                                        $connection->update(
                                            'sales_flat_order_grid',
                                            array('status' => 'complete'),
                                            array(
                                                'entity_id = ?' => $order->getEntityId(),
                                            )
                                        );
                                        $connection->commit();
                                        $result->imported_amount += $order->getGrandTotal();
                                        $result->update++;
                                    } else {
                                        $connection->rollback();
                                        $result->fail[] = "Row $row has incorrect status";
                                    }
                                }else{
                                    $connection->rollback();
                                    $result->fail[] = "Error";
                                }
                            }else{
                                $result->fail[] = "Over the allowed amount";
                                $result->done = 1;
                                return $result;
                            }

                        } catch (Exception $e) {
                            $connection->rollback();
                            $result->fail[] = "Row $row has problem when saving orderid: " . $value['increment_id'] . ' loi:' . $e->getMessage();
                        }
                    }
                }
            } else {
                $result->fail[] = "Row $row is missing data";
            }
        }
        return $result;
    }

    protected function _getAssignedState($status)
    {
        try {
            $item = Mage::getResourceModel('sales/order_status_collection')
                ->joinStates()
                ->addFieldToFilter('main_table.status', $status)
                ->getFirstItem();

            return $item->getState();
        } catch (Exception $e) {
            return 'new';
        }
    }

    public function getWebsiteByUserId($userId = 0){

        $userStoreMappings = Mage::getModel("ved_purchase/mapping")->getCollection();

        $userStoreMappings
            ->addFieldToFilter('user_id', $userId);
        $result = array();
        foreach($userStoreMappings as $userStoreMapping){
            $result[] = $userStoreMapping->getWebsiteId();
        }
        return $result;
    }


}