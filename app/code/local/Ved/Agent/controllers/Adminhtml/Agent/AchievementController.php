<?php

class Ved_Agent_Adminhtml_Agent_AchievementController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Agent Achievements'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_achievement');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_achievement'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('agent/adminhtml_achievement_grid')->toHtml()
        );
    }

    public function editAction()
    {
        $achievementId = $this->getRequest()->getParam('id');
        $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($achievementId);

        if (!$this->_checkUpdateable($achievementId))
            return false;

        if ($this->_checkOnlyUpdateStatusByID($achievementId)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agent')->__('Cannot update declined achievement (except status)'));
            $this->_redirect('*/*/');
            return false;
        }

        if ($achievement->getId()) {
            $achievement->fullname = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('entity_id', $achievement->getUserId())
                ->addAttributeToSelect('firstname')
                ->getFirstItem()
                ->getFirstname();

            $achievement->achievement_type_name = Mage::getModel('Ved_Agent/agentachievementtype')
                ->getCollection()
                ->addFieldToFilter('achievement_type_id', $achievement->getAchievementTypeId())
                ->getFirstItem()
                ->getAchievementTypeName();

            $achievement->status_label = array(
                Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY => Mage::helper('agent')->__('Waiting to Verify'),
                Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE => Mage::helper('agent')->__('Require to Update'),
                Ved_Agent_Model_Agentachievement::DECLINED => Mage::helper('agent')->__('Declined'),
                Ved_Agent_Model_Agentachievement::VERIFIED => Mage::helper('agent')->__('Verified'))[$achievement->getStatus()];

            $achievement->account_name = Mage::getModel('Ved_Agent/agentaccount')
                ->getCollection()
                ->addFieldToFilter('account_id', $achievement->getAccountId())
                ->getFirstItem()
                ->getAccountName();

            $achievement->updated_user = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', $achievement->getUpdatedBy())
                ->getFirstItem()
                ->getEmail();

            Mage::register('achievement_data', $achievement);

            $this->loadLayout();

            $this->_setActiveMenu('agent/agent_achievement');
            $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_achievement_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agent')->__('Achievement does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $achievementId = $this->getRequest()->getParam('id');

        $achievementModel = Mage::getModel('Ved_Agent/agentachievement');
        $achievement = $achievementModel->load($achievementId);

        if (!$this->_checkUpdateable($achievementId))
            return false;

        if ($postData) {
            try {
                $transaction = Mage::getModel('core/resource_transaction');

                if ($achievement->getStatus() != Ved_Agent_Model_Agentachievement::VERIFIED && $postData['status'] == Ved_Agent_Model_Agentachievement::VERIFIED) {
                    $point = Mage::getModel('Ved_Agent/agentachievementtype')
                        ->getCollection()
                        ->addFieldToFilter('achievement_type_id', $achievement->getAchievementTypeId())
                        ->getFirstItem()
                        ->getPoint();

                    $agent = Mage::getModel('customer/customer')->load($achievement->getUserId());

                    $accumulatePoint = json_decode(Mage::helper('agent')
                        ->getAgentInfoAfterAddPoint($agent, $point))->available_point;

                    $achievementAccount = Mage::getModel('Ved_Agent/agentaccount')
                        ->load($achievement->getAccountId());
                    $achievementChannel = Mage::getModel('Ved_Agent/agentchannel')
                        ->load($achievementAccount->getChannelId());
                    $isSocialChannel = $achievementChannel->getChannelType() == Ved_Agent_Model_Agentchannel::SOCIAL;
                    $accountLink = $isSocialChannel ? $this->_getTargetLink($achievementAccount->getAccountName()) : $achievementAccount->getAccountName();

                    $transaction->addObject(
                        $agent->setAgentInfo(Mage::helper('agent')->getAgentInfoAfterAddPoint($agent, $point))
                    );

                    $transaction->addObject(
                        Mage::getModel('Ved_Agent/agentpointhistory')
                            ->setUserId($achievement->getUserId())
                            ->setPoint($point)
                            ->setType(Ved_Agent_Model_Agentachievementhistory::VERIFY_ACHIEVEMENT)
                            ->setObjectId($achievement->getId())
                            ->setDetail("Được duyệt bài viết {$this->_getTargetLink($achievement->getLink())} với account {$accountLink}")
                            ->setAccumulatePoint($accumulatePoint)
                            ->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                    );
                }

                if ($this->_checkOnlyUpdateStatus($achievement->getStatus())) {
                    $transaction->addObject(
                        $achievementModel->setStatus($postData['status'])
                        ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                        ->setId($achievementId)
                    );
                } else {
                    $transaction->addObject(
                        $achievementModel->addData($postData)
                        ->setUpdatedAt(Mage::getSingleton('core/date')->timestamp())
                        ->setUpdatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                        ->setId($achievementId)
                    );
                }

                $transaction->addObject(
                    Mage::getModel('Ved_Agent/agentachievementhistory')
                        ->setAchievementId($achievement->getAchievementId())
                        ->setStatus($postData['status'])
                        ->setComment($postData['comment'])
                        ->setCreatedBy(Mage::getSingleton('admin/session')->getUser()->getUserId())
                        ->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                );

                $transaction->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Agent Achievement'));

                $this->_redirectReferer();
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $achievementId));
                return;
            }

            $this->_redirectReferer();
        }
    }

    private function _getTargetLink($link)
    {
        return "<a class=\"agent-table-link\" href=\"{$link}\" target=\"_blank\">{$link}</a>";
    }

    public function deleteAction()
    {
        $achievementId = $this->getRequest()->getParam('id');
        $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($achievementId);

        if (!$this->_checkUpdateable($achievementId))
            return false;

        if ($achievementId) {
            try {
                Mage::getModel('Ved_Agent/agentachievement')
                    ->setId($achievementId)
                    ->setIsDeleted(1)
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Achievement'));

                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $achievementId));
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _checkOnlyUpdateStatusByID($achievementId)
    {
        $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($achievementId);

        if (!$achievement->getId()) {
            return false;
        }

        return $this->_checkOnlyUpdateStatus($achievement->getStatus());
    }
    protected function _checkOnlyUpdateStatus($status)
    {
        return $status == Ved_Agent_Model_Agentachievement::DECLINED;
    }

    protected function _checkUpdateable($achievementId)
    {
        $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($achievementId);

        if ($achievement->getId() && $achievement->getStatus() == Ved_Agent_Model_Agentachievement::VERIFIED) {
            $error =  Mage::helper('agent')->__('Cannot update verified achievement');
            Mage::getSingleton('adminhtml/session')->addError($error);
            $this->_redirect('*/*/');
            return false;
        }

        return true;
    }

}
