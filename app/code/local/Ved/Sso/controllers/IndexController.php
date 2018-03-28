<?php

class Ved_Sso_IndexController extends Mage_Core_Controller_Front_Action

{
    public function indexAction()
    {
        $accessToken = $this->getRequest()->getParam('accessToken');
        $apiUrlSso = (string)Mage::getConfig()->getNode('global/sso_url') . 'validate_access_token';
        $client = new Varien_Http_Client($apiUrlSso);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('accessToken', $accessToken);
        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                $response = json_decode($response->getBody());
                Mage::register('user', $response);
                Mage::register('accessToken', $accessToken);
                $this->loadLayout();
                $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('sso/sso'));
                $this->renderLayout();
            } else {
                var_dump("Có lỗi xảy ra, xin vui lòng thử lại sau !");
            }
        } catch (Exception $e) {
            var_dump($e->getTraceAsString());
            return $e;
        }
    }

    public function authenticateAction()
    {
        $params = $this->getRequest()->getParams();
        $username = $params['username'];
        $password = $params['password'];
        $email = $params['email'];
        $accessToken = $params['accessToken'];
        $user = Mage::getModel('admin/user')->getCollection();
        $user = $user->addFieldToFilter('email', $email)->getData();
        if(!count($user)) {
            try {
                $userModel = Mage::getModel('admin/user');
                if ($userModel->authenticate($username, $password)) {
                    $editUser = Mage::getModel('admin/user')
                        ->getCollection()
                        ->addFieldToFilter('username', $username)
                        ->getData();
                    if(!strstr($editUser['email'],"@teko.vn")){
                        $userModel->load($editUser[0]['user_id']);
                        $userModel->setData('email', $email);
                        try {
                            $userModel->save();
                            header('Location:' .'/sso.php?accessToken='.$accessToken);
                        } catch (Exception $e) {
                            $result = array('error' => "Cập nhật email có vấn đề !");
                            $this->getResponse()->setBody(json_encode($result));
                        }
                    }
                    else{
                        $result = array('error' => "Tài khoản đã được mapping");
                        $this->getResponse()->setBody(json_encode($result));
                    }

                } else {
                    $result = array('error' => "Tài khoản/mật khẩu không chính xác");
                    $this->getResponse()->setBody(json_encode($result));
                }
            } catch (Mage_Core_Exception $e) {
                $result = array('error' => "Đăng nhập thất bại");
                $this->getResponse()->setBody(json_encode($result));
            }
        }
        else
        {
            $result = array('error' => "Email đã được sử dụng để mapping tài khoản");
            $this->getResponse()->setBody(json_encode($result));
        }
    }
}