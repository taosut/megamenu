<?php
/**
 * Created by PhpStorm.
 * User: Tran Linh
 * Date: 12/13/2016
 * Time: 4:00 PM
 */
class Ved_Purchase_Model_Api2_Purchaseitem_Rest_Admin_V1 extends Ved_Purchase_Model_Api2_Purchaseitem
{

    /**
     * Create a customer
     * @return array
     */

    public function _construct(){
        var_dump(1);die();
    }

    public function _create($requestData) {
//        var_dump($requestData);die();
//        $requestData = $this->getRequest()->getBodyParams();
        $code = $requestData['code'];
        $items = $requestData['items'];
        //$result = array("result"=>"success","msg"=>"");
        try{
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if($code){
                $connection->beginTransaction();

                $purchase = Mage::getModel("ved_purchase/purchase")->getCollection()
                    ->addFieldToFilter('code', $code)
                    ->getData();

                if(count($purchase)){
                    foreach($items as $value){

                        $purchaseItemCollection = Mage::getModel("ved_purchase/purchaseitem")->getCollection()
                            ->addFieldToFilter('purchase_id', $purchase[0]['id'])
                            ->addFieldToFilter('product_id', $value['entity_id']);

                        if($purchaseItemCollection->getData()){
                            $purchaseItem = $purchaseItemCollection->getFirstItem();
                            $purchaseItem->setImportQty(new Zend_Db_Expr('import_qty +' . $value['quantity']));
                            // Update to imported status
                            $purchaseItem->setStatus(2);
                            $purchaseItem->save();
                        }
                    }
                    $connection->commit();
                    $result = array("result"=>"success","msg"=>"");
                }else{
                    $result = array("result"=>"error","msg"=>"No purchase request corresponding");
                }
            }else{
                $result = array("result"=>"error","msg"=>"Missing purchase code");
            }
        }catch(Exception $e){
            $connection->rollback();
            $result = array("result"=>"error","msg"=>$e->getMessage()." Error code ".$e->getCode());
        }
        echo json_encode($result); die();
        //return  json_encode($result);
    }

    public function _retrieveCollection()
    {
        return json_encode(array("a"=>"b"));
    }

}
?>