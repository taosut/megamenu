<?php

/**
 * Class Teko_Amp_Model_Queue
 * @method string getRoutingKey()
 * @method string getMessage()
 * @method $this setCount(int $int)
 */
class Teko_Amp_Model_Queue extends Mage_Core_Model_Abstract
{
    /**
     * @throws Exception
     * @return array
     */
    public function allowProcessQueue()
    {
        $config = Mage::registry('amp_config');
        if (!in_array($this->getData('routing_key'), $config))
            throw new Exception('Routing key is not support');

        $properties = json_decode($this->getData('properties'), true);
        if (is_array($properties) && isset($properties['message_id'])) {
            $isProcesses = Mage::getModel('teko_amp/queue_property')->getCollection()
                ->addFieldToFilter('routing_key', $this->getData('routing_key'))
                ->addFieldToFilter('message_id', $properties['message_id'])
                ->getFirstItem();
            if ($isProcesses->getId()) {
                throw new Exception('Message Queue processed');
            }
            return $properties;
        }
        throw new Exception('Properties not found');
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('teko_amp/queue');
    }
}
