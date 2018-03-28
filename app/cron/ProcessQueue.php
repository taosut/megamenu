<?php

class ProcessQueue
{
    protected $createdAt;

    /**
     * ProcessQueue constructor.
     */
    public function __construct()
    {
        $this->getConfigProcess();
        $this->loadLib(dirname(__FILE__) . '/Queue');

    }

    public function runTest($data, $properties, $routing_key)
    {
        /** @var Teko_Amp_Model_Queue $queue */
        $queue = Mage::getModel('teko_amp/queue')->setData([
            'routing_key' => $routing_key,
            'message' => $data,
            'properties' => $properties,
        ])->save();
        $queue->allowProcessQueue();
        $class = $this->getClassFromRoutingKey($queue->getRoutingKey());
        if (class_exists($class)) {
            $process = new  $class;
            $process->process($queue->getMessage());
        } else {
            throw new Exception("Routing ");
        }
    }

    /**
     * @use command.php
     */
    public function run($lt = 0)
    {
        if ($lt >= 3) return;
        /** @var Teko_Amp_Model_Queue[] $queue */
        $queue = Mage::getModel('teko_amp/queue')
            ->getCollection()
            ->addFieldToFilter('count', $lt)
            ->addFieldToFilter('type', 1)
            ->setPageSize(100)
            ->setOrder('id', 'ASC')
            ->load();
        if (count($queue) == 0) $this->run($lt + 1);
        foreach ($queue as $item) {
            /** @var Queue_Exception $e */
            try {
                print 'Processing: ' . $item->getId() . PHP_EOL;
                $property = $item->allowProcessQueue();
                $class = $this->getClassFromRoutingKey($item->getRoutingKey());
                if (class_exists($class)) {
                    $process = new  $class;
                    if (method_exists($class, 'setCreatedAt')) {
                        $process->setCreatedAt($item->getData('created_at'));
                    }
                    $process->process($item->getMessage());
                } else {
                    throw new Exception("Queue $class not found!");
                }
                Mage::getModel('teko_amp/queue_property')->addData([
                    'routing_key' => $item->getRoutingKey(),
                    'message_id' => $property['message_id']
                ])->save();
                $item->delete();
            } catch (Exception $e) {
                $item->setCount($item->getCount() + 1)
                    ->setException($e->__toString())
                    ->save();
            }
        }
    }

    private function getClassFromRoutingKey($routingKey)
    {
        $data = [];
        foreach (explode('.', $routingKey) as $text)
            $data[] = ucwords($text);
        return 'Queue_' . implode('_', $data);
    }

    /**
     * @param int $id
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function loadOrderOrFail($id)
    {
        $order = new Mage_Sales_Model_Order();
        $order->load($id);
        if ($order->isEmpty()) throw new Exception("Order not fail");
        return $order;
    }

    /*
 * @param array[] $data
 * @param array[] $key
 * @return array
 */
    protected function getArrayByData($data, $key)
    {
        $result = [];
        foreach ($data as $value) {
            if (isset($value[$key])) $result[] = $value[$key];
        }
        return $result;
    }

    /**
     * Load Lib
     * @param $root
     */
    private function loadLib($root)
    {
        foreach (glob($root . '/*') as $filename) {
            if (is_file($filename)) include_once $filename;
            if (is_dir($filename)) $this->loadLib($filename);
        }
    }

    private function getConfigProcess()
    {
        if (!Mage::registry('amp_config')) {
            /** @var Mage_Catalog_Model_Resource_Product_Collection $config */
            $config = Mage::getResourceModel('teko_amp/config_collection')->addFieldToFilter('status', true)->load();
            $amp_config = $config->toArray(['routing_key']);
            Mage::register('amp_config', array_column($amp_config['items'], 'routing_key'));
        }
    }

}