<?php

class Ved_Productqc_Adminhtml_Catalog_Product_Attribute_SetController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/update_attribute_set');
    }

    public function massEditAction()
    {
        try {
            $session = Teko::getSession();
            if ($this->getRequest()->isPost()) {
                if (count($this->getRequest()->get('product')) > 20) throw new Exception('Không thể thay đổi nhiều hơn 20 sản phẩm');
                $session->setData('current_product_edit_ids', $this->getRequest()->get('product'));
                $this->_redirect('*/*/massEdit');
                return;
            }
            $this->loadLayout()
                ->_addContent($this->getLayout()->createBlock(
                    'ved_productqc/adminhtml_catalog_product_attribute_set',
                    'ved_productqc/adminhtml_catalog_product_attribute_set',
                    ['product_ids' => $session->getData('current_product_edit_ids')]
                ))
                ->renderLayout();
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $e->getMessage());
            $this->_redirect('*/catalog_product/index');
        }
    }

    public function massEditSubmitAction()
    {
        try {
            $session = Teko::getSession();
            $productIds = $session->getData('current_product_edit_ids', true);
            foreach ($productIds as $entityId) {
                $product = Mage::getModel('catalog/product')->load($entityId);
                $product->setAttributeSetId($this->getRequest()->get('attribute_set_id'))
                    ->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }
        $this->_redirect('*/catalog_product/index');
    }
}