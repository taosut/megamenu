<?php

class Ved_Productqc_Adminhtml_ProductqcController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('QC Products'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->_addContent($this->getLayout()->createBlock('ved_productqc/adminhtml_catalog_productqc'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_productqc/adminhtml_catalog_Productqc_grid')->toHtml()
        );
    }

    public function WarehouseSkuEditorAction()
    {
        //Get all Categories
        $categories = Mage::helper("ved_gorders")->getAllData('categories');
        $categories = array_column($categories, 'name','id');
        //Get all Manufacter
        $manufacturers = Mage::helper("ved_gorders")->getAllData('manufacturers');
        $manufacturers = array_column($manufacturers, 'name','id');

        $content = $this->getLayout()->createBlock('ved_productqc/adminhtml_catalog_Productqc_warehouse', '' , array(
            'categories'            => $categories,
            'manufacturers'           => $manufacturers,));

        $this->getResponse()->setBody($content->toHtml());
    }

    public function ComboSupplierSkuEditorAction()
    {
        //Get all Categories

        $content = $this->getLayout()->createBlock('ved_productqc/adminhtml_catalog_Productqc_Combosupplier', '' , array());

        $this->getResponse()->setBody($content->toHtml());
    }

    public function massApproveAction(){
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        var_dump($productIds, $storeId);
        try {
            //$this->_validateMassStatus($productIds, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            //Mage::getModel('catalog/product_status')->updateProductStatus($productIds, $storeId, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED), $storeId);

//            Mage::getSingleton('catalog/product_action')
//                ->updateAttributes($productIds, array('status' => 0), $storeId);

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            var_dump($e->getMessage());die();
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            var_dump($e->getMessage());die();
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            var_dump($e->getMessage());die();
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/');
    }

    public function massRejectAction(){
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);

        try {
            //$this->_validateMassStatus($productIds, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED), $storeId);

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/');
    }

}