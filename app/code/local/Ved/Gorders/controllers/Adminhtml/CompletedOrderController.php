<?php
 
class Ved_Gorders_Adminhtml_CompletedOrderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Completed Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('ved_gorders/adminhtml_sales_CompletedOrder'));
        $this->renderLayout();
    }
 
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_CompletedOrder_grid')->toHtml()
        );
    }
 
    public function exportGcafeCsvAction()
    {
        $fileName = 'orders_completed.csv';
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_CompletedOrder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
 
    public function exportGcafeExcelAction()
    {
        $fileName = 'orders_completed.xml';
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_CompletedOrder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
		
		public function exportGcafeXlsAction()  
		{  
				$fileName   = 'orders_completed.xls';
			 $worksheet_name = "Completed Order";
			 $content    = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_CompletedOrder_grid')->getXls();
			 
			 include Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";
			 $objPHPExcel = new PHPExcel();
			 $rowCount = 1;    
			 foreach($content as $value){
				$column = 'A';
				foreach($value as $val){
				 $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column.$rowCount, $val);
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
		
    /************************** Function process action button **********************/
    /******* Ready to Reject *******/
    public function massReDeliveryAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countRedeliveryOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canRedelivery()) {
                $order->redelivery()
                    ->save();
                $countRedeliveryOrder++;
            }
        }

        $countNonRedeliveryOrder = count($orderIds) - $countRedeliveryOrder;

        if ($countNonRedeliveryOrder) {
            if ($countNonRedeliveryOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on re-delivery.', $countNonRedeliveryOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on re-delivery.'));
            }
        }
        if ($countRedeliveryOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on re-delivery.', $countRedeliveryOrder));
        }

        $this->_redirect('*/*/');
    }

    public function massCancelCompletedOrdersAction()
    {
        $user = Mage::getSingleton('admin/session');
        $userUsername = $user->getUser()->getUsername();

        $orderIds = $this->getRequest()->getPost('order_ids', array());

        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('entity_id', $orderIds)
            ->addFieldToFilter('state', 'complete')
            ->load();

        foreach($orders as $order) {
            $order
                ->setData('state', 'canceled')
                ->setStatus('canceled');
            $order->addStatusHistoryComment('<b>' . $userUsername . '</b> hủy đơn hàng<br/>');

            $order->save();

            $message = 'Khách hàng hủy đơn hàng';
            $order->callMessageQueueCancel($message);
        }
        $this->_getSession()->addSuccess($this->__('%s completed order(s) have been canceled.', count($orders)));
        $this->_redirect('*/*/');
    }
    
    /********** Print list product **************/
    public function pdfshipmentsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if(count($orderIds)>0){
            $shipment_label = Mage::getModel("ved_gorders/pdf_shipmentLabel");
            $pdf = $shipment_label->printListProductspdf($orderIds);
            $content =  $pdf->render();
            return $this->_prepareDownloadResponse(
                'packet_list_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                $content,
                'application/pdf; charset=utf-8');
        }
        $this->_redirect('*/*/');
    }
    /********** Print Shipping Label **************/
    public function pdfinvoicesAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if(count($orderIds)>0){
            $shipment_label = Mage::getModel("ved_gorders/pdf_shipmentLabel");
            $pdf = $shipment_label->printInvoicepdf($orderIds);
            $content =  $pdf->render();
            return $this->_prepareDownloadResponse('invoice_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                '.pdf',$content, 'application/pdf');
        }
        $this->_redirect('*/*/');
    }

}