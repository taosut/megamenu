<?php

class Ved_Agent_Adminhtml_Agent_InfoController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Tekshop Agent'))->_title($this->__('Agent Info'));

        $this->loadLayout();

        $this->_setActiveMenu('agent/agent_info');
        $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_info'));

        $this->renderLayout();
    }

    public function pointHistoryGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('agent/adminhtml_info_edit_tabs_point_grid')->toHtml()
        );
    }

    public function referedGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('agent/adminhtml_info_edit_tabs_refer_grid')->toHtml()
        );
    }

    public function editAction()
    {
        $agentId = $this->getRequest()->getParam('id');
        $agent = Mage::getModel('customer/customer')->load($agentId);

        if ($agent->getId() || $agentId == 0) {
            if ($agent->getId()) {
                Mage::helper('agent')->applyAgentInfoToModel($agent);
            }
            Mage::register('agent_data', $agent);

            $this->loadLayout();

            $this->_setActiveMenu('agent/agent_info');
            $this->_addContent($this->getLayout()->createBlock('agent/adminhtml_info_edit'))
                ->_addLeft($this->getLayout()->createBlock('agent/adminhtml_info_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agent')->__('Agent does not exist'));

            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        $agentId = $this->getRequest()->getParam('id');

        if ($postData) {
            try {
                $agent = Mage::getModel('customer/customer')->load($agentId);

                if ($agent->getId()) {
                    $agentNewInfo = Mage::helper('agent')->getNewAgentInfo($agent, $postData);

                    $agent->setAgentInfo($agentNewInfo)->save();
                } else {
                    // TODO: Create new agent
                    $agent = Mage::getModel('customer/customer');
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Saved Agent Information'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $agentId));
                return;
            }

            $this->_redirect('*/*/');
        }
    }

    public function deleteAction()
    {
        $agentId = $this->getRequest()->getParam('id');

        if ($agentId) {
            try {
                $agent = Mage::getModel('customer/customer')->load($agentId);

                $transaction = Mage::getModel('core/resource_transaction');

                $transaction->addObject(
                    $agent->setIsAgentDeleted(1)
                );

                $achievements = Mage::getModel('Ved_Agent/agentachievement')
                    ->getCollection()
                    ->addFieldToFilter('user_id', $agentId);
                foreach ($achievements as $achievement) {
                    $transaction->addObject(
                        $achievement->setIsDeleted(1)
                    );
                }

                $accounts = Mage::getModel('Ved_Agent/agentaccount')
                    ->getCollection()
                    ->addFieldToFilter('user_id', $agentId);
                foreach ($accounts as $account) {
                    $transaction->addObject(
                        $account->setIsDeleted(1)
                    );
                }

                $transaction->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Deleted Agent'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $agentId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function recoverAction()
    {
        $agentId = $this->getRequest()->getParam('id');

        if ($agentId) {
            try {
                $agent = Mage::getModel('customer/customer')->load($agentId);

                $transaction = Mage::getModel('core/resource_transaction');

                $transaction->addObject(
                    $agent->setIsAgentDeleted(0)
                );

                $achievements = Mage::getModel('Ved_Agent/agentachievement')
                    ->getCollection()
                    ->addFieldToFilter('user_id', $agentId);
                foreach ($achievements as $achievement) {
                    $transaction->addObject(
                        $achievement->setIsDeleted(0)
                    );
                }

                $accounts = Mage::getModel('Ved_Agent/agentaccount')
                    ->getCollection()
                    ->addFieldToFilter('user_id', $agentId);
                foreach ($accounts as $account) {
                    $transaction->addObject(
                        $account->setIsDeleted(0)
                    );
                }

                $transaction->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Recovered Agent'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/edit', array('id' => $agentId));
            }
        }

        $this->_redirect('*/*/');
    }

    public function massChangePointAction()
    {
        $agentIds = $this->getRequest()->getParam('agent_point_id');

        if (!is_array($agentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('agent')->__('Please select agents'));
        } else {
            try {
                $point = $this->getRequest()->getParam('point');
                $reason = $this->getRequest()->getParam('reason');
                $type = $this->getRequest()->getParam('type');

                if ($type == Ved_Agent_Model_Agentachievementhistory::OTHER_SUB) {
                    $point = -$point;
                }

                $transaction = Mage::getModel('core/resource_transaction');

                foreach ($agentIds as $agentId) {
                    $agent = Mage::getModel('customer/customer')->load($agentId);
                    $agentNewInfo = Mage::helper('agent')->getAgentInfoAfterAddPoint($agent, $point);
                    $transaction->addObject(
                        $agent->setAgentInfo($agentNewInfo)
                    );

                    $transaction->addObject(
                        Mage::getModel('Ved_Agent/agentpointhistory')
                            ->setUserId($agentId)
                            ->setPoint(abs($point))
                            ->setType($type)
                            ->setObjectId(Mage::getSingleton('admin/session')->getUser()->getUserId())
                            ->setDetail($reason)
                            ->setAccumulatePoint(json_decode($agentNewInfo)->available_point)
                            ->setCreatedAt(Mage::getSingleton('core/date')->timestamp())
                    );
                }

                $transaction->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('agent')->__('Successfully Mass Change Agent Point'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*');
            }
        }

        $this->_redirect('*/*');
    }

}
