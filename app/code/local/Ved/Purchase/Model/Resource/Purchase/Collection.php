<?php

class Ved_Purchase_Model_Resource_Purchase_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init("ved_purchase/purchase");
    }

    /**
     * @param array $ids
     */
    public function checkComplete($ids)
    {
        $purchases = $this->addFieldToFilter('id', ['in' => $ids])
            ->load();
        foreach ($purchases as $purchase) {
            /**
             * @var Ved_Purchase_Model_Resource_Purchase $purchase
             */
            $checkItem = Mage::getModel('ved_purchase/purchaseitem')->getCollection()
                ->addFieldToFilter('import_qty', ['lt' => new Zend_Db_Expr('request_qty')])
                ->addFieldToFilter('purchase_id', $purchase->getId())
                ->getFirstItem();
            if($checkItem->isEmpty()){
                $purchase->setStatus(2)->save();
            }
        }
    }

    /**
     * @return int
     */
    public function getSize(){
        $this->getSelect()->group('main_table.id');
        if ( is_null( $this->_totalRecords ) ) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count( $this->getConnection()->fetchall( $sql, $this->_bindParams ) );
        }

        return intval( $this->_totalRecords );
    }
}