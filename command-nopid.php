<?php
require_once 'vendor/autoload.php';
require_once 'app/Mage.php';
require_once 'app/cron/ProcessQueue.php';
require_once 'app/cron/RecallMassageQueuePurchase.php';
require_once 'app/cron/ReceiveMessageQueue.php';
require_once 'app/cron/PushMessageQueue.php';
require_once 'app/cron/CheckSumQueue.php';
require_once 'app/cron/AlertOverdueOrder.php';
require_once 'app/cron/AdminSso.php';

Mage::app()->setCurrentStore('admin');

$command = new \Commando\Command();
// Define first option
$command->option()
    ->require()
    ->describedAs('A Class\'s name');
$arguments = $command->getArguments();
/** @var  Commando\Option $option */
$option = reset($arguments);
$queue = $option->getValue();
if (class_exists($queue)) {
    try {
        $running = new $queue;
        $running->run();
    } catch (Exception $exception) {
        Mage::log("\n" . $exception->__toString(), Zend_Log::ERR, 'log_error_message_queue.log');
        print $exception->getMessage();
    }
} else {
    print "Process: $queue don't support" . PHP_EOL;
    exit;
}