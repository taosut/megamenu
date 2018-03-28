<?php

class ReceiveMessageQueue
{
    /**
     * @use command.php
     */
    public function run()
    {
        Mage::receiveMessageQueue(function ($message) {
            /**
             * @var PhpAmqpLib\Message\AMQPMessage $message
             */
            Mage::saveLogMessageQueue(
                2,
                'teko.sale',
                $message->delivery_info['routing_key'],
                $message->body,
                $message->get_properties()
            );
            /**
             * @var Teko_Amp_Model_Queue $queue
             */
            $queue = Mage::getModel('teko_amp/queue');
            $queue->setData([
                'queue' => 'teko.sale',
                'routing_key' => $message->delivery_info['routing_key'],
                'message' => $message->body,
                'properties' => json_encode($message->get_properties()),
                'count' => 0,
                'type' => 1,
                'created_at' => time(),
            ])->save();
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        });
    }
}