<?php

class Ved_Agent_Adminhtml_Agent_ChannelController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Agent Channels'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_channel');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_channel'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $channelId = $this->getRequest()->getParam('id');
        $channel = Mage::getModel('Ved_Agent/agentchannel')->load($channelId);

        if ($channel->getId() || $channelId == 0) {
            $channel->created_user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', $channel->created_by)
                ->getFirstItem()
                ->getEmail();

            $channel->updated_user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', $channel->updated_by)
                ->getFirstItem()
                ->getEmail();

            Mage::register('channel_data', $channel);

            $this->loadLayout();

            $this->_setActiveMenu('agent/agent_channel');
            $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_channel_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Channel does not exist');

            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $channelId = $this->getRequest()->getParam('id');

        // Forbidden to create Channel which has same name, same type
        $checkExist = Mage::getModel('Ved_Agent/agentchannel')
            ->getCollection()
            ->addFieldToFilter('is_deleted', 0)
            ->addFieldToFilter('channel_name', $postData['channel_name'])
            ->addFieldToFilter('channel_type', $postData['channel_type']);
        if ($channelId) {
            $checkExist->addFieldToFilter('channel_id', array('neq' => $channelId));
        }
        if ($checkExist->count()) {
            $error = Mage::helper('agent')->__('Already exist channel with same name and type');
            Mage::getSingleton('adminhtml/session')->addError($error);
            $this->_forward('edit');
            return;
        }

        if ($postData) {
            try {
                $channelModel = Mage::getModel('Ved_Agent/agentchannel');

                if (!$channelId) {
                    $channelModel->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId());
                }

                $channelModel->addData($postData)
                    ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                    ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                    ->setId($channelId)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Channel'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $channelId));
                return;
            }

            $this->_redirect('*/*/');
        }

        $this->_redirect('*/*/');
    }

    public function deactiveAction()
    {
        $channelId = $this->getRequest()->getParam('id');

        if ($channelId) {
            try {
                $activeValue = $this->getRequest()->getParam('value');
                Mage::getModel('Ved_Agent/agentchannel')
                    ->setId($channelId)
                    ->setIsActive($activeValue)
                    ->save();

                if ($activeValue) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deactived Channel'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Actived Channel'));
                }

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $channelId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $channelId = $this->getRequest()->getParam('id');

        if (!$this->_checkUpdateable($channelId))
            return false;

        if ($channelId) {
            try {
                Mage::getModel('Ved_Agent/agentchannel')
                    ->setId($channelId)
                    ->setIsDeleted(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Channel'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $channelId));
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _checkUpdateable($channelId)
    {
        $accountCount = Mage::getModel('Ved_Agent/agentaccount')
            ->getCollection()
            ->addFieldToFilter('channel_id', $channelId)
            ->addFieldToFilter('is_deleted', 0)
            ->count();
        if ($accountCount) {
            $error = Mage::helper('agent')->__('Cannot update channel having accounts');
            Mage::getSingleton('adminhtml/session')->addError($error);

            $this->_redirect('*/*/');
            return false;
        }

        $typeCount = Mage::getModel('Ved_Agent/agentachievementtype')
            ->getCollection()
            ->addFieldToFilter('channel_id', $channelId)
            ->addFieldToFilter('is_deleted', 0)
            ->count();
        if ($typeCount) {
            $error = Mage::helper('agent')->__('Cannot update channel having achievement types');
            Mage::getSingleton('adminhtml/session')->addError($error);

            $this->_redirect('*/*/');
            return false;
        }

        return true;
    }

}
