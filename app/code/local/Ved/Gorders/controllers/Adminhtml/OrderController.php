<?php

class Ved_Gorders_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('All Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_grid')->toHtml()
        );
    }

    public function exportGcafeCsvAction()
    {
        $fileName = 'orders_all.csv';
        /**
         * @var Ved_Gorders_Block_Adminhtml_Sales_Order_Grid $grid
         */
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportGcafeExcelAction()
    {
        $fileName = 'orders_all.xml';
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function exportGcafeXlsAction()
    {
        $fileName = 'orders_all.xls';
        $worksheet_name = "All Order";
        $content = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_grid')->getXls();

        include Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $rowCount = 1;
        foreach ($content as $value) {
            $column = 'A';
            foreach ($value as $val) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $rowCount, $val);
                $column++;
            }
            $rowCount++;
        }
        $objPHPExcel->getActiveSheet()->setTitle($worksheet_name);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName");
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    public function exportOrderXlsAction()
    {
        $results = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_order_grid')->getOrderXls();
        //var_dump($results);die();
        $helper = Mage::helper("ved_gorders");

        header('Content-Description: File Transfer');
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m-d") . ".csv");
        header("Content-disposition: filename=non_cancel_order_" . time() . ".csv");
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        print "\xEF\xBB\xBF";
        if (count($results) > 0) {
            echo $helper->generateNonCancelOrders($results);
            die;
        }
        die;
    }

    /************************  Process Action  **********************************/
    /*** Cancel order  ***/
    /**
     * Cancel selected orders
     */
    public function massCancelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be canceled', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be canceled'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been canceled.', $countCancelOrder));
        }
        $this->_redirect('*/*/');
    }


    /********** Verify Order *********/
    public function massVerifyAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countVerifyOrder = 0;
        $countNonVerifyOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canVerify()) {
                $order->verify()
                    ->save();
                $countVerifyOrder++;
            } else {
                $countNonVerifyOrder++;
            }
        }
        if ($countNonVerifyOrder) {
            if ($countVerifyOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be verified', $countNonVerifyOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be verified'));
            }
        }
        if ($countVerifyOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been verified.', $countVerifyOrder));
        }
        $this->_redirect('*/*/');
    }

    /******* Ready to pickup *******/
    public function massReadyAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countReadyOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canReady()) {
                $order->ready()
                    ->save();
                $countReadyOrder++;
            }
        }

        $countNonReadydOrder = count($orderIds) - $countReadyOrder;

        if ($countNonReadydOrder) {
            if ($countNonReadydOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on ready.', $countNonReadydOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on ready.'));
            }
        }
        if ($countReadyOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on ready.', $countReadyOrder));
        }

        $this->_redirect('*/*/');
    }

    /*********Change status to ship **************/
    public function massShipAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countShippedOrder = 0;

        foreach ($orderIds as $orderId) {
            if ($this->_createShipment($orderId)) {
                $countShippedOrder++;
            }
        }

        $countNonShippeddOrder = count($orderIds) - $countShippedOrder;

        if ($countNonShippeddOrder) {
            if ($countNonShippeddOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not shipped.', $countNonShippeddOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were shipped.'));
            }
        }
        if ($countShippedOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been shipped.', $countShippedOrder));
        }

        $this->_redirect('*/*/');
    }

    /********** Print list product **************/
    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if (count($orderIds) > 0) {
            $shipment_label = Mage::getModel("ved_gorders/pdf_shipmentLabel");
            $pdf = $shipment_label->printListProductspdf($orderIds);
            $content = $pdf->render();
            return $this->_prepareDownloadResponse(
                'packet_list_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf',
                $content,
                'application/pdf; charset=utf-8');
        }
        $this->_redirect('*/*/');
    }

    /********** Print Shipping Label **************/
    public function massPrintShippingLabelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if (count($orderIds) > 0) {
            $shipment_label = Mage::getModel("ved_gorders/pdf_shipmentLabel");
            $pdf = $shipment_label->printShipmentLabelpdf($orderIds);
            $content = $pdf->render();
            return $this->_prepareDownloadResponse('packet_label_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') .
                '.pdf', $content, 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    /*** Hold order ***/
    /**
     * Hold selected orders
     */
    public function massHoldAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countHoldOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canHold()) {
                $order->hold()
                    ->save();
                $countHoldOrder++;
            }
        }

        $countNonHoldOrder = count($orderIds) - $countHoldOrder;

        if ($countNonHoldOrder) {
            if ($countHoldOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on hold.', $countNonHoldOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on hold.'));
            }
        }
        if ($countHoldOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on hold.', $countHoldOrder));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Unhold selected orders
     */
    public function massUnholdAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countUnholdOrder = 0;
        $countNonUnholdOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canUnhold()) {
                $order->unhold()
                    ->save();
                $countUnholdOrder++;
            } else {
                $countNonUnholdOrder++;
            }
        }
        if ($countNonUnholdOrder) {
            if ($countUnholdOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not released from holding status.', $countNonUnholdOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were released from holding status.'));
            }
        }
        if ($countUnholdOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been released from holding status.', $countUnholdOrder));
        }
        $this->_redirect('*/*/');
    }


    /********** Support function ************/
    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    protected function _createShipment($orderId)
    {
        $carrier = "GHTK";
        $title = "Default shipment";
        $trackInfo = "GHTK-" . $orderId;

        $comment = null;
        $email = false;
        $includeComment = false;

        //Create shipment
        $order = Mage::getModel('sales/order')->load($orderId);

        $convertor = Mage::getModel('sales/convert_order');
        if (isset($order)) {
            try {
                $shipment = $convertor->toShipment($order);

                foreach ($order->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToShip()) {
                        continue;
                    }
                    if ($orderItem->getIsVirtual()) {
                        continue;
                    }
                    $item = $convertor->itemToShipmentItem($orderItem);
                    $qty = $orderItem->getQtyToShip();
                    $item->setQty($qty);
                    $shipment->addItem($item);
                }

                //Add tracking info
                $trackdata = array();
                $trackdata['carrier_code'] = $carrier;
                $trackdata['title'] = $title;
                $trackdata['number'] = $trackInfo;

                $track = Mage::getModel('sales/order_shipment_track')->addData($trackdata);
                $shipment->addTrack($track);
                //echo "<pre>"; print_r($shipment);echo "<pre>"; exit;

                //Register current shipment, set sent email and change status
                Mage::register('current_shipment', $shipment);

                $shipment->register();
                $shipment->addComment($comment, $email && $includeComment);
                $shipment->setEmailSent(true);
                $shipment->getOrder()->setIsInProcess(true);

                //Log transaction
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

                $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                return true;
            } catch (Mage_Core_Exception $e) {

            } catch (Exception $e) {
                Mage::logException($e);
            }

        }
        return false;
    }

    public function testAction()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        require Mage::getBaseDir('lib') . "/Qrcode/qrlib.php";
        $filename = date("YmdHis", time()) . ".png";
        $codeContents = "test";
        QRcode::png($codeContents, Mage::getBaseDir('media') . "/qrcode/" . $filename, QR_ECLEVEL_L, 4);
        echo '<html><body><img  src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/qrcode/' . $filename . '" /></body></html>';
    }

    /********** Export non cancel orders  **************/
    public function exportNonCancelOrdersAction()
    {
        $new_db_resource = Mage::getSingleton('core/resource');
        $connection = $new_db_resource->getConnection('default_read');
        $query = "select a.customer_id, c.firstname,
                    c.region, c.city 'quan_huyen' , a.increment_id, a.status, a.created_at, b.sku, b.name,
                    b.qty_ordered, b.base_price, b.discount_amount 'khuyen_mai', a.coupon_code,
                    a.coupon_rule_name, a.discount_amount 'coupon_giam', b.row_total, a.updated_at from sales_flat_order a
                    join sales_flat_order_item b on a.entity_id = b.order_id
                    left join sales_flat_order_address c on a.shipping_address_id = c.entity_id
                    where a.status <> 'canceled' and a.updated_at > DATE_SUB(now(),INTERVAL 60 day)";
        $results = $connection->fetchAll($query);
        //var_dump($results);die();


        if (count($results) > 0) {
            $helper = Mage::helper("ved_gorders");

            header('Content-Description: File Transfer');
            header("Content-type: application/vnd.ms-excel");
            header("Content-disposition: csv" . date("Y-m-d") . ".csv");
            header("Content-disposition: filename=non_cancel_order_" . time() . ".csv");
            header('Content-Transfer-Encoding: binary');
            header('Pragma: public');
            print "\xEF\xBB\xBF";

            echo $helper->generateNonCancelOrders($results);

        }
        return;
    }

    public function ampAction()
    {
        try {
            /**
             * @var Mage_Sales_Model_Order[] $orders
             */
            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $this->getRequest()->get('ids')])
                ->load();
            if ($this->getRequest()->get('action') == 'cancel')
                foreach ($orders as $order) {
                    $order->callMessageQueueCancel();
                    echo "Cancel oder:" . $order->getId() . "<br/>";
                }
            if ($this->getRequest()->get('action') == 'create')
                foreach ($orders as $order) {
                    Mage::callMessageQueue($order->getDataMessageQueue(), 'sale.order.create');
                    $order->setIsSendQueue(true)->save();
                    echo "Cancel oder:" . $order->getId() . "<br/>";;
                }
            echo "done!";
        } catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }


}