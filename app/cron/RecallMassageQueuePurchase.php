<?php

class RecallMassageQueuePurchase
{
    public function run()
    {
        Mage::app();
        echo "Vui long dien id de day lai message quee\r";
        echo "Hoac go \"quit\" de thoat \n";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 'quit') {
            echo "ABORTING!\n";
            exit;
        }
        $id = trim($line);
        $purchase = Mage::getModel('ved_purchase/purchase')->load($id);
        fclose($handle);
        if ($purchase->getId())
            $purchase->callMessageQueue();
        else
            echo "Khong tim thay purchase \n";
        echo "\n";
        echo "Done\n";
    }
}