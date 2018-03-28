<?php
/**
 * Created by PhpStorm.
 * User: tranlinh
 * Date: 26/10/2016
 * Time: 6:18 PM
 */
class Ved_Gorders_Adminhtml_UploadShipmentStatusController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Import Product from Excel file"));
        $this->renderLayout();
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
                $helper = Mage::helper("ved_gorders");

                $re = array("result"=>"ok","msg"=>"",
                    "data"=>array(
                        "filename"=>$result['file'],
                        "sheets"=>$helper->getSheetNames($file)
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
            $helper = Mage::helper("ved_gorders");
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
                        2=>array("name" => "Trạng thái đơn hàng", "code" => "status"),
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


        $filePath = Mage::getBaseDir('media') . DS."shipmentstatus".DS.date("Y-m-d").DS.basename($file);
        $helper = Mage::helper("ved_gorders");
        $input = array("file"=>$filePath,"sheet"=>$sheet,"from"=>$from,"attrs"=>$attrs,"updater"=>$updater);
        $rs = $helper->importData((object)$input);
        echo json_encode($rs);
        die();
    }
}