<?php

/**
 * Class TEKShop_PaymentOnline_Helper_Client
 * @property  array node
 */
class TEKShop_PaymentOnline_Helper_Client extends Varien_Http_Client
{
    protected $config;

    /**
     * TEKShop_PaymentOnline_Helper_Client constructor.
     * @param null $uri
     * @param null $config
     */
    public function __construct($uri = null, $config = null)
    {
        $this->node = (array)Mage::getConfig()->getNode('global/pay_online');
        parent::__construct($uri = null, $config = null);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getPaymentGatewayInfo()
    {
        $this->setPath($this->node['gateway_info']);
        $this->setMethod(Varien_Http_Client::GET);
        $response = $this->request();
        if ($response->isSuccessful()) {
            $responseBody = json_decode($response->getBody());
            if (isset($responseBody->payment_url))
                return $responseBody->payment_url;
            throw new Exception("Not found url for payment");
        }
        throw new Exception("Connect to gateway error");
    }

    /**
     * @return string
     * @param array $data
     * @throws Exception
     */
    public function getPaymentUrl($data)
    {
        $pathUrl = $this->getPaymentGatewayInfo();
        $this->setPath($pathUrl);
        $this->setMethod(Varien_Http_Client::POST);
        $this->setParameterPost($data);
        $response = $this->request();
        if ($response->isSuccessful()) {
            $responseBody = json_decode($response->getBody());
            if ($responseBody->error_code == 200) {
                Teko::getSession()->setData('payment_online_transaction_id', $responseBody->transaction_id);
                return $responseBody->payment_url;

            }
            throw new Exception($responseBody->message, $responseBody->error_code);
        }
        throw new Exception("Connect to gateway error");
    }

    /**
     * @param string $path
     * @throws Zend_Http_Client_Exception
     */
    public function setPath($path)
    {
        $url = $this->node['base_url'] . ($path[0] != DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : "") . $path;
        $url .= $this->node['string_version'];
        $this->setUri($url);
    }

    /**
     * @return string
     * @throws Zend_Http_Client_Exception
     * @throws Exception
     */
    public function checkPayment()
    {
        $transactionId = Teko::getSession()->getData('payment_online_transaction_id', true);
        $this->setPath($this->node['check_payment']);
        $this->setMethod(Varien_Http_Client::POST);
        $this->setParameterPost(['transaction_id' => $transactionId]);
        $response = $this->request();
        if ($response->isSuccessful()) {
            $responseBody = json_decode($response->getBody());
            if ($responseBody->transaction_status_code == '00')
                return $responseBody;
            else
                throw new Exception($responseBody->transaction_status_description, $responseBody->transaction_status_code);
        }
        throw new Exception("Connect to gateway error");
    }

    /**
     * @param null $method
     * @return Zend_Http_Response
     */
    public function request($method = null)
    {
        $response = parent::request($method);
        $contentLog = json_encode([
            'method' => $this->method,
            'param' => $this->getParam(),
            'url' => $this->getUri(true),
            'response' => $response->getBody(),
        ], JSON_UNESCAPED_UNICODE);
        Mage::getModel('TEKShop_PaymentOnline/Log')->addData([
            'content' => $contentLog,
            'ip' => Mage::helper('core/http')->getRemoteAddr(),
            'created_at' => now(),
        ])->save();
        return $response;
    }

    /**
     * @return array
     */
    protected function getParam()
    {
        if ($this->method == Varien_Http_Client::POST) return $this->paramsPost;
        return $this->paramsGet;
    }

    /**
     * @param $orderId
     * @param $transactionId
     * @return mixed
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    public function submitOrder($orderId, $transactionId)
    {
        $this->setPath($this->node['submit_order']);
        $this->setMethod(Varien_Http_Client::GET);
        $this->setParameterGet(['transaction_id' => $transactionId, 'order_id' => $orderId]);
        $response = $this->request();
        if ($response->isSuccessful()) {
            $responseBody = json_decode($response->getBody());
            if ($responseBody->error_code == '200')
                return $responseBody;
            else
                throw new Exception($responseBody->error_message, $responseBody->error_code);
        }
        throw new Exception("Connect to gateway error");
    }
}