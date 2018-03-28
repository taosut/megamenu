<?php

class Ved_Agent_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getIsAgentCustomerCollection()
    {
        return Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('is_agent', 1)
            ->addAttributeToFilter('website_id', 20)
            ->addAttributeToFilter('is_agent_deleted', 0)
            ->addAttributeToFilter('agent_info', array('like' => '%')) // To get agent_info in query
            ->addAttributeToSelect(array('firstname', 'phone_number', 'agent_info', 'agent_code'));
    }

    public function applyAgentInfoToModel(&$agent)
    {
        $agentInfo = json_decode($agent->getAgentInfo());

        foreach ($agentInfo as $key => $value) {
            $agent->$key = $value;
        }

        return true;
    }

    public function getNewAgentInfo($agent, $data)
    {
        $agentInfo = json_decode($agent->getAgentInfo());
        $acceptField = [
            'address', 'dob', 'gender', 'email'
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $acceptField)) {
                $agentInfo->$key = $value;
            }
        }

        return json_encode($agentInfo, JSON_UNESCAPED_UNICODE);
    }

    public function getAgentInfoAfterAddPoint($agent, $point) {
        $agentInfo = json_decode($agent->getAgentInfo());

        $agentInfo->available_point += $point;
        $agentInfo->available_point = max($agentInfo->available_point, 0);

        return json_encode($agentInfo, JSON_UNESCAPED_UNICODE);
    }
}
