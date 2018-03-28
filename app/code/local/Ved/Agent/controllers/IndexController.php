<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/6/2017
 * Time: 11:05 AM
 */
class Ved_Agent_IndexController extends Mage_Core_Controller_Front_Action
{
    private $agentChannels = array(
        1 => 'Mạng xã hội',
        2 => 'Diễn đàn/Website'
    );

    private $accountStatus = array(
        0 => 'Chờ duyệt',
        1 => 'Cần cập nhật',
        2 => 'Từ chối',
        3 => 'Phê duyệt'
    );

    private $achievementStatus = array(
        0 => 'Chờ duyệt',
        1 => 'Cần cập nhật',
        2 => 'Từ chối',
        3 => 'Phê duyệt'
    );

    public function indexAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function registerAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user da la dac vu thi redirect ve trang chinh cua dac vu
            if ($currentUser->getIsAgent() == '1') {
                $this->_redirect('agent');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    // Dang ky dac vu Tekshop
    private function getListCodeExist($array)
    {
        $code_array = array();

        foreach ($array as $item) {
            array_push($code_array, $item['agent_code']);
        }

        return $code_array;
    }

    public function registerAgentAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            $data['address'] = strip_tags($data['address']);
            // Xu ly dang ky dac vu Tekshop
            $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($data['user_email']);

            $customer->setIsAgent(1);
            $customer->setIsAgentDeleted(0); // Agent chua bi xoa tam thoi khoi he thong

            $new_agent_code = $this->generateAgentCode();

            // Check ma code gen ra da ton tai trong he thong
            $agentCollection = Mage::getModel("customer/customer")->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('agent_code', array('notnull' => true));

            while (in_array($new_agent_code, $this->getListCodeExist($agentCollection->getData()))) {
                $new_agent_code = $this->generateAgentCode();
            }

            $customer->setAgentCode($new_agent_code);

            $recommendation_code = base64_decode($data['recommendation_code']);
            $master_agent = Mage::getModel("customer/customer")->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('agent_code', $recommendation_code)
                ->getFirstItem();

            $agent_info = new stdClass();
            $agent_info->email = $data['email'];
            $agent_info->address = $data['address'];
            $agent_info->dob = $data['dob'];
            $agent_info->gender = $data['gender'];
            $agent_info->available_point = 0;
            $agent_info->used_point = 0;

            // Xu ly gioi thieu dac vu (neu tim thay nguoi gioi thieu voi ma code tren url)
            if (count($master_agent->getData()) > 0) {
                $agent_info->referred_by = $master_agent->getId();
                $agent_info->referred_at = Mage::getModel('core/date')->date('Y-m-d H:i:s');

                // Cong diem cho nguoi gioi thieu
                $master_agent_info = json_decode($master_agent->getAgentInfo());
                $bonus_point = Mage::getModel('core/variable')->loadByCode('agent_recommendation_point')->getValue('html');
                $master_agent_info->available_point += intval($bonus_point);

                $master_agent->setAgentInfo(json_encode($master_agent_info, JSON_UNESCAPED_UNICODE));
                $master_agent->save();

                // Luu record vao bang bien dong diem
                $newData = array(
                    'user_id' => $master_agent->getId(),
                    'point' => intval($bonus_point),
                    'object_id' => $customer->getId(),
                    'type' => 3,
                    'detail' => "Giới thiệu thành công đặc vụ " . $customer->getFirstname(),
                    'accumulate_point' => $master_agent_info->available_point,
                    'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                );

                $model = Mage::getModel('Ved_Agent/agentpointhistory')->addData($newData);
                $model->save();

                $result = array('success' => true, 'master_agent_name' => $master_agent->getFirstname(), 'agent_code' => $new_agent_code);
            } else {
                $result = array('success' => true, 'agent_code' => $new_agent_code);
            }


            $customer->setAgentInfo(json_encode($agent_info, JSON_UNESCAPED_UNICODE));
            $customer->save();
            // End xu ly dang ky dac vu Tekshop

            // Gui SMS dang ky thanh cong
            $message = "Chào mừng bạn đến với chương trình đặc vụ Tekshop, mã đặc vụ $new_agent_code. Bạn có thể bắt đầu thực hiện các chiến dịch của Tekshop ngay từ bây giờ!";
            $this->sendSMS($data['phone_number'], $message);

        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function accountManagementAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function updateAgentInfoAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            $data['address'] = strip_tags($data['address']);
            // Xu ly cap nhat thong tin dac vu Tekshop
            $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($data['user_email']);

            $agent_info = json_decode($customer->getAgentInfo());
            $agent_info->email = $data['email'];
            $agent_info->address = $data['address'];
            $agent_info->dob = $data['dob'];
            $agent_info->gender = $data['gender'];

            $customer->setAgentInfo(json_encode($agent_info, JSON_UNESCAPED_UNICODE));
            $customer->save();
            // End xu ly cap nhat thong tin dac vu Tekshop

            $result = array('success' => true);
        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /* Xu ly achievement cho agent */
    public function getSubmitHistoryAction()
    {
        $data = $this->getRequest()->getParams();

        $achievementCollection = Mage::getModel('Ved_Agent/agentachievement')->getCollection()
            ->addFieldToFilter('main_table.user_id', $data['user_id'])
            ->addFieldToFilter('main_table.is_deleted', 0);

        $achievementCollection->getSelect()
            ->join(
                'agent_achievement_type',
                'agent_achievement_type.achievement_type_id = main_table.achievement_type_id',
                array('achievement_type_name' => 'agent_achievement_type.achievement_type_name')
            )->join(
                'agent_account',
                'agent_account.account_id = main_table.account_id',
                array('account_name' => 'agent_account.account_name')
            )->join(
                'agent_channel',
                'agent_channel.channel_id = agent_account.channel_id',
                array('channel_name' => 'agent_channel.channel_name')
            )->joinLeft(
                'agent_point_history',
                'agent_point_history.object_id = main_table.achievement_id AND agent_point_history.type = 1',
                array('point' => 'agent_point_history.point')
            )->where(
                "agent_achievement_type.is_deleted = 0 
                AND agent_account.is_deleted = 0 
                AND agent_channel.is_deleted = 0"
            );

        $achievementArray = array();
        foreach ($achievementCollection as $achievement) {

            $achievementId = $achievement->getId();
            $accountId = $achievement->getAccountId();
            $achievementTypeId = $achievement->getAchievementTypeId();
            $link = $achievement->getLink();

            $actionHtml = '';
            if ($achievement->getStatus() == Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY || $achievement->getStatus() == Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE) {
                $actionHtml = <<<EOD
<span class="agent-action-btn edit-account-btn" onclick="editAchievement(this)"
                     data-achievement-id="$achievementId"
                     data-account-id="$accountId"
                     data-achievement-type-id="$achievementTypeId"
                     data-link="$link"
                              ><i class="fa fa-pencil-square-o"></i></span>&emsp;<span class="agent-action-btn" onclick="deleteAchievement(this)"
                              data-achievement-id="$achievementId"><i class="fa fa-trash-o delete-achievement-btn"></i></span>
EOD;
            }

            $achievementPoint = ($achievement->getStatus() == Ved_Agent_Model_Agentachievement::VERIFIED) ? $achievement->point : 0;

            $achievementLink = $achievement->getLink();

            $accountName = $achievement->account_name;
            $channelName = strtolower($achievement->channel_name);

            $accountName = ($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'twitter' || $channelName == 'youtube')
                ? "<a class='agent-table-link' href='$accountName' target='_blank'>$accountName</a>"
                : $accountName;

            if ($achievement->getStatus() == Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY) {
                $statusText = "<span class='achieve-waiting-text'>" . $this->achievementStatus[$achievement->getStatus()] . "</span>";
            } else if ($achievement->getStatus() == Ved_Agent_Model_Agentachievement::REQUIRE_TO_UPDATE) {
                $statusText = "<span class='achieve-ntu-text'>" . $this->achievementStatus[$achievement->getStatus()] . "</span>";
            } else if ($achievement->getStatus() == Ved_Agent_Model_Agentachievement::DECLINED) {
                $statusText = "<span class='achieve-rejected-text'>" . $this->achievementStatus[$achievement->getStatus()] . "</span>";
            } else {
                $statusText = "<span class='achieve-approved-text'>" . $this->achievementStatus[$achievement->getStatus()] . "</span>";
            }

            array_push($achievementArray, [
                "<span class='no-display'>" . date("Y-m-d H:i:s", strtotime($achievement->getUpdatedAt())) . "|</span>" . date("d/m/Y H:i:s", strtotime($achievement->getUpdatedAt())),
                $accountName . " (" . $channelName . ")",
                $achievement->achievement_type_name,
                "<a class='agent-table-link' href='$achievementLink' target='_blank'>$achievementLink</a>",
                $statusText,
                $achievement->getComment(),
                $achievementPoint,
                $actionHtml
            ]);
        }
        $result = array('data' => $achievementArray);
        echo Mage::helper('core')->jsonEncode($result);
    }

    public function getAchievementTypeByAccountAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            // Xu ly lay danh sach hinh thuc theo account
            $account = Mage::getModel("Ved_Agent/agentaccount")->load($data['agent_account_id']);
            $achievementTypeCollection = Mage::getModel("Ved_Agent/agentachievementtype")->getCollection()
                ->addFieldToFilter('channel_id', $account->getChannelId())
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('is_deleted', 0);
            $html = "<option value=''></option>";
            $i = 0;
            foreach ($achievementTypeCollection as $achievementType) {
                $typeId = $achievementType->getId();
                $typeName = $achievementType->getAchievementTypeName();
                if ($i == 0) {
                    $html .= "<option value='$typeId' selected>$typeName</option>";
                } else {
                    $html .= "<option value='$typeId'>$typeName</option>";
                }
                $i++;
            }
            // End xu ly lay danh sach hinh thuc theo account

            $result = array('success' => true, 'list_type_html' => $html);
        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function submitAchievementAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            $data['link'] = strip_tags($data['link']);

            $data['link'] = rtrim($data['link'], "/");

            // Check neu link trung va da bi tu choi thi dc up bai moi, ko thi ko dc up
            $link = str_replace('?', '_', $data['link']);

            $achievementCollection = Mage::getModel('Ved_Agent/agentachievement')->getCollection()
                ->addFieldToFilter('link', array('like' => $link));

            $achievementType = Mage::getModel('Ved_Agent/agentachievementtype')->load($data['achievement_type_id']);

            $channel = Mage::getModel('Ved_Agent/agentchannel')->load($achievementType->getChannelId());
            $channelName = strtolower($channel->getChannelName());

            $linkCheck = $channelName . '.com';

            // Check link co phu hop voi kenh?
            if (($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'youtube' || $channelName == 'twitter') && strpos($data['link'], $linkCheck) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link bài viết không tương ứng với nhau, vui lòng kiểm tra lại!');
            } else if (strpos($data['link'], $channelName) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link bài viết không tương ứng với nhau, vui lòng kiểm tra lại!');
            } else if ($achievementCollection->count() > 0) { // Tim thay ban ghi bi trung link va status ko thoa man
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Link bạn nhập trùng với bài viết đã có trên hệ thống trước đó!');
            } else { // Data truyen vao thoa man

                // Xu ly submit thanh tich
                $newData = array(
                    'user_id' => $data['user_id'],
                    'account_id' => $data['account_id'],
                    'achievement_type_id' => $data['achievement_type_id'],
                    'link' => $data['link'],
                    'status' => Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY,
                    'is_deleted' => 0,
                    'created_by' => $data['user_id'],
                    'updated_by' => $data['user_id'],
                    'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
                    'updated_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                );

                $model = Mage::getModel('Ved_Agent/agentachievement')->addData($newData);
                $model->save();
                // End xu ly submit thanh tich

                $result = array('success' => true);
            }

        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateAchievementAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            $data['link'] = strip_tags($data['link']);

            // Check neu link trung va da bi tu choi thi dc up bai moi, ko thi ko dc up
            $link = str_replace('?', '_', $data['link']);
            $achievementCollection = Mage::getModel('Ved_Agent/agentachievement')->getCollection()
                ->addFieldToFilter('achievement_id', array('neq' => $data['achievement_id']))// Loai tru bai dang sua
                ->addFieldToFilter('link', array('like' => $link));

            $achievementType = Mage::getModel('Ved_Agent/agentachievementtype')->load($data['achievement_type_id']);

            $channel = Mage::getModel('Ved_Agent/agentchannel')->load($achievementType->getChannelId());
            $channelName = strtolower($channel->getChannelName());

            $linkCheck = $channelName . '.com';

            // Check link co phu hop voi kenh?
            if (($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'youtube' || $channelName == 'twitter') && strpos($data['link'], $linkCheck) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link bài viết không tương ứng với nhau, vui lòng kiểm tra lại!');
            } else if (strpos($data['link'], $channelName) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link bài viết không tương ứng với nhau, vui lòng kiểm tra lại!');
            } else if ($achievementCollection->count() > 0) { // Tim thay ban ghi bi trung link va status ko thoa man
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Link bạn nhập trùng với bài viết đã có trên hệ thống trước đó!');
            } else { // Data truyen vao thoa man

                // Xu ly update thanh tich
                $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($data['achievement_id']);
                $achievement->account_id = $data['account_id'];
                $achievement->achievement_type_id = $data['achievement_type_id'];
                $achievement->link = $data['link'];
                if ($achievement->status == 1) { // Neu chuyen trang thai tu can cap nhat -> luu vao bang agent_achievement_history
                    $newData = array(
                        'achievement_id' => $data['achievement_id'],
                        'status' => Ved_Agent_Model_Agentachievement::WAITING_TO_VERIFY,
                        'comment' => '',
                        'is_deleted' => 0,
                        'created_by' => $achievement->user_id,
                        'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                    );

                    $model = Mage::getModel('Ved_Agent/agentachievementhistory')->addData($newData);
                    $model->save();
                }
                $achievement->status = 0;
                $achievement->comment = '';
                $achievement->updated_by = $achievement->getUserId();
                $achievement->updated_at = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $achievement->save();
                // End xu ly update thanh tich

                $result = array('success' => true);
            }

        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteAchievementAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            // Xu ly xoa achievement
            $achievement = Mage::getModel('Ved_Agent/agentachievement')->load($data['achievement_id']);
            $achievement->setIsDeleted(1);
            $achievement->updated_at = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $achievement->save();
            // End xu ly xoa achievement

            $result = array('success' => true);
        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /* End xu ly achievement cho agent */

    /* Xu ly account cho agent */
    public function getAccountDataAction()
    {
        $data = $this->getRequest()->getParams();

        $accountCollection = Mage::getModel('Ved_Agent/agentaccount')->getCollection()
            ->addFieldToFilter('main_table.user_id', $data['user_id'])
            ->addFieldToFilter('main_table.is_deleted', 0);

        $accountCollection->getSelect()
            ->join(
                'agent_channel',
                'agent_channel.channel_id = main_table.channel_id',
                array('channel_name' => 'agent_channel.channel_name', 'channel_type' => 'agent_channel.channel_type', 'channel_active' => 'agent_channel.is_active')
            )->where(
                "agent_channel.is_deleted = 0"
            );

        $accountArray = array();
        $i = 0;
        foreach ($accountCollection as $account) {
            $channelId = $account->getChannelId();
            $accountName = $account->getAccountName();
            $accountId = $account->getId();

            // Check neu tai khoan dang co bai viet chua bi xoa thi ko the Sua / Xoa
            $achievementCollection = Mage::getModel('Ved_Agent/agentachievement')->getCollection()
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('is_deleted', 0);

            if (count($achievementCollection) > 0) {
                $actionHtml = "";
            } else {
                $actionHtml = <<<EOD
<span class="agent-action-btn edit-account-btn" onclick="editAccount(this)"
                              data-channel-id="$channelId"
                              data-account-name="$accountName"
                              data-account-id="$accountId"><i class="fa fa-pencil-square-o"></i></span>&emsp;<span class="agent-action-btn" onclick="deleteAccount(this)"
                              data-account-id="$accountId"><i class="fa fa-trash-o delete-account-btn"></i></span>
EOD;
            }

            $channelName = strtolower($account->channel_name);

            $accountName = ($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'twitter' || $channelName == 'youtube')
                ? "<a class='agent-table-link' href='$accountName' target='_blank'>$accountName</a>"
                : $accountName;

            if (!$account->getChannelActive()) $channelName .= ' (Đã vô hiệu)';

            if ($account->getStatus() == Ved_Agent_Model_Agentaccount::WAITING_TO_VERIFY) {
                $statusText = "<span class='achieve-waiting-text'>" . $this->accountStatus[$account->getStatus()] . "</span>";
            } else if ($account->getStatus() == Ved_Agent_Model_Agentaccount::DECLINED) {
                $statusText = "<span class='achieve-rejected-text'>" . $this->accountStatus[$account->getStatus()] . "</span>";
            } else {
                $statusText = "<span class='achieve-approved-text'>" . $this->accountStatus[$account->getStatus()] . "</span>";
            }

            // Get admin comment from agent_account_history
            $agentAccountHistory = Mage::getModel('Ved_Agent/agentaccounthistory')->getCollection()
                ->addFieldToFilter('account_id', $accountId)->getLastItem();

            array_push($accountArray, [
                $i + 1,
                $this->agentChannels[$account->channel_type],
                $channelName,
                $accountName,
                $statusText,
                $agentAccountHistory->getComment(),
                $actionHtml
            ]);
            $i++;
        }
        $result = array('data' => $accountArray);
        echo Mage::helper('core')->jsonEncode($result);
    }

    public function addAccountAction()
    {
        $data = $this->getRequest()->getParams();

        try {

            $channel = Mage::getModel('Ved_Agent/agentchannel')->load($data['channel_id']);
            $channelName = strtolower($channel->getChannelName());
            $channelId = $data['channel_id'];

            $data['account_name'] = strip_tags($data['account_name']);
            $accountName = str_replace('?', '_', $data['account_name']);

            $account = Mage::getModel('Ved_Agent/agentaccount')->getCollection()
                ->addFieldToFilter('channel_id', $channelId)
                ->addFieldToFilter('account_name', array('like' => $accountName))
                ->getFirstItem();

            $linkProfileCheck = $channelName . '.com';

            // Check link co phu hop voi kenh?
            if (($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'youtube' || $channelName == 'twitter') && strpos($data['account_name'], $linkProfileCheck) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link profile không tương ứng với nhau, vui lòng kiểm tra lại!');
            } // Check da ton tai account voi kenh nay trong he thong?
            else if (count($account->getData()) > 0 && $account->getIsDeleted() == 0) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Tài khoản trên kênh này đã tồn tại trên hệ thống, vui lòng kiểm tra lại');
            } // Thoa man
            else {
                // Xu ly them tai khoan mang xa hoi cho dac vu Tekshop
                $newData = array(
                    'account_name' => $data['account_name'],
                    'channel_id' => $channelId,
                    'user_id' => $data['user_id'],
                    'is_deleted' => 0,
                    'status' => Ved_Agent_Model_Agentaccount::WAITING_TO_VERIFY,
                    'created_by' => $data['user_id'],
                    'updated_by' => $data['user_id'],
                    'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
                    'updated_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                );

                $model = Mage::getModel('Ved_Agent/agentaccount')->addData($newData);
                $model->save();
                // End xu ly them tai khoan mang xa hoi cho dac vu Tekshop

                $result = array('success' => true);
            }

        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateAccountAction()
    {
        $data = $this->getRequest()->getParams();

        try {

            $channel = Mage::getModel('Ved_Agent/agentchannel')->load($data['channel_id']);
            $channelName = strtolower($channel->getChannelName());
            $channelId = $data['channel_id'];

            $data['account_name'] = strip_tags($data['account_name']);
            $accountName = str_replace('?', '_', $data['account_name']);

            $accountCheck = Mage::getModel('Ved_Agent/agentaccount')->getCollection()
                ->addFieldToFilter('channel_id', $channelId)
                ->addFieldToFilter('account_id', array('neq' => $data['agent_account_id']))// Loai tru tai khoan dang sua
                ->addFieldToFilter('account_name', array('like' => $accountName))
                ->getFirstItem();

            $linkProfileCheck = $channelName . '.com';

            // Check link co phu hop voi kenh?
            if (($channelName == 'facebook' || $channelName == 'instagram' || $channelName == 'youtube' || $channelName == 'twitter') && strpos($data['account_name'], $linkProfileCheck) === false) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Kênh và link profile không tương ứng với nhau, vui lòng kiểm tra lại!');
            } // Check da ton tai account voi kenh nay trong he thong?
            else if (count($accountCheck->getData()) > 0 && $accountCheck->getIsDeleted() == 0) {
                $result = array('success' => false, 'no_satisfy' => true, 'error_message' => 'Tài khoản trên kênh này đã tồn tại trên hệ thống, vui lòng kiểm tra lại');
            } // Thoa man
            else {
                // Xu ly update tai khoan mang xa hoi cho dac vu Tekshop
                $account = Mage::getModel('Ved_Agent/agentaccount')->load($data['agent_account_id']);
                $account->account_name = $data['account_name'];
                $account->channel_id = $data['channel_id'];
                $account->status = Ved_Agent_Model_Agentaccount::WAITING_TO_VERIFY;
                $account->updated_by = $data['user_id'];
                $account->updated_at = Mage::getModel('core/date')->date('Y-m-d H:i:s');

                $account->save();
                // End xu ly update tai khoan mang xa hoi cho dac vu Tekshop

                $result = array('success' => true);
            }
        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteAccountAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            // Xu ly xoa tai khoan mang xa hoi cho dac vu Tekshop
            $account = Mage::getModel('Ved_Agent/agentaccount')->load($data['agent_account_id']);
            $account->setIsDeleted(1);
            $account->updated_at = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $account->save();
            // End xu ly xoa tai khoan mang xa hoi cho dac vu Tekshop

            $result = array('success' => true);
        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /* End xu ly account cho agent */

    /* Xu ly quy doi thanh tich */
    public function redemptionAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function getRedemptionGiftAction()
    {
        $redemptionGiftCollection = Mage::getModel('Ved_Agent/agentredemptiongift')->getCollection()
            ->addFieldToFilter('is_deleted', 0);
        $redemptionGiftArray = array();

        $currentUser = Mage::getSingleton('customer/session')->getCustomer();
        $agent_info = json_decode($currentUser->getAgentInfo());

        $i = 0;
        foreach ($redemptionGiftCollection as $redemptionGift) {
            if ($agent_info->available_point >= $redemptionGift->getPoint()) {
                $redemptionGiftName = $redemptionGift->getRedemptionGiftName() . "&nbsp;&nbsp;<i class='fa fa-check agent-redeem-available-icon'></i>";
                $redemptionGiftId = $redemptionGift->getId();
                $redeemButton = "<div class='agent-redeem-btn agent-btn' onclick='redeem(this)' data-redemption-gift-id='$redemptionGiftId'>Quy đổi</div>";
            } else {
                $redemptionGiftName = "<span class='agent-redeem-not-available'>" . $redemptionGift->getRedemptionGiftName() . "</span>&nbsp;&nbsp;<i class='fa fa-close agent-redeem-not-available-icon'></i>";
                $redeemButton = "";
            }

            array_push($redemptionGiftArray, [
                $i + 1,
                $redemptionGiftName,
                $redemptionGift->getPoint(),
                $redeemButton
            ]);
            $i++;
        }
        $result = array('data' => $redemptionGiftArray);
        echo Mage::helper('core')->jsonEncode($result);
    }

    public function redeemAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            // Xu ly quy doi thanh tich cho dac vu Tekshop
            $redemptionGift = Mage::getModel('Ved_Agent/agentredemptiongift')->load($data['redemption_gift_id']);

            $couponCode = Mage::getResourceModel('salesrule/coupon_collection')
                ->addFieldToFilter('rule_id', $redemptionGift->getRuleId())
                ->addFieldToFilter('times_used', 0)
                ->addFieldToFilter('is_redeem', 0)
                ->getFirstItem();

            if (count($couponCode->getData()) == 0) { // Khong co ma coupon thoa man
                $result = array('success' => false, 'no_coupon' => true, 'error_message' => "Tạm thời hết Voucher cho quyền lợi này. Vui lòng liên hệ Admin Tekshop theo hotline 0988796885 hoặc email đến địa chỉ hotro@teko.vn để được hỗ trợ nhanh nhất. Xin cảm ơn!");
            } else {
                $couponId = $couponCode->getData('coupon_id');

                // Luu thong tin vao agent_redemption (lich su quy doi)
                $newRedemptionData = array(
                    'user_id' => $data['user_id'],
                    'redemption_gift_id' => $data['redemption_gift_id'],
                    'coupon_id' => $couponId,
                    'is_deleted' => 0,
                    'created_by' => $data['user_id'],
                    'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                );

                $newRedemption = Mage::getModel('Ved_Agent/agentredemption')->addData($newRedemptionData);
                $newRedemption->save();

                // Update agent info (giam available point, tang used point)
                $agent = Mage::getModel("customer/customer")->load($data['user_id']);
                $agent_info = json_decode($agent->getAgentInfo());
                $agent_info->available_point -= $redemptionGift->getPoint();
                $agent_info->used_point += $redemptionGift->getPoint();
                $agent->setAgentInfo(json_encode($agent_info, JSON_UNESCAPED_UNICODE));
                $agent->save();

                // Luu thong tin vao bang agent_point_history
                $newPointHistoryData = array(
                    'user_id' => $data['user_id'],
                    'point' => $redemptionGift->getPoint(),
                    'object_id' => $newRedemption->getId(),
                    'type' => 2,
                    'detail' => "Quy đổi " . $redemptionGift->getRedemptionGiftName(),
                    'accumulate_point' => $agent_info->available_point,
                    'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                );

                $pointHistory = Mage::getModel('Ved_Agent/agentpointhistory')->addData($newPointHistoryData);
                $pointHistory->save();

                $couponCode->setIsRedeem(1);
                $couponCode->save();

                // Gui SMS quy doi thanh cong
                $message = "Cảm ơn đặc vụ " . $agent->getFirstname() . " đã quy đổi " . $redemptionGift->getRedemptionGiftName() . ", mã Voucher mua hàng tại Tekshop của bạn là: " . $couponCode->getData('code');
                $this->sendSMS($agent->getPhoneNumber(), $message);

                // End xu ly quy doi thanh tich cho dac vu Tekshop

                $result = array('success' => true, 'available_point' => $agent_info->available_point, 'used_point' => $agent_info->used_point);
            }

        } catch (Exception $e) {
            $result = array('success' => false, 'error_message' => $e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /* End xu ly quy doi thanh tich */

    /* Xu ly lich su quy doi */
    public function redemptionHistoryAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function getRedemptionHistoryAction()
    {
        $data = $this->getRequest()->getParams();

        $redemptionCollection = Mage::getModel('Ved_Agent/agentredemption')->getCollection()
            ->addFieldToFilter('main_table.user_id', $data['user_id']);

        $redemptionCollection->getSelect()
            ->join(
                'agent_redemption_gift',
                'agent_redemption_gift.redemption_gift_id = main_table.redemption_gift_id',
                array(
                    'redemption_gift_name' => 'agent_redemption_gift.redemption_gift_name',
                    'point' => 'agent_redemption_gift.point'
                )
            )->join(
                'salesrule_coupon',
                'salesrule_coupon.coupon_id = main_table.coupon_id',
                array(
                    'times_used' => 'salesrule_coupon.times_used'
                )
            )->where(
                "agent_redemption_gift.is_deleted = 0"
            );

        $redemptionArray = array();

        foreach ($redemptionCollection as $redemption) {
            $statusText = ($redemption->times_used > 0) ? 'Đã sử dụng' : 'Chưa sử dụng';
            array_push($redemptionArray, [
                "<span class='no-display'>" . date("Y-m-d H:i:s", strtotime($redemption->getCreatedAt())) . "|</span>" . date("d/m/Y H:i:s", strtotime($redemption->getCreatedAt())),
                $redemption->redemption_gift_name,
                $redemption->point,
                $statusText
            ]);
        }
        $result = array('data' => $redemptionArray);
        echo Mage::helper('core')->jsonEncode($result);
    }
    /* End xu ly lich su quy doi */

    /* Xu ly danh sach dac vu gioi thieu */
    public function agentListAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function getAgentListAction()
    {
        $data = $this->getRequest()->getParams();

        $agentCollection = Mage::getModel('Ved_Agent/agentpointhistory')->getCollection()
            ->addFieldToFilter('user_id', $data['user_id'])
            ->addFieldToFilter('type', 3);

        $firstname = Mage::getResourceModel('customer/customer')->getAttribute('firstname');
        $agentCode = Mage::getResourceModel('customer/customer')->getAttribute('agent_code');

        $agentCollection->getSelect()
            ->join(
                array('customer_first_name' => $firstname->getBackend()->getTable()),
                'customer_first_name.entity_id = main_table.object_id AND customer_first_name.attribute_id = ' . $firstname->getAttributeId(),
                array(
                    'first_name' => 'customer_first_name.value'
                )
            )->join(
                array('customer_agent_code' => $agentCode->getBackend()->getTable()),
                'customer_agent_code.entity_id = main_table.object_id AND customer_agent_code.attribute_id = ' . $agentCode->getAttributeId(),
                array(
                    'agent_code' => 'customer_agent_code.value'
                )
            );

        $agentArray = array();
        $i = 0;
        foreach ($agentCollection as $agent) {
            array_push($agentArray, [
                $i + 1,
                $agent->agent_code,
                $agent->first_name,
                "<span class='no-display'>" . date("Y-m-d H:i:s", strtotime($agent->getCreatedAt())) . "|</span>" . date("d/m/Y H:i:s", strtotime($agent->getCreatedAt())),
                $agent->getPoint()
            ]);
            $i++;
        }
        $result = array('data' => $agentArray);
        echo Mage::helper('core')->jsonEncode($result);
    }
    /* End xu ly danh sach dach vu gioi thieu */

    /* Xu ly lich su tich luy diem */
    public function pointHistoryAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $currentUser = $session->getCustomer();
            // Kiem tra neu user chua phai dac vu thi redirect ve trang dang ky dac vu
            if ($currentUser->getIsAgent() != '1') {
                $this->_redirect('agent/index/register');
            } else {
                $this->loadLayout();
                $this->renderLayout();
            }

        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function getPointHistoryAction()
    {
        $data = $this->getRequest()->getParams();

        $pointHistoryCollection = Mage::getModel('Ved_Agent/agentpointhistory')->getCollection()
            ->addFieldToFilter('user_id', $data['user_id']);
        $pointHistoryArray = array();
        foreach ($pointHistoryCollection as $pointHistory) {
            $changeText = ($pointHistory->getType() == 1 || $pointHistory->getType() == 3 || $pointHistory->getType() == 4) ? 'Tăng ' : 'Giảm ';
            array_push($pointHistoryArray, [
                "<span class='no-display'>" . date("Y-m-d H:i:s", strtotime($pointHistory->getCreatedAt())) . "|</span>" . date("d/m/Y H:i:s", strtotime($pointHistory->getCreatedAt())),
                $changeText . $pointHistory->getPoint() . ' điểm',
                $pointHistory->getDetail(),
                $pointHistory->getAccumulatePoint()
            ]);
        }
        $result = array('data' => $pointHistoryArray);
        echo Mage::helper('core')->jsonEncode($result);
    }

    /*End xu ly lich su tich luy diem */

    private function generateAgentCode($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @deprecated
     * @param $phone
     * @param $message
     * @return bool
     */
    private function sendSMS($phone, $message)
    {
        $key = "8b78a259eb04ccc9676a80abe791e16d";
        $timestamp = time();
        $skey = md5($key);
        $sign = md5($key . $phone . $timestamp);
        $data = array(
            'skey' => $skey,
            'sign' => $sign,
            'phone' => $phone,
            'body' => $message,
            'timestamp' => $timestamp,
            'app' => 'Tekshop',
            'brand' => 'Tekshop',
            'type' => '',
        );

        $data_params = array();
        foreach ($data as $k => $v) {
            $data_params[] = urlencode($k) . '=' . urlencode($v);
        }
        $data_str = implode('&', $data_params);

        $parts = parse_url('http://sms-brandname.teko.vn/sendMT');
        $host = $parts['host'];
        if (isset($parts['port'])) {
            $port = $parts['port'];
        } else {
            $port = $parts['scheme'] == 'https' ? 443 : 80;
        }

        $fp = fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            2
        );

        if (!$fp) {
            return false;
        } else {
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            if (!empty($data_str)) {
                $out .= "Content-Length: " . strlen($data_str) . "\r\n";
            }
            $out .= "Connection: Close\r\n\r\n";
            if (!empty($data_str)) {
                $out .= $data_str;
            }
            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }
}
