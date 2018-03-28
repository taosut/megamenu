<?php

class Ved_Instalment_Helper_Data extends Mage_Payment_Helper_Data
{
    private $apiUrlWarehouse;
    private $apiUrlSupplierTool;

    public function __construct()
    {
        $this->apiUrlWarehouse = (string)Mage::getConfig()->getNode('global/warehouse_api_url');
        $this->apiUrlSupplierTool = (string)Mage::getConfig()->getNode('global/supplier_api_url');
    }

    private function getPurchaseWahouse(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        $data = Mage::getModel('ved_instalment/orderitemstock')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('order_item_id', $item->getItemId())
            ->addFieldToFilter('status', array('in' => array(1, 2)))
            ->load();
        if (count($data)) {
            $html = "<table style='min-width: 200px'>";

            foreach ($data as $key => $value) {
                $html .= "<tr>
                            <td>Lấy từ kho:</td>
                            <td>{$value->getStoreName()}</td>
                        </tr>
                        <tr>
                            <td>Số lượng: </td>
                            <td>{$value->getQuantity()}</td>
                        </tr>";
            }

            return $html .= "</table>";
        }
        return null;

    }

    private function getPurchaseSupplier(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        $data = Mage::getModel('ved_instalment/purchaserequestitem')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('order_item_id', $item->getItemId())
            ->addFieldToFilter('status', array('in' => array(1, 2)))
            ->load();

        if (count($data)) {
            $html = "<table style='min-width: 200px'>";

            foreach ($data as $key => $value) {
                $html .= "<tr>
                            <td>Y/C mua hàng về kho:</td>
                            <td>{$value->getStoreName()}</td>
                        </tr>";
                if ($value->getSupplierId())
                    $html .= "<tr>
                            <td>Y/C mua từ NCC:</td>
                            <td>{$value->getSupplierName()}</td>
                        </tr>";
                $html .= "<tr>
                            <td>Số lượng: </td>
                            <td>{$value->getQuantity()}</td>
                        </tr>";
            }

            return $html .= "</table>";
        }
        return null;
    }

    private function getPurchaseTransfer(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        $data = Mage::getModel('ved_instalment/transfer')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('order_item_id', $item->getItemId())
            ->addFieldToFilter('status', array('in' => array(1, 2)))
            ->load();

        if (count($data)) {
            $html = "<table style='min-width: 200px'>";

            foreach ($data as $key => $value) {
                $html .= "<tr>
                            <td>Chuyển từ kho:</td>
                            <td>{$value->getStoreName()}</td>
                        </tr>
                        <tr>
                            <td>Số lượng: </td>
                            <td>{$value->getQuantity()}</td>
                        </tr>";
            }

            return $html .= "</table>";
        }
        return null;
    }

    public function getPurchaseHtml(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $item)
    {
        if ($this->_getAssignedState($order->getStatus()) == Mage_Sales_Model_Order::STATE_NEW) {
            if ($item->getProductType() == 'configurable') {
                $data = Mage::getModel('sales/order_item')
                    ->getCollection()
                    ->addFieldToFilter('parent_item_id', $item->getId())
                    ->getData();
                if (isset($data[0]['item_id']) && $data[0]['item_id'])
                    $item = Mage::getModel('sales/order_item')->load($data[0]['item_id']);
            }
            $url = Mage::getUrl('*/*/purchase', array('order_id' => $order->getId(), 'product_id' => $item->getId()));

            $html = $this->getPurchaseWahouse($order, $item) . $this->getPurchaseSupplier($order, $item) . $this->getPurchaseTransfer($order, $item);

            return $html ? $html : "<a href='$url'><button class='button'>Purchase Warehouse</button></a>";

        }
    }


    public function getFreeItemHtml(Mage_Sales_Model_Order_Item $item)
    {
        $free_html = '';
        $free_item = unserialize($item->getAdditionalData());

        if ($free_item['type'] && $free_item['type'] == 'Free' && $free_item['parent_product_name'])
            $free_html = '<strong>(Sản phẩm KM khi mua ' . $free_item['parent_product_name'] . ')</strong>';

        return $free_html;
    }

