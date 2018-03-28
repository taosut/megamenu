<?php

class SendSmsToCustomer
{
    /**
     *
     */
    public function run()
    {
        $smsCollection = Mage::getModel("ved_customer/smsmessage")
            ->getCollection()
            ->addFieldToFilter('status', 0)
            ->addFieldToFilter('timetable', ['lt' => 'NOW()'])
            ->load();
        foreach ($smsCollection as $sms) {
            /** @var Ved_Customer_Model_Smsmessage $sms $receivers */
            $receivers = $sms->getData('receiver');
            $receivers = str_replace("-", "", $receivers);
            $receivers = str_replace(array(' ', ';', ','), PHP_EOL, $receivers);
            $list = array();
            $list = $list + array_map('trim', explode(PHP_EOL, $receivers));
            $list = array_filter($list);
            $sms->addData([
                'status' => 1,
                'result' => $this->sendSMS($list, $sms->getData('content'))
            ])->save();

        }
    }

    public function sendSMS($list, $content)
    {
        $error = "";
        $count = 0;
        foreach ($list as $phonenumber) {
            if ($this->sendSMSToPhone($phonenumber, $content)) {
                $count++;
            } else {
                $error .= Mage::helper("suppliernew")->__("%s failed. Please check phone number;", $phonenumber);
            }
        }
        if (empty($error)) {
            return Mage::helper("suppliernew")->__("%s sent successfully.", $count);
        }
        return $error;
    }

    public function sendSMSToPhone($phone, $message)
    {
        return Mage::sendSMS($phone, $message);
    }
}