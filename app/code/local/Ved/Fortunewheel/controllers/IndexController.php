<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 12/27/2017
 * Time: 2:23 PM
 */
class Ved_Fortunewheel_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
    public function testLoginAction()
    {
        $listCustomer = Mage::getModel("customer/customer")
            ->getCollection()
            ->addAttributeToFilter('store_id', array('in' => array(20, 21, 23)))
            ->load();

        $customer_id = array_rand($listCustomer->toArray());
        $customer = Mage::getModel("customer/customer")
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->load($customer_id);

        Mage::helper('tek_authentication')->loginCustomer($customer);
        $result = array('status' => true, 'customer_id' => $customer_id);
        $this->getResponse()->setBody(json_encode($result));
    }
     */

    public function isRollAction()
    {
        $customer_id = $this->getRequest()->getParam('customer_id');
        $option = Mage::getModel('core/variable')->loadByCode('fortune_option')->getValue('plain');
        if ($this->checkIfExistWheel($customer_id) && $option) {
            $wheel = $this->isRollResult($customer_id);
            $result = array(
                'status' => 'is_roll',
                'data' => array(
                    'customer_id' => $customer_id,
                    'wheel_id' => $wheel->getEntityId(),
                    'rule_id' => $wheel->getRuleId(),
                    'rule_name' => Mage::getModel('Ved_Fortunewheel/wheelInfoSalesrule')->load($wheel->getRuleId())->getRuleName(),
                    'coupon_id' => $wheel->getCouponId(),
                    'coupon' => Mage::getModel('salesrule/coupon')->load($wheel->getCouponId())->getCode()
                )
            );
        } else {
            $result = array('status' => 'can_roll');
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function indexAction()
    {
        $helper = Mage::helper('wheel');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $option = Mage::getModel('core/variable')->loadByCode('fortune_option')->getValue('plain');

        try {
            $connection->beginTransaction();
            $customer_id = $this->getRequest()->getParam('customer_id');
            if (!$option) {
                $query = 'UPDATE customer_entity SET process_lock= NULL WHERE entity_id=' . $customer_id . ';';
                $connection->query($query);
            }

            /*check if this user roll, send old result*/
            if ($this->checkIfExistWheel($customer_id) && $option) {
                $result = $this->sentIfRoll($customer_id);
            } else {
                /**Process lock to block 1 user send multi request*/
                $process_lock = uniqid(time() . '_');
                $query = 'UPDATE customer_entity SET process_lock="' . $process_lock .
                    '" WHERE entity_id=' . $customer_id . ' AND process_lock is NULL';
                $affected_rows = $connection->exec($query);

                // If is processing
                if (!$affected_rows) {
                    $result = array(
                        'status' => 'processing',
                    );
                }
                else {
                    if ($this->checkIfExistWheel($customer_id) && $option) {
                        $result = $this->sentIfRoll($customer_id);
                    }
                    else {
                        // get rule id
                        $rule_id = $helper->getSaleRule();

                        // if do_den
                        if ($rule_id == 0) {
                            $result = $this->returnNonResult($customer_id, $rule_id);
                        } else {
                            $ruleDetail = Mage::getModel('Ved_Fortunewheel/wheelInfoSalesrule')->load($rule_id);

                            // Het coupon
                            if (!$ruleDetail->getCount()) {
                                $result = $this->returnNonResult($customer_id, $rule_id);
                            } else {
                                $couponCollection = Mage::getModel('salesrule/coupon')->getCollection()
                                    ->addFieldToFilter('rule_id', $rule_id)
                                    ->addFieldToFilter('is_sent', array('null' => true));

                                $coupon = $couponCollection->getFirstItem();

                                // Het coupon
                                if (!$coupon->getCouponId()) {
                                    $result = $this->returnNonResult($customer_id, $rule_id);
                                } else {
                                    // process_lock coupon_id
                                    $query = 'UPDATE salesrule_coupon SET is_sent="' . $customer_id .
                                        '" WHERE coupon_id=' . $coupon->getCouponId() . ' AND is_sent is NULL';
                                    $affected_rows_coupon = $connection->exec($query);

                                    if (!$affected_rows_coupon) {
                                        $result = $this->returnNonResult($customer_id, $rule_id);
                                    }
                                    else {
                                        // save info user and coupon
                                        $couponUser = $helper->saveResult($customer_id, $rule_id, $coupon->getCouponId());

                                        $ruleDetail->addData(array(
                                            'count' => $ruleDetail->getCount() - 1
                                        ))->save();

                                        $result = array(
                                            'status' => true,
                                            'data' => array(
                                                'customer_id' => $customer_id,
                                                'wheel_id' => $couponUser->getEntityId(),
                                                'rule_id' => $rule_id,
                                                'rule_name' => $ruleDetail->getRuleName(),
                                                'coupon_id' => $coupon->getCouponId(),
                                                'coupon' => $coupon->getCode()
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $this->getResponse()->setBody(json_encode($result));
            $connection->commit();
        } catch (Exception $e) {
            $result = array('status' => false, 'message' => $e->getMessage());
            $connection->rollback();
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    private function sentIfRoll($customer_id)
    {
        $wheel = $this->isRollResult($customer_id);
        // if do_den
        if ($wheel->getRuleId() == 0) {
            $result = $this->returnNonResult($customer_id, false);
        } else {
            $result = array(
                'status' => 'is_roll',
                'data' => array(
                    'customer_id' => $customer_id,
                    'wheel_id' => $wheel->getEntityId(),
                    'rule_id' => $wheel->getRuleId(),
                    'rule_name' => Mage::getModel('Ved_Fortunewheel/wheelInfoSalesrule')->load($wheel->getRuleId())->getRuleName(),
                    'coupon_id' => $wheel->getCouponId(),
                    'coupon' => Mage::getModel('salesrule/coupon')->load($wheel->getCouponId())->getCode()
                )
            );
        }
        return $result;
    }

    private function returnNonResult($customer_id, $rule_id, $is_new = true)
    {
        $helper = Mage::helper('wheel');

        if ($is_new) {
            $couponUser = $helper->saveResult($customer_id, 0);
        }

        $result = array(
            'status' => true,
            'data' => array(
                'wheel_id' => $couponUser->getEntityId(),
                'rule_id' => 0,
                'rule_name' => 'do_den'
            )
        );
        return $result;
    }

    private function checkIfExistWheel($customer_id)
    {
        $wheel = $this->isRollResult($customer_id);
        return $wheel->getEntityId() != null;
    }

    private function isRollResult($customer_id)
    {
        $wheel = Mage::getModel('Ved_Fortunewheel/wheelDetail')->getCollection()
            ->addFieldToFilter('customer_id', $customer_id)
            ->getFirstItem();
        return $wheel;
    }
}