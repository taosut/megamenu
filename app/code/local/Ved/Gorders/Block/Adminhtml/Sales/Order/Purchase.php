<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_Purchase extends Mage_Adminhtml_Block_Template
{

    private $_api;


    private $_product;

    private $_order;

    private $_listStore;

    public function __construct()
    {
        $this->_product = Mage::getModel('sales/order_item')->load($this->getRequest()->get('product_id'));

        $this->_order = Mage::getModel('sales/order')->load($this->getRequest()->get('order_id'));

        $province_name = $this->_order->getAssignProvince() ? $this->_order->getAssignProvince() : $this->_order->getShippingAddress()->getRegion();

        $path = 'products/in-stock-quantity-by-province-entity?' . http_build_query(array(
                'entity_id' => $this->_product->getProductId(),
                'province_name' => $province_name,
            ));

        $this->_api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $this->_listStore = Mage::getModel('core/store');

        parent::__construct();
    }

    public function _prepareLayout()
    {
        $adapter = Mage::getConfig()->getNode('global/warehouse_adapter');
        $template = 'sales/order/view/purchase.phtml';
        if ($adapter == 'phongvu') {
            $template = 'sales/order/view/purchase_pv.phtml';
        }
        $this->setTemplate($template);
        parent::__construct();
    }

    /**
     * @return string
     */
    public function saveWarehouseUrl()
    {
        return $this->getUrl('*/*/purchaseWarehouse', array('order_id' => $this->_order->getId(), 'product_id' => $this->_product->getId()));
    }

    /**
     * @return string
     */
    public function saveSupplierUrl()
    {
        return $this->getUrl('*/*/purchaseSupplier', array('order_id' => $this->_order->getId(), 'product_id' => $this->_product->getId()));
    }


    public function saveTransportUrl()
    {
        return $this->getUrl('*/*/transfer', array('order_id' => $this->_order->getId(), 'product_id' => $this->_product->getId()));
    }

    /**
     * @return mixed
     */
    public function getListStore()
    {
        return $this->_listStore;
    }

    public function getStoreName($id)
    {
        return $this->_listStore->load($id)->getName();
    }

    public function getAllStore()
    {
        return $this->_listStore->getCollection()->load();
    }

    /**
     * @return string
     */
    public function getApi()
    {
        $json = json_decode($this->_api, true);

        if ($json['status'] == 1)
            return $json['data'];

        return array();
    }

    public function getListStoreWithout()
    {
        $stores = $this->getApi();

        if (isset($stores['parent']['quantity']))
            return $stores['parent'];
        return [];
    }

    public function getListWithStore($id)
    {
        $stores = $this->getApi();
        if ($stores['quantity'] > 0)
            return $stores;
        return [];
    }


    /**
     * @return Mage_Sales_Model_Order_Item
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getOrder()
    {
        return $this->_order;
    }

    public function getListSupplier()
    {
        $productId = $this->_product->getProductId();

        $path = 'products/suppliers?' . http_build_query(array('entity_id' => array($productId)));

        $api = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));

        $json = json_decode($api, true);

        return $json['data'][$productId];

    }

    /**
     * @return array
     */
    public function getStoreList()
    {
        $response = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse('stores/list'));
        $result = json_decode($response, true);
        return $result['data'];
    }

    public function getQtyItem()
    {
        return $this->_order->getItemById($this->_product->getId())->getQtyOrdered();
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/view', array('order_id' => $this->_order->getId()));
    }

    public function checkProductWarehouse($orderId, $productId, $entityId)
    {
        $path = 'products/in-stock-quantity?' . http_build_query(array(
                'product_ids' => $entityId,
            ));
        $response = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));
        $result = json_decode($response, true);

        $itemStockCollection = Mage::getModel("ved_gorders/orderitemstock")
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('standard_product_id',$entityId)
            ->addFieldToSelect('standard_product_id')
            ->addFieldToSelect('store_id')
            ->addExpressionFieldToSelect('total_request', 'sum({{quantity}})', 'quantity');

        $itemStockCollection->getSelect()->group(array('standard_product_id', 'store_id'));

        $itemStock = $itemStockCollection->getData();

        $oldRequestCollection = Mage::getModel('ved_gorders/purchaserequestitem')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('product_id', $productId);

        $oldStockCollection = Mage::getModel('ved_gorders/orderitemstock')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('product_id', $productId);

        if ($oldRequestCollection->count() > 0) {
            $oldRequest = $oldRequestCollection->getFirstItem()->getData();
        }
        else {
            $oldRequest = null;
        }
        if ($oldStockCollection->count() > 0) {
            $oldStock = $oldStockCollection->getFirstItem()->getData();
        }
        else {
            $oldStock = null;
        }
        $data = array(
            "stock" => $result['data'],
            "hold" => $itemStock,
            "old_request" => $oldRequest,
            'old_stock' => $oldStock
        );
        //var_dump(json_encode($data));die();
        return $data;
    }

    public function checkProductWarehousePV($productSku)
    {
        $path = 'product/in-stock-quantity?' . http_build_query(array(
                'product_skus' => $productSku,
            ));
        $response = file_get_contents(Mage::helper('ved_gorders')->getApiUrlWarehouse($path));
        $result = json_decode($response, true);
        $data = array(
            "stock" => $result['data'],
        );
        return $data;
    }

}