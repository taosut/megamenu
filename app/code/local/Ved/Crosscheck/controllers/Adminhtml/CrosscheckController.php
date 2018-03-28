<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/5/2016
 * Time: 5:33 PM
 */
class Ved_Crosscheck_Adminhtml_CrosscheckController extends Mage_Adminhtml_Controller_Action
{

    private $import_result = array("update" => 0, "total" => 0, "fail" => array());

    /**
     * Product grid for AJAX request
     */
    public function gridPaymentAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_crosscheck/adminhtml_PaymentCrosscheck_Grid', 'ved_crosscheck_grid')->toHtml()
        );
    }

    public function gridOverdueOrderAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_crosscheck/adminhtml_OverdueOrder_Grid', 'ved_overdue_grid')->toHtml()
        );
    }

    public function gridPaymentItemAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_crosscheck/adminhtml_ViewPaymentCrosscheck_Grid', 'ved_crosscheckItem_grid')->toHtml()
        );
    }

    public function overdueAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Overdue Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('crosscheck/crosscheck');
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_OverdueOrder'));
        $this->renderLayout();
    }

    public function paymentAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Payment'));
        $this->loadLayout();
        $this->_setActiveMenu('crosscheck/crosscheck');
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_PaymentCrosscheck'));
        $this->renderLayout();
    }

    public function newAction(){
        $this->_title($this->__('Sales'))->_title($this->__('Create Payment'));
        $this->loadLayout();
        $this->_setActiveMenu('crosscheck/crosscheck');
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_EditPaymentCrosscheck'));
        $this->renderLayout();
    }

    public function editPaymentCrosscheckAction(){
        $this->_title($this->__('Sales'))->_title($this->__('Create Payment'));
        $this->loadLayout();
        $this->_setActiveMenu('crosscheck/crosscheck');
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_EditPaymentCrosscheck'));
        $this->renderLayout();
    }

    public function viewPaymentCrosscheckAction(){
        $this->_title($this->__('Sales'))->_title($this->__('Create Payment'));
        $this->loadLayout();
        $this->_setActiveMenu('crosscheck/crosscheck');
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_ViewPaymentCrosscheck'));
        $this->renderLayout();
    }

    public function uploadStatusAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Import Product from Excel file"));
        $this->_addContent($this->getLayout()->createBlock('ved_crosscheck/adminhtml_UploadStatus'));
        $this->renderLayout();
    }

    public function saveAction(){
        if ($data = $this->getRequest()->getPost()) {
            $storeId = $this->getRequest()->get('payment_store');
            $totalAmount = $this->getRequest()->get('total_amount');
            $payDate = $this->getRequest()->get('pay_date');
            $note = $this->getRequest()->get('note');
            $id = $this->getRequest()->get('payment_id');
            if ($storeId && $totalAmount && $payDate) {
                $store = Mage::getModel("core/store")->load($storeId)->getData();
                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $date = str_replace('/', '-', $payDate);
                if($store && isset($store['store_id'])){

                    $data = Mage::getModel('ved_crosscheck/paymentcrosscheck')
                        ->setStoreId($store['store_id'])
                        ->setWebsiteId($store['website_id'])
                        ->setPayDate(date('Y-m-d', strtotime($date)))
                        ->setTotalAmount($totalAmount)
                        ->setNote($note);
                    if ($id){
                        $data->setId($id);
                    }else{
                        $data->setCreatedBy($adminuserId)
                            ->setCreatedAt(date('Y-m-d H:i:s',time()));
                    }
                    $data->save();
                }
            }
            $this->getResponse()->setRedirect($this->getUrl('*/*/payment'));
        }
    }

    public function uploadAction(){
        if (isset($_FILES['file']['name'])) {
            try {
                $date = date("Y-m-d");
                $uploader = new Varien_File_Uploader('file');
                $uploader->setAllowedExtensions(explode(",","xls,xlsx")); // or pdf or anything
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media') . DS."shipmentstatus".DS.$date;
                $result = $uploader->save($path, $_FILES['file']['name']);

                $file = Mage::getBaseDir('media') . DS."shipmentstatus".DS.$date.DS.$result['file'];
                $helper = Mage::helper("crosscheck");

                $admin_user_session = Mage::getSingleton('admin/session');
                $adminuserId = $admin_user_session->getUser()->getUserId();
                $fileUpload = Mage::getModel('ved_crosscheck/importLog');
                $fileUpload
                    ->setFilename($_FILES['file']['name'])
                    ->setTemplate($file)
                    ->setSize($_FILES['file']['size'])
                    ->setImported(1)
                    ->setImportedUser($adminuserId)
                    ->setImportedDate(date('Y-m-d H:i:s',time()))
                    ->save();

                $re = array("result"=>"ok","msg"=>"",
                    "data"=>array(
                        "filename"=>$result['file'],
                        "sheets"=>$helper->getSheetNames($file),
                        "file_id" => $fileUpload->getId()
                    ));



            } catch (Exception $e) {
                $re = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
            }
        }else{
            $re = array("result"=>"error","msg"=>"no upload file");
        }
        echo json_encode($re);
        die();
    }

    public function matchingAction(){
        $file = $this->getRequest()->getParam('file');
        $ignore = $this->getRequest()->getParam('ignore');
        $sheet = $this->getRequest()->getParam('sheet');

        //security
        $filePath = Mage::getBaseDir('media') . DS."shipmentstatus".DS.date("Y-m-d").DS.basename($file);
        try {
            $helper = Mage::helper("crosscheck");
            $sheetData = $helper->getSheetData($filePath,$sheet,$ignore);

            $headers = $sheetData["headers"][1];
            $attrCode = array(); $attrName = array();$unique = array();

            //try to match excel column with attribute
            $cols = array();
            foreach ($headers as $colNo=>$header) {
                $name = trim(strtolower($header));
                $col = array("col"=>$colNo,"name"=>$name);
                if(isset($attrCode[$name])){
                    $col["matched"] = $attrCode[$name];
                }else if(isset($attrName[$name])){
                    $col["matched"] = $attrName[$name];
                }
                $cols[] = $col;
            }

            $re = array("result"=>"ok","msg"=>"",
                "data"=>array(
                    "row_num" => $sheetData["row_num"],
                    "unique"=>$unique,
                    "cols" => $cols,
                    "attrs" => array(
                        1=>array("name" => "Mã đơn hàng", "code" => "increment_id"),
                    ),
                ));
        }catch (Exception $e) {
            $re = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($re);
        die();
    }

    public function importingAction(){
        $file = $this->getRequest()->getParam('file');
        $sheet = $this->getRequest()->getParam('sheet');
        $updater = $this->getRequest()->getParam('updater');
        $attrs = $this->getRequest()->getParam('attr');
        $from = $this->getRequest()->getParam('from');
        $crosscheckId = $this->getRequest()->getParam('crosscheck_id');
        $fileId = $this->getRequest()->getParam('file_id');

        $filePath = Mage::getBaseDir('media') . DS."shipmentstatus".DS.date("Y-m-d").DS.basename($file);
        $helper = Mage::helper("crosscheck");
        $input = array("file"=>$filePath,"sheet"=>$sheet,"from"=>$from,"attrs"=>$attrs,"updater"=>$updater, "crosscheck_id" => $crosscheckId, 'file_id' => $fileId);
        $rs = $helper->importData((object)$input);
        echo json_encode($rs);
        die();


//        $filePath = Mage::getBaseDir('media') . DS."shipmentstatus".DS.date("Y-m-d").DS.basename($file);
//        $helper = Mage::helper("crosscheck");
//        $input = array("file"=>$filePath,"sheet"=>$sheet,"from"=>$from,"attrs"=>$attrs,"updater"=>$updater);
//        $rs = $helper->importData((object)$input);
//        $this->import_result['update'] += $rs->update;
//        $this->import_result['total'] += $rs->total;
//        $this->import_result['fail'] = array_merge($this->import_result['fail'],$rs->fail);
//        echo json_encode($this->import_result);
//        die();
    }


    public function exportOverdueCsvAction()
    {
        $fileName = 'all_overdue_orders.csv';
        $grid = $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_OverdueOrder_Grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportCrosscheckItemCsvAction()
    {
        $fileName = 'all_imported_item_orders.csv';
        $grid = $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_OverdueOrder_Grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOverdueExcelAction()
    {
        $fileName = 'all_overdue_orders.xml';
        $grid = $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_OverdueOrder_Grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function exportOverdueXlsAction()
    {
        $fileName = 'all_overdue_orders.xls';
        $worksheet_name = "All Order";
        $content = $this->getLayout()->createBlock('ved_crosscheck/Adminhtml_OverdueOrder_Grid')->getXls();

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

    public function exportOverdueOrderXlsAction()
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

}