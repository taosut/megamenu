<?php

class Ved_SupplierNew_Adminhtml_InventoryController extends Mage_Adminhtml_Controller_Action
{
    //Manage inventory from supplier
    public function indexAction()
    {
        print "Inventory";
        die();
        $this->_title($this->__("Manage Inventory"));
        $this->loadLayout()
            ->_setActiveMenu("adminhtml/suppliernew")
            ->_addBreadcrumb(
                Mage::helper("adminhtml")->__("Manage Inventory"),
                Mage::helper("adminhtml")->__("Manage Inventory")
            );
        $this->renderLayout();
    }
    /******** Support Action **************/
    //List Supplier
  public function gridAction()
  {
    $this->loadLayout();
    $this->renderLayout();
  }

  protected function _initAction()
  {
    $this->loadLayout()
      //->_setActiveMenu("adminhtml/supplier")
      ->_addBreadcrumb(
          Mage::helper("adminhtml")->__("Supplier  Manager"),
          Mage::helper("adminhtml")->__("Supplier Manager")
      );

    return $this;
  }

  public function editAction()
  {
    $this->_title($this->__("Supplier"));
    $this->_title($this->__("Supplier"));
    $this->_title($this->__("Edit Supplier"));

    $id = $this->getRequest()->getParam("id");
    $model = Mage::getModel("suppliernew/supplier")->load($id);
    if ($model->getId()) {
      Mage::register("supplier_data", $model);
      $this->loadLayout();

      //$this->_setActiveMenu("adminhtml/supplier/index");

      //$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Supplier Manager"), Mage::helper("adminhtml")->__("Supplier Manager"));
      //$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Supplier Description"), Mage::helper("adminhtml")->__("Supplier Description"));
                  //var_dump($this->getLayout());
      $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
      $this->_addContent($this->getLayout()->createBlock("suppliernew/adminhtml_supplier_edit"));//->_addLeft($this->getLayout()->createBlock("supplier/adminhtml_supplier_edit_tabs"));
      $this->renderLayout();
    } else {
      Mage::getSingleton("adminhtml/session")->addError(Mage::helper("suppliernew")->__("Supplier does not exist."));
      $this->_redirect("*/*/");
    }
  }

  public function newAction()
  {
    $this->_title($this->__("Supplier"));
    $this->_title($this->__("Supplier"));
    $this->_title($this->__("New Supplier"));

    $id = $this->getRequest()->getParam("id");
    $model = Mage::getModel("supplier/supplier")->load($id);

    $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
    if (!empty($data)) {
      $model->setData($data);
    }

    Mage::register("supplier_data", $model);

    $this->loadLayout();
    //$this->_setActiveMenu("adminhtml/supplier/index");

    $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

    //$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Supplier Manager"), Mage::helper("adminhtml")->__("Supplier Manager"));
    //$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Supplier Description"), Mage::helper("adminhtml")->__("Supplier Description"));

    $this->_addContent($this->getLayout()->createBlock("supplier/adminhtml_supplier_edit"));//->_addLeft($this->getLayout()->createBlock("supplier/adminhtml_supplier_edit_tabs"));

    $this->renderLayout();
  }

