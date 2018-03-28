<?php

class Ved_Agent_Adminhtml_Agent_AccountController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Agent Account'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_account');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_account'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('agent/adminhtml_account_grid')->toHtml()
        );
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $accountId = $this->getRequest()->getParam('id');

        $accountModel = Mage::getModel('Ved_Agent/agentaccount');
        $account = $accountModel->load($accountId);

        if (!$this->_checkUpdateable($accountId))
            return false;

        if ($postData) {
            try {
                $transaction = Mage::getModel('core/resource_transaction');

                $transaction->addObject(
                    $account->addData($postData)
                        ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                        ->setId($accountId)
                );

                $transaction->addObject(
                    Mage::getModel('Ved_Agent/agentaccounthistory')
                        ->addData($postData)
                        ->setAccountId($accountId)
                        ->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                );

                $transaction->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Agent Account'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $accountId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $account = Mage::getModel('Ved_Agent/agentaccount')->load($accountId);

        if (!$this->_checkUpdateable($accountId))
            return false;

        if ($accountId) {
            try {
                Mage::getModel('Ved_Agent/agentaccount')
                    ->setId($accountId)
                    ->setIsDeleted(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Agent Account'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $accountId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function viewHistoryAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        if (!$accountId) {
            $this->_redirect('*/*/');
        }

        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Agent Account'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_account');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_account_history'));

        $this->renderLayout();
    }

    protected function _checkUpdateable($accountId)
    {
        $account = Mage::getModel('Ved_Agent/agentaccount')->load($accountId);
        $achievementNumber = Mage::getModel('Ved_Agent/agentachievement')
            ->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('is_deleted', 0)
            ->count();

        if ($account->getId() && $achievementNumber) {
            $error = Mage::helper('agent')->__('Cannot update account having achievements');
            Mage::getSingleton('adminhtml/session')->addError($error);
            $this->_redirect('*/*/');
            return false;
        }

        return true;
    }

}
