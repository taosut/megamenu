<?php
 
class Ved_Gorders_Adminhtml_ProcessingOrderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Processing Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('ved_gorders/adminhtml_sales_ProcessingOrder'));
        $this->renderLayout();
    }
 
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_ProcessingOrder_grid')->toHtml()
        );
    }
 
    public function exportGcafeCsvAction()
    {
        $fileName = 'orders_processing.csv';
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_ProcessingOrder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
 
    public function exportGcafeExcelAction()
    {
        $fileName = 'orders_processing.xml';
        $grid = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_ProcessingOrder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
		
		public function exportGcafeXlsAction()  
		{  
				$fileName   = 'orders_processing.xls';
			 $worksheet_name = "Processing Order";
			 $content    = $this->getLayout()->createBlock('ved_gorders/adminhtml_sales_ProcessingOrder_grid')->getXls();
			 
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
    public function massRejectAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countRejectedOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canReject()) {
                $order->reject()
                    ->save();
                $countRejectedOrder++;
            }
        }

        $countNonRejectedOrder = count($orderIds) - $countRejectedOrder;

        if ($countNonRejectedOrder) {
            if ($countNonRejectedOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not put on rejected.', $countNonRejectedOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were put on rejected.'));
            }
        }
        if ($countRejectedOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been put on rejected.', $countRejectedOrder));
        }

        $this->_redirect('*/*/');
    }
    /*********Change status to Complete **************/
    public function massCompleteAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countInvoicedOrder = 0;

        foreach ($orderIds as $orderId) {
            if ($this->_createInvoice($orderId)) {
                $countInvoicedOrder++;
            }
        }

        $countNonInvoicedOrder = count($orderIds) - $countInvoicedOrder;

        if ($countNonInvoicedOrder) {
            if ($countNonInvoicedOrder) {
                $this->_getSession()->addError($this->__('%s order(s) were not invoiced.', $countNonInvoicedOrder));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were invoiced.'));
            }
        }
        if ($countInvoicedOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been invoiced.', $countInvoicedOrder));
        }

        $this->_redirect('*/*/');
    }
		
		/********** Print stock output  **************/
    public function massPrintStockOutputAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $includePath = Mage::getBaseDir(). "/lib/Excel/";
        set_include_path(get_include_path() . PS . $includePath. PS . $includePath. 'PhpExcel/');

        if(count($orderIds)>0){
            $helper = Mage::helper("ved_gorders");
            $objPHPExcel = $helper->generateStockOutput($orderIds);
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=stock_output_" . time() .".xls");
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
        $this->_redirect('*/*/');
    }
		
    /*********** Support function ************/
    protected function _createInvoice($order_id){
        $order = Mage::getModel("sales/order")->load($order_id);
        if(isset($order)){
            try {
                if(!$order->canInvoice())
                {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                    return false;
                }

                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                    return false;
                }

                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();
                return true;
            }
            catch (Mage_Core_Exception $e) {

            }
        }
        return false;
    }
}