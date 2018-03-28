<?php

class Ved_SupplierNew_Adminhtml_Supplier_ProductController extends Mage_Adminhtml_Controller_Action
{
    //List Product
    public function indexAction()
    {

        $this->_title($this->__("Manage Products"));
        $this->loadLayout()
            ->_setActiveMenu("adminhtml/suppliernew")
            ->_addBreadcrumb(
                Mage::helper("adminhtml")->__("Manage Products"),
                Mage::helper("adminhtml")->__("Manage Products")
            );
        $this->renderLayout();
    }
    /******** Support Action **************/
    //List product
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

    protected function _initProduct()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Manage Products'));

        $productId = (int)$this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int)$this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (Exception $e) {
                $product->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE);
                Mage::logException($e);
            }
        }

        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())
        ) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                explode(",", base64_decode(urldecode($attributes)))
            );
        }

        // Required attributes of simple product for configurable creation
        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')
        ) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
            && $this->getRequest()->getParam('product')
            && !is_array($this->getRequest()->getParam('product'))
            && $this->getRequest()->getParam('id', false) === false
        ) {

            $configProduct = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->load($this->getRequest()->getParam('product'))
                ->setTypeId($this->getRequest()->getParam('type'));

            /* @var $configProduct Mage_Catalog_Model_Product */
            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes() as $attribute) {

                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if (!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType() != 'gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $configProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }

            $product->addData($data)
                ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $product;
    }

    public function editAction()
    {
        $this->_title($this->__("Supplier"));

        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->_initProduct();

        if ($productId && !$product->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This product no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($product->getName());

        Mage::dispatchEvent('catalog_product_edit_action', array('product' => $product));

        $_additionalLayoutPart = '';
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && !($product->getTypeInstance()->getUsedProductAttributeIds())
        ) {
            $_additionalLayoutPart = '_new';
        }

        $this->loadLayout(array(
            'default',
            strtolower($this->getFullActionName()),
            'adminhtml_catalog_product_' . $product->getTypeId() . $_additionalLayoutPart
        ));

        $this->_setActiveMenu('catalog/products');

        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null))
                );
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->renderLayout();
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
                if (isset($post_data['supplier_name'])) {
                    $post_data['supplier_name'] = trim($post_data['supplier_name']);
                }

                if (is_null($supplierId) && isset($post_data['supplier_name'])) {
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
                } else if (!is_null($supplierId) && isset($post_data['supplier_name'])) {
                    $supplierName = $post_data['supplier_name'];
                    unset($post_data['supplier_name']);
                }

                $post_data['updated_at'] = time();
                if (is_null($supplierId)) {
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
                    $result = $resource->getConnection('core_read')->fetchRow("SELECT * FROM " . $resource->getTableName('eav/attribute_option_value') . " WHERE value = '" . $option['value']['any_option_name'][0] . "'");
                    if ($result && is_array($result) && isset($result['option_id'])) {
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
                foreach ($post_data['districts'] as $key => $district_id) {
                    $data['suplier_id'] = $this->getRequest()->getParam("id");
                    $data['city_id'] = $district_id;
                    $data['created_at'] = time();
                    $data['updated_at'] = time();
                    $admin_user_session = Mage::getSingleton('admin/session');

                    $data['createdby'] = $admin_user_session->getUser()->getUserId();
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


    //Export function
    public function exportProductCsvAction()
    {
        $fileName = 'supplier_products.csv';
        $grid = $this->getLayout()->createBlock('suppliernew/catalog_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportProductExcelAction()
    {
        $fileName = 'supplier_products.xml';
        $grid = $this->getLayout()->createBlock('suppliernew/catalog_product_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
