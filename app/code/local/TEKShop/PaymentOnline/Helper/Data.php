<?php

class TEKShop_PaymentOnline_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array|string
     * @throws Exception
     */
    public function getUrlPayment($quote)
    {
        $inputData = array(
            "cart_id" => $quote->getId(),
            "vnp_OrderInfo" => "Thanhtoan",
            "vnp_Amount" => (int)($quote->getGrandTotal()),
            "vnp_IpAddr" => Mage::helper('core/http')->getRemoteAddr(),
            "tekshop_callback_url" => Mage::getUrl('payment_online/online/callback'),
        );
        $client = new TEKShop_PaymentOnline_Helper_Client();
        $outUrl = $client->getPaymentUrl($inputData);
        return $outUrl;
    }

    /**
     * @return string
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    public function checkPaymentOnline()
    {
        $client = new TEKShop_PaymentOnline_Helper_Client();
        return $client->checkPayment();
    }

    /**
     * @return string
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    public function submitOrderPaymentOnline($orderId, $transactionId)
    {
        $client = new TEKShop_PaymentOnline_Helper_Client();
        return $client->submitOrder($orderId, $transactionId);
    }

    /**
     * @deprecated
     * @param $data
     * @throws Exception
     */
    private function validateMd5($data)
    {
        try {
            $dataInput = array_diff_key($data, ['vnp_SecureHashType' => "", 'vnp_SecureHash' => ""]);
            if ($this->getHasKey($dataInput) != $data['vnp_SecureHash']) {
                throw new Exception("Mã MD5 lỗi");
            }
        } catch (Exception $e) {
            throw new Exception("Thanh toán lỗi từ ngân hàng");
        }
    }

    /**
     * @param array $params
     * @return array
     * @throws TEKShop_PaymentOnline_Exception
     * @throws Exception
     */
    public function ipn($params)
    {
        $vnp_SecureHash = $params['vnp_SecureHash'];
        $dataInput = array_diff_key($params, ['vnp_SecureHashType' => '', 'vnp_SecureHash' => '']);
        $secureHash = $this->getHasKey($dataInput);
        if ($secureHash != $vnp_SecureHash) throw new TEKShop_PaymentOnline_Exception('Chu ky khong hop le', 97);
        if ($dataInput['vnp_ResponseCode'] == '00') {
            $returnData['RspCode'] = '00';
            $returnData['Message'] = 'Confirm Success';
        } else {
            $returnData['RspCode'] = '00';
            $returnData['Message'] = 'Confirm Success';
        }
        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote = Mage::getModel('sales/quote')->load($dataInput['vnp_TxnRef']);
        if ($quote->isEmpty()) throw new TEKShop_PaymentOnline_Exception('Order not found', 1);
        if (Mage::getModel('TEKShop_PaymentOnline/cart')->getCollection()->addFieldToFilter('quote_id', $dataInput['vnp_TxnRef'])->getFirstItem()->getId())
            throw new TEKShop_PaymentOnline_Exception('Order already confirmed', 2);
        return $returnData;
    }

    /**
     * @param $data
     * @return string
     * @throws Exception
     */
    private function getHasKey(&$data)
    {
        $hashSecret = (string)Mage::getConfig()->getNode('global/pay_online/vnpay_hash_secret');
        ksort($data);
        if (!$hashSecret) throw new Exception("Please Config Environment.");
        $hashData = urldecode(http_build_query($data));
        return md5($hashSecret . $hashData);
    }

    /**
     * @param $data
     * @param bool $isMD5
     * @param bool $isPay
     * @return string
     * @throws Exception
     */
    private function getVNPayUrl($data, $isMD5 = false, $isPay = false)
    {
        $data = array_diff_key($data, ['vnp_SecureHashType' => '', 'vnp_SecureHash' => '']);
        $hashSecret = $this->getHasKey($data);
        if ($isMD5) $data['vnp_SecureHashType'] = "MD5";
        $data['vnp_SecureHash'] = $hashSecret;
        if ($isPay)
            $baseVNPayUrl = (string)Mage::getConfig()->getNode('global/pay_online/vnpay_url_pay');
        else
            $baseVNPayUrl = (string)Mage::getConfig()->getNode('global/pay_online/vnpay_url_api');
        if (!$baseVNPayUrl) throw new Exception("Please Config Environment.");
        return $baseVNPayUrl . "?" . http_build_query($data);
    }

    /**
     * Linkify all plaintext to become link
     *
     * @param $desc
     *
     * @author hoang.pt
     */
    public function linkify($desc)
    {
        $keywordsStr = Mage::getModel('core/variable')->loadByCode('seo_linkify')->getValue('plain');
        $keywords = json_decode($keywordsStr, true);

        //replace all text
        foreach ($keywords as $key => $href) {
            $reg = "/<.*?>(*SKIP)(*FAIL)|" . $key . "/i";
            $desc = preg_replace($reg,
                '<a class="linkify" title="' . $key . '" href="' . $keywords[$key] . '" target="_blank" rel="author">$0</a>',
                $desc);
        }

        return $desc;
    }
}