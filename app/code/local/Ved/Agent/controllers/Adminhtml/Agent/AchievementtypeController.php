<?php

class Ved_Agent_Adminhtml_Agent_AchievementtypeController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Manage Achievement Types'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_achievementtype');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_achievementtype'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $typeId = $this->getRequest()->getParam('id');
        $type = Mage::getModel('Ved_Agent/agentachievementtype')->load($typeId);

        if ($type->getId() || $typeId == 0) {
            $type->channel_name = Mage::getModel('Ved_Agent/agentchannel')
                ->load($type->channel_id)
                ->getChannelName();

            $type->created_user = Mage::getModel('admin/user')
                ->load($type->created_by)
                ->getEmail();

            $type->updated_user = Mage::getModel('admin/user')
                ->load($type->updated_by)
                ->getEmail();

            Mage::register('achievement_type_data', $type);

            $this->loadLayout();

            $this->_setActiveMenu('agent/agent_achievementtype');
            $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_achievementtype_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Achievement type does not exist');

            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $typeId = $this->getRequest()->getParam('id');

        // if (!$this->_checkUpdateable($typeId))
        //     return false;

        // Forbidden to create Achievement Type (AT) which has same name, in same channel with exist AT
        $checkExist = Mage::getModel('Ved_Agent/agentachievementtype')
            ->getCollection()
            ->addFieldToFilter('is_deleted', 0)
            ->addFieldToFilter('channel_id', $postData['channel_id'])
            ->addFieldToFilter('achievement_type_name', $postData['achievement_type_name']);
        if ($typeId) {
            $checkExist->addFieldToFilter('achievement_type_id', array('neq' => $typeId));
        }
        if ($checkExist->count()) {
            $error = Mage::helper('agent')->__('Already exist achievement type with same name and channel');
            Mage::getSingleton('adminhtml/session')->addError($error);
            $this->_forward('edit');
            return;
        }

        if ($postData) {
            try {
                $typeModel = Mage::getModel('Ved_Agent/agentachievementtype');

                if (!$typeId) {
                    $typeModel->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId());
                }

                $typeModel->addData($postData)
                    ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                    ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                    ->setId($typeId)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Agent Achievement Type'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $typeId));
                return;
            }

            $this->_redirect('*/*/');
        }
    }

    public function deactiveAction()
    {
        $typeId = $this->getRequest()->getParam('id');

        if ($typeId) {
            try {
                $activeValue = $this->getRequest()->getParam('value');
                $type = Mage::getModel('Ved_Agent/agentachievementtype')->load($typeId);
                $type->setIsActive($activeValue)->save();

                if ($activeValue) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deactived Agent Achievement Type'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Actived Agent Achievement Type'));
                }

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $typeId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $typeId = $this->getRequest()->getParam('id');

        if (!$this->_checkUpdateable($typeId))
            return false;

        if ($typeId) {
            try {
                $type = Mage::getModel('Ved_Agent/agentachievementtype')->load($typeId);
                $type->setIsDeleted(1)->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Agent Achievement Type'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $typeId));
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _checkUpdateable($typeId)
    {
        $type = Mage::getModel('Ved_Agent/agentachievementtype')->load($typeId);
        $achievementCount = Mage::getModel('Ved_Agent/agentachievement')
            ->getCollection()
            ->addFieldToFilter('is_deleted', 0)
            ->addFieldToFilter('achievement_type_id', $typeId)
            ->count();

        if ($type->getId() && $achievementCount) {
            $error = Mage::helper('agent')->__('Cannot update achievement type having achievement');
            Mage::getSingleton('adminhtml/session')->addError($error);
            $this->_redirect('*/*/');
            return false;
        }

        return true;
    }

}