    /**
     * @return string
     */
    public function getApiUrlWarehouse($path)
    {
        return $this->apiUrlWarehouse . $path;
    }

    public function getApiUrlSupplier($path)
    {
        return $this->apiUrlSupplierTool . $path;
    }

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

        $result = $this->saveOrder($orders);
        $result->done = 0;
        $result->total = count($rows);
        $result->detail[] = "Total: " . $result->total . " rows";
        $result->detail[] = "Updated " . $result->update . " row(s)";
        return $result;
    }

    private function saveOrderBK($orders)
    {
        $result = (object)array("update" => 0, "detail" => array(), "fail" => array());
        $states = [];
        foreach ($orders as $row => $value) {
            if (isset($value['increment_id']) && isset($value['status']) && $value['increment_id'] && $value['status']) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($value['increment_id']);
                if (!$order || !$order->getId()) {
                    $result->fail[] = "Row $row has incorrect data";
                } else {
                    if (!isset($states[$value['status']])) {
                        $state = $this->_getAssignedState($value['status']);
                        if ($state) {
                            $states[$value['status']] = $state;
                        } else {
                            $result->fail[] = "Row $row has incorrect status: " . $value['status'];
                            continue;
                        }

                    }
                    if ($order
                        ->setState($states[$value['status']])
                        ->setStatus($value['status'])
                        ->save()
                    ) {
                        $result->update++;
                    } else {
                        $result->fail[] = "Row $row has incorrect status: " . $value['status'];
                    }
                }
            } else {
                $result->fail[] = "Row $row is missing data";
            }
        }
        return $result;
    }

    private function saveOrder($orders)
    {
        $result = (object)array("update" => 0, "detail" => array(), "fail" => array());
        $states = [];
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserName = $admin_user_session->getUser()->getUsername();
        foreach ($orders as $row => $value) {
            if (isset($value['increment_id']) && isset($value['status']) && $value['increment_id'] && $value['status']) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($value['increment_id']);
                if (!$order || !$order->getId()) {
                    $result->fail[] = "Row $row has incorrect data";
                } else {
                    if (!isset($states[$value['status']])) {
                        $state = $this->_getAssignedState($value['status']);
                        if ($state) {
                            switch ($state) {
                                case 'new':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_NEW;
                                    break;
                                case 'pending_payment':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                                    break;
                                case 'processing':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_PROCESSING;
                                    break;
                                case 'complete':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_COMPLETE;
                                    break;
                                case 'closed':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_CLOSED;
                                    break;
                                case 'canceled':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_CANCELED;
                                    break;
                                case 'holded':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_HOLDED;
                                    break;
                                case 'payment_review':
                                    $states[$value['status']] = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                                    break;
                            }
                        } else {
                            $result->fail[] = "Row $row has incorrect status: " . $value['status'];
                            continue;
                        }

                    }
                    try {
                        $order
                            ->setData('state', $states[$value['status']])
                            ->setStatus($value['status']);
                        $order->addStatusHistoryComment('<b>' . $adminUserName . '</b><br/> Import status');

                        $order
                            ->setData('state', $states[$value['status']])
                            ->setStatus($value['status']);

                        if ($order->save()) {
                            $result->update++;
                        } else {
                            $result->fail[] = "Row $row has incorrect status: " . $value['status'];
                        }
                    } catch (Exception $e) {
                        $result->fail[] = "Row $row has problem when saving orderid: " . $value['increment_id'] . " and status: " . $value['status'] . ' State: ' . $states[$value['status']] . ' loi:' . $e->getMessage();
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

    /**
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function cancelOrder(Mage_Sales_Model_Order $order)
    {
        $purchases = Mage::getModel('ved_instalment/orderitemstock')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('status', 1)
            ->load();

        if (count($purchases)) {
            $param = array();

            $client = new Varien_Http_Client(Mage::helper('ved_instalment')->getApiUrlWarehouse('products/unhold'));

            $client->setMethod(Varien_Http_Client::POST);

            foreach ($purchases as $purchase) {
                $param[] = array(
                    'entity_id' => $purchase->getProductId(),
                    'quantity' => $purchase->getQuantity(),
                    'store_id' => $purchase->getStoreId(),
                );
            }

            $client->setParameterPost('data', $param);

            $response = $client->request();

            if ($response->isSuccessful()) {
                $purchases->setDataToAll('status', 0)
                    ->save();
            }
        }

        Mage::getModel('ved_instalment/purchaserequestitem')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('purchase_id', array('null' => true))
            ->setDataToAll('status', 0)
            ->save();

        Mage::getModel('ved_instalment/transfer')
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->setDataToAll('status', 0)
            ->save();
    }

    public function hasPurchase($order, $product)
    {
        return $this->getPurchaseWahouse($order, $product) || $this->getPurchaseSupplier($order, $product) || $this->getPurchaseTransfer($order, $product);
    }

    public function cloneParent($parent_id, $new_id)
    {
        $purchases = Mage::getModel('ved_instalment/orderitemstock')
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('order_id', $parent_id)
            ->setDataToAll('status', 0);

        if (count($purchases)) {
            $param = array();

            $client = new Varien_Http_Client(Mage::helper('ved_instalment')->getApiUrlWarehouse('products/unhold'));

            $client->setMethod(Varien_Http_Client::POST);

            foreach ($purchases as $purchase) {
                $param[] = array(
                    'entity_id' => $purchase->getProductId(),
                    'quantity' => $purchase->getQuantity(),
                    'store_id' => $purchase->getStoreId(),
                );
            }

            $client->setParameterPost('data', $param);

            $response = $client->request();

            if ($response->isSuccessful()) {
                $purchases->setDataToAll('status', 0)
                    ->save();
            }
        }

        Mage::getModel('ved_instalment/purchaserequestitem')
            ->getCollection()
            ->addFieldToFilter('order_id', $parent_id)
            ->setDataToAll('status', 0)
            ->save();

        Mage::getModel('ved_instalment/transfer')
            ->getCollection()
            ->addFieldToFilter('order_id', $parent_id)
            ->setDataToAll('status', 0)
            ->save();
    }

    public function checkProductCodeWarehouse($code)
    {
        $path = 'products/check-code?' . http_build_query(array('warehouse_sku' => $code));

        $api = file_get_contents($this->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        return $json['data'];
    }

    public function getProductCodeWarehouse($name)
    {
        $path = 'products/get-codes?' . http_build_query(array('name' => $name));

        $api = file_get_contents($this->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        return $json['data'];
    }

    public function getAllAttributes($name)
    {
        $path = $name . '/get_all';

        $api = file_get_contents($this->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        return $json['data'];
    }

    public function getAllData($name)
    {
        $path = $name;

        $api = file_get_contents($this->getApiUrlSupplier($path));

        $json = json_decode($api, true);

        return $json;
    }

    public function checkProductCodeSupplier($code)
    {
        if(!trim($code))
            return false;
        $path = 'listProductSku?' . http_build_query(array('sku' => $code));

        $api = file_get_contents($this->getApiUrlSupplier($path));

        $json = json_decode($api, true);

        return $json['data'];
    }


    public function callApi($path, $method, array $data)
    {
        $client = new Varien_Http_Client($this->getApiUrlWarehouse($path));
        $response = $client->setMethod($method);
        switch ($method) {
            case Varien_Http_Client::POST:
                foreach ($data as $key => $value) {
                    $response->setParameterPost($key, $value);
                }
                break;
            case Varien_Http_Client::GET:
                foreach ($data as $key => $value) {
                    $response->setParameterGet($key, $value);
                }
                break;
            default:
                break;
        }
        $result = $response->request();
        if ($result->isSuccessful()) {
            return $result->getBody();
        }
        throw  new Exception("Call API fail: " . $result->getStatus() .
            " Copy this\"" . $result->getBody() . "\" send to Administrator");
    }

}