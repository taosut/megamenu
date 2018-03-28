<?php

class Ved_Agent_Adminhtml_Agent_GiftController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Redemption Gift'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_gift');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_gift'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_gift');

        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_gift_promo'));
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_gift_edit'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $giftId = $this->getRequest()->getParam('id');
        $gift = Mage::getModel('Ved_Agent/agentredemptiongift')->load($giftId);

        if ($gift->getId() || $giftId == 0) {
            if (!$giftId == 0) {
                $gift->rule_name = Mage::getModel('salesrule/rule')
                    ->load($gift->getRuleId())
                    ->getName();

                $gift->created_user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', $gift->created_by)
                ->addFieldToSelect('email')
                ->getFirstItem()
                ->getEmail();

                $gift->updated_user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', $gift->updated_by)
                ->addFieldToSelect('email')
                ->getFirstItem()
                ->getEmail();
            }

            Mage::register('redemption_gift', $gift);

            $this->loadLayout();

            $this->_setActiveMenu('agent/agent_gift');

            $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_gift_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agent')->__('Redemption gift does not exist'));

            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $giftId = $this->getRequest()->getParam('id');

        if (!$this->_checkUpdateable($giftId))
            return false;

        // Forbidden to create Gift which has same name, same rule and same point
        $checkExist = Mage::getModel('Ved_Agent/agentredemptiongift')
            ->getCollection()
            ->addFieldToFilter('is_deleted', 0)
            ->addFieldToFilter('redemption_gift_name', $postData['redemption_gift_name'])
            ->addFieldToFilter('rule_id', $postData['rule_id'])
            ->addFieldToFilter('point', $postData['point']);
        if ($giftId) {
            $checkExist->addFieldToFilter('redemption_gift_id', array('neq' => $giftId));
        }
        if ($checkExist->count()) {
            $error = Mage::helper('agent')->__('Already exist Gift with same name, salesrule and point');
            Mage::getSingleton('adminhtml/session')->addError($error);
            if (!$giftId) {
                $this->_redirect('*/*/');
            } else $this->_forward('edit');
            return;
        }

        if ($postData) {
            try {
                $model = Mage::getModel('Ved_Agent/agentredemptiongift');
                if (!$giftId) {
                    $model->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId());
                }

                $model->addData($postData)
                    ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                    ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                    ->setId($giftId)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Redemption Gift'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $redirect = $giftId ? 'edit' : 'new';
                $this->_redirect('*/*/'.$redirect, array('id' => $giftId));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $giftId = $this->getRequest()->getParam('id');

        if (!$this->_checkUpdateable($giftId))
            return false;

        if ($giftId) {
            try {
                Mage::getModel('Ved_Agent/agentredemptiongift')
                    ->setId($giftId)
                    ->setIsDeleted(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Redemption Gift'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $giftId));
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _checkUpdateable($giftId)
    {
        $redemptionCount = Mage::getModel('Ved_Agent/agentredemption')
            ->getCollection()
            ->addFieldToFilter('redemption_gift_id', $giftId)
            ->count();

        if ($redemptionCount) {
            $error = Mage::helper('agent')->__('Cannot update redemption gift used');
            Mage::getSingleton('adminhtml/session')->addError($error);

            $this->_redirect('*/*/');
            return false;
        }

        return true;
    }

}