  public function saveAction()
  {
    $post_data = $this->getRequest()->getPost();
      //var_dump($this->getRequest()->getParam("id"));
     // var_dump($post_data);        die();
    if ($post_data) {
      $supplierId = $this->getRequest()->getParam('id');

      try {
        if(isset($post_data['supplier_name'])) {
          $post_data['supplier_name'] = trim($post_data['supplier_name']);
        }

        if(is_null($supplierId) && isset($post_data['supplier_name'])) {
          $supplierName = $post_data['supplier_name'];

          $modelSupplier = Mage::getModel("supplier/supplier");
          $supplier = $modelSupplier->load($supplierName, 'supplier_name');
          if ($supplier->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Supplier with the same name '%s' already exists", $supplierName));
            Mage::getSingleton("adminhtml/session")->setSupplierData($post_data);
            $this->getResponse()->setRedirect($this->getUrl('*/*/new'));
            $this->_redirect("*/*/new");
            return;
          }
        } else if(!is_null($supplierId) && isset($post_data['supplier_name'])) {
          $supplierName = $post_data['supplier_name'];
          unset($post_data['supplier_name']);
        }

        $post_data['updated_at'] = time();
        if(is_null($supplierId)) {
          $arg_attribute = 'supplier';
          $arg_value = $supplierName;

          $attr_model = Mage::getModel('catalog/resource_eav_attribute');
          $attr = $attr_model->loadByCode('catalog_product', $arg_attribute);
          $attr_id = $attr->getAttributeId();

          $option['attribute_id'] = $attr_id;
          $option['value']['any_option_name'][0] = $arg_value;

          $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
          $setup->addAttributeOption($option);

          $resource = Mage::getSingleton('core/resource');
          $result = $resource->getConnection('core_read')->fetchRow("SELECT * FROM ".$resource->getTableName('eav/attribute_option_value')." WHERE value = '".$option['value']['any_option_name'][0]."'");
          if($result && is_array($result) && isset($result['option_id'])) {
            $post_data['option_id'] = (int)$result['option_id'];
          } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Not found option value '%s'. Please try again!", $option['value']['any_option_name'][0]));
            Mage::getSingleton("adminhtml/session")->setSupplierData($post_data);
            $this->getResponse()->setRedirect($this->getUrl('*/*/new'));
            $this->_redirect("*/*/new");
            return;
          }
          $post_data['created_at'] = time();   
          
        }
        
        $model = Mage::getModel("supplier/supplier");

        $model->addData($post_data)
          ->setId($this->getRequest()->getParam("id"))
          ->save();
        $model->removeDistrictOfSupplierCurrently($supplierId);
         // var_dump($post_data['districts']);die();
        foreach ($post_data['districts'] as $key => $district_id){
          $data['suplier_id'] =  $this->getRequest()->getParam("id");
          $data['city_id']    =  $district_id;
          $data['created_at'] =  time();
          $data['updated_at'] =  time();
          $admin_user_session = Mage::getSingleton('admin/session');
          
          $data['createdby']  =  $admin_user_session->getUser()->getUserId();
          $model->saveDistrictforSupplierDistribution($data);
        }
        
        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Supplier was successfully saved"));
        Mage::getSingleton("adminhtml/session")->setSupplierData(false);

        if ($this->getRequest()->getParam("back")) {
          $this->_redirect("*/*/edit", array("id" => $model->getId()));
          return;
        }
        $this->_redirect("*/*/");
        return;

      } catch (Exception $e) {
        Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        Mage::getSingleton("adminhtml/session")->setSupplierData($post_data);
        $this->_redirect("*/*/edit", array("id" => $supplierId));
        return;
      }

    }
    $this->_redirect("*/*/");
  }

  public function provinceCityAction()
  {
    $arrCities = array();
    $province = $this->getRequest()->getParam('parent');

    if($province) {
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');
      $query = 'SELECT c.* FROM '.$resource->getTableName('directory_city')." c INNER JOIN ".$resource->getTableName('directory_country_region')." r WHERE c.region_id = r.region_id AND r.default_name = '".$province."'";
      $dataCities = $readConnection->fetchAll($query);
      foreach ($dataCities as $key => $value) {
        $arrCities[$value['name']] = $value['name'];
      }
      if($arrCities) {
        $collator = new Collator('vi_VN');
        $collator->sort($arrCities);
      }
    }

    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrCities));
  }

//  public function deleteAction()
//  {
//    if ($this->getRequest()->getParam("id") > 0) {
//      try {
//        $model = Mage::getModel("supplier/supplier");
//        $model->setId($this->getRequest()->getParam("id"))->delete();
//        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Supplier was successfully deleted"));
//        $this->_redirect("*/*/");
//      } catch (Exception $e) {
//        Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
//        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
//      }
//    }
//    $this->_redirect("*/*/");
//  }


//  public function massRemoveAction()
//  {
//    try {
//      $ids = $this->getRequest()->getPost('supplier_ids', array());
//      foreach ($ids as $id) {
//        $model = Mage::getModel("supplier/supplier");
//        $model->setId($id)->delete();
//      }
//      Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Supplier(s) was successfully removed"));
//    } catch (Exception $e) {
//      Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
//    }
//    $this->_redirect('*/*/');
//  }

  /**
   * Export order grid to CSV format
   */
  public function exportCsvAction()
  {
    $fileName = 'supplier.csv';
    $grid = $this->getLayout()->createBlock('supplier/adminhtml_supplier_grid');
    $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
  }

  /**
   *  Export order grid to Excel XML format
   */
  public function exportExcelAction()
  {
    $fileName = 'supplier.xml';
    $grid = $this->getLayout()->createBlock('supplier/adminhtml_supplier_grid');
    $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
  }
    //Export function
    public function exportProductCsvAction(){
        $fileName = 'supplier_products.csv';
        $grid = $this->getLayout()->createBlock('suppliernew/catalog_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    public function exportProductExcelAction(){
        $fileName = 'supplier_products.xml';
        $grid = $this->getLayout()->createBlock('suppliernew/catalog_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
