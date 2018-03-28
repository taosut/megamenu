<?php

class PushMessageQueue
{
    /**
     * @use command.php
     */
    public function run()
    {
        /**
         * @var Teko_Amp_Model_Queue[] $queue
         */
        $queue = Mage::getModel('teko_amp/queue')
            ->getCollection()
            ->addFieldToFilter('count', ['lt' => 3])
            ->addFieldToFilter('type', 2)
            ->load();

        foreach ($queue as $item) {
            try {
                // Check if exist routing_key in teko_amp_message_id table
                $message_id = Mage::getModel('teko_amp/message')
                    ->getCollection()->addFieldToFilter('routing_key', $item->getRoutingKey())->getFirstItem();

                if ($message_id->isEmpty()) { // If not exist
                    $message_id->addData([
                        'routing_key' => $item->getRoutingKey(),
                        'message_id' => 1
                    ]);
                } else { // If exist
                    $message_id->setMessageId($message_id->getMessageId() + 1);
                }
                $message_id->save();

                $this->validateJson($item->getMessage());

                if ($item->getRoutingKey() == 'teko.sms') {
                    $exchange = 'teko.sms';
                    Mage::pushMessageQueue($item->getMessage(), $item->getRoutingKey(), ['message_id' => $message_id->getMessageId(), 'timestamp' => time()], $exchange);
                } else {
                    Mage::pushMessageQueue($item->getMessage(), $item->getRoutingKey(), ['message_id' => $message_id->getMessageId(), 'timestamp' => time()]);
                }
                $item->delete();
            } catch (Exception $e) {
                Mage::logException($e);
                $item->setCount($item->getCount() + 1)->save();
            }
        }
    }

    public function validateJson($json)
    {
        if (strpos($json, '"productId":null') !== false)
            throw new Queue_Exception("Message has a product is null");
        if (strpos($json, '"addressCode":null') !== false)
            throw new Queue_Exception("Message has a product is null");
    }
}