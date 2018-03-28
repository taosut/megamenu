<?php

/**
 * Class Ved_AdminApi_Controller_ApiController
 * @property Ved_AdminApi_Helper_Data $helper
 */
class Ved_AdminApi_Controller_ApiController extends Mage_Adminhtml_Controller_Action
{
    protected $helper;

    public function dispatch($action)
    {
        $this->helper = Mage::helper('adminapi');
        parent::dispatch($action);
        try {
            /**
             * @var Ved_AdminApi_Model_Log $log
             */
            if ($this->getRequest()->getActionName() != 'getOrderTekshopByMonth' &&
                $this->getRequest()->getActionName() != 'products' &&
                $this->getRequest()->getActionName() != 'getProductTekshop') {
                $log = Mage::getModel('ved_adminapi/log')
                    ->addData([
                        'controller' => $this->getRequest()->getControllerName(),
                        'action' => $this->getRequest()->getActionName(),
                        'request_body' => $this->getRequest()->getRawBody(),
                        'request_param' => json_encode($this->getRequest()->getParams(), JSON_UNESCAPED_UNICODE),
                        'method' => $this->getRequest()->getMethod(),
                        'response' => $this->getResponse()->getBody(),
                        'status' => 200,
                        'ip' => $this->getRequest()->getClientIp(),
                        'created_at' => now(),
                    ]);
                $log->save();
            }
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        Mage::app()->getRequest()->setParam('forwarded', true);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        return parent::preDispatch();
    }

}