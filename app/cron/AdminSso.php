<?php

class AdminSso
{
    public function run()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $connection->beginTransaction();
            $sso_Url = (string)Mage::getConfig()->getNode('global/sso_url') . "get_all_users";
            $svMagento = new Varien_Http_Client();
            $svMagento->setUri($sso_Url);
            $response = $svMagento->request('GET');
            $usersData = json_decode($response->getBody(), true)['users'];

            foreach($usersData as $userData) {
                $user = Mage::getModel('teko_sso/admin_sso')->load($userData['id']);

                if ($user->getSsoId() != null) {
                    if ($user->getAsiaId() != $userData['asia_id'] || $user->getEmail() != $userData['email']) {
                        $user->addData(array(
                            'asia_id' => $userData['asia_id'],
                            'email' => $userData['email'],
                        ))->save();
                    }
                }
                else {
                    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $write->insert("admin_sso", array(
                        "sso_id" => $userData['id'],
                        "asia_id" => $userData['asia_id'],
                        "email" => $userData['email']
                    ));
                }

                $user_admin = Mage::getModel('admin/user')
                    ->getCollection()
                    ->addFieldToFilter('sso_id', $userData['id'])
                    ->getFirstItem();

                if ($user_admin->getId() != null) {
                    if ($user_admin->getAsiaId() != $userData['asia_id']) {
                        $user_admin->addData(array(
                            'asia_id' => $userData['asia_id'],
                        ))->save();
                    }
                }
            }
            $connection->commit();
        }
        catch (Exception $e) {
            $connection->rollback();
            var_dump($e->getMessage());
        }
    }
}