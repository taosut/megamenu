<?php

class Teko_Amp_Model_Resource_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teko_amp/log');
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return string
     */
    public function getDataCheckSum(DateTime $from, DateTime $to)
    {
        /**
         * @var  Varien_Db_Statement_Pdo_Mysql $query
         */
        $query = $this->addFieldToFilter('created_at', ['gteq' => $from->getTimestamp()])
            ->addFieldToFilter('created_at', ['lt' => $to->getTimestamp()])
            ->getSelect()
            ->columns('COUNT(*) AS totalMessage')
            ->group(['type', 'routing_key'])
            ->query();
        $data = $query->fetchAll();
        $result = ['service_name' => 'teko.sale'];
        foreach ($data as $item) {
            if ($item['type'] == 1)
                $result['sent'][] = ['routing_key' => $item['routing_key'] , 'quantity'=> $item['totalMessage']];
            if ($item['type'] == 2)
                $result['received'][] = ['routing_key' => $item['routing_key'] , 'quantity'=> $item['totalMessage']];
        }
        return json_encode($result);
    }
}
