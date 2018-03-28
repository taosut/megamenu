<?php

class Ved_AdminApi_Adminhtml_Api_OrderController extends Ved_AdminApi_Controller_ApiController
{
    /**
     */
    public function createAction()
    {
        try {
            if ($this->getRequest()->getMethod() != "POST") {
                throw new Exception('Method not allow', 102);
            }
            $json = json_decode($this->getRequest()->getRawBody(), true);
            $data = $json['data'];
            $provinceCode = $data['province_code'];
            $inputProducts = $data['products'];
            /**
             * @var Ved_Gorders_Model_Directorycountryregion $region
             * @var Mage_Core_Model_Store $store
             */
            $region = Mage::getModel('ved_gorders/directorycountryregion')
                ->loadByAttribute('province_code', $provinceCode);
            $store = Mage::getModel('core/store')
                ->getCollection()
                ->addFieldToFilter('code', $region->getCode())
                ->getFirstItem();
            if (!$data['billing']['district']) throw new Exception('District not allow null', 104);
            if (!$data['shipping']['district']) throw new Exception('District not allow null', 105);
            if ($store->isEmpty()) {
                throw new Exception('Store invalid', 101);
            }
            if (isset($data['discount_amount']) && $data['discount_amount'] > 0) {
                $data['discount_amount'] = (-1) * $data['discount_amount'];
            }
            /**
             * Create Order Model
             * @var Mage_Sales_Model_Order $order
             */
            $order = Mage::getModel('sales/order');
            $listOrderItems = [];
            foreach ($inputProducts as $inputProduct) {
                $productCollection = new Ved_AdminApi_Model_Resource_Product_Collection();
                $product = $productCollection->getProductWithStandardProductId($inputProduct, $store);
                if (isset($inputProduct) && (int)$inputProduct['quantity'] > 0) {
                    $listOrderItems[] = [
                        'product' => $product,
                        'input' => $inputProduct,
                    ];
                }
            }
            $order->setData(array_merge($data, [
                'store_name' => $store->getName(),
                'customer_firstname' => $data['customer_name'],
                'shipping_description' => 'Thanh toán khi nhận hàng',
                'store_id' => $store->getId(),
                'store_currency_code' => 'VND',
                'global_currency_code' => 'VND',
                'order_currency_code' => 'VND',
                'base_currency_code' => 'VND',
                'created_by_id' => $data['createdById'],
                'created_by_name' => $data['createdByName'],
                'base_total_due' => $data['grand_total'],
                'base_grand_total' => $data['grand_total']
            ]));
            /**
             * @var Mage_Sales_Model_Order_Payment $payment
             */
            $payment = Mage::getModel('sales/order_payment')->save();
            $order->setPayment($payment->setMethod('free'))
                ->setStoreId($store->getId())
                ->setStoreName($store->getName());
            /**
             * @var Mage_Sales_Model_Order_Address $orderShipping
             */
            $orderShipping = Mage::getModel('sales/order_address');
            $cityShipping = $this->helper->getRegionFromCityCode($data['shipping']['address_code']);
            $orderShipping->setData(array_merge($data['shipping'], [
                'address_type' => 'shipping',
                'firstname' => $data['shipping']['name'],
                'region_id' => $cityShipping->getRegionId(),
                'region' => $cityShipping->getRegion()->getDefaultName(),
                'city' => $cityShipping->getName(),
                'postcode' => $cityShipping->getCode()
            ]));
            $order->setShippingAddress($orderShipping);

            /**
             * @var Mage_Sales_Model_Order_Address $orderBilling
             */
            $orderBilling = Mage::getModel('sales/order_address');
            $cityBilling = $this->helper->getRegionFromCityCode($data['billing']['addressCode']);
            $orderBilling->setData(array_merge($data['billing'], [
                'address_type' => 'billing',
                'firstname' => $data['billing']['name'],
                'region_id' => $cityBilling->getRegionId(),
                'region' => $cityBilling->getRegion()->getDefaultName(),
                'city' => $cityBilling->getName(),
                'postcode' => $cityBilling->getCode()
            ]));
            $order->setBillingAddress($orderBilling);
            $totalQuantity = 0;
            $subTotal = 0;
            foreach ($listOrderItems as $listOrderItem) {
                $orderItem = Mage::getModel('sales/order_item');
                $orderItem->setData(array_merge($listOrderItem['input'], [
                    'product_id' => $listOrderItem['product']->getId(),
                    'order_id' => $order->getId(),
                    'qty_ordered' => $listOrderItem['input']['quantity'],
                    'name' => isset($listOrderItem['input']['product_name']) ?
                        $listOrderItem['input']['product_name'] :
                        Mage::getModel('teko_amp/varchar')->getProductName($listOrderItem['product']->getId()),
                    'sku' => $listOrderItem['product']->getSku(),
                    'state' => 'new',
                    'unit_price' => $listOrderItem['input']['price'],
                    'product_type' => $listOrderItem['product']->getTypeId(),
                    'standard_product_id' => $listOrderItem['product']->getData('standard_product_id'),
                    'warehouse_sku' => $listOrderItem['product']->getData('warehouse_sku'),
                    'status' => 'pending',
                    'shipping_description' => 'Thanh toán khi nhận hàng',
                    'row_total' => (float)$listOrderItem['input']['quantity'] * (float)$listOrderItem['input']['price']
                ]));
                $subTotal += (float)$listOrderItem['input']['quantity'] * (float)$listOrderItem['input']['price'];
                $totalQuantity += $listOrderItem['input']['quantity'];
                $order->addItem($orderItem);
            }
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, true, 'Create by ' . $data['createdByName'])
                ->setSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setBaseSubtotalInclTax($subTotal)
                ->setTotalQtyOrdered($totalQuantity);
            if ($data['deposit_amount'] && $data['deposit_method']) {
                $order->addStatusHistoryComment(
                    'Đơn hàng được KTV thông báo đặt cọc:' . '<br>' .
                    'Số tiền: ' . number_format($data['deposit_amount'], 0, ',', '.') . 'đ<br>' .
                    'Hình thức: ' . $data['deposit_method'] . '<br>'
                );
                $order->setDepositAmount(0)->setDepositMethod(null);
            }
            $order->save();
            $result = array('result' => 'success', 'oder' => [
                'entity_id' => $order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'region_id' => $region->getRegionId(),
                'region_name' => $region->getDefaultName(),
            ]);
            Mage::log($json, null, 'create_order');
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array('result' => 'error', 'msg' => $e->getMessage(), 'errorCode' => $e->getCode());
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    public function getOrderMessageAction()
    {
        try {
            $orderId = intval($this->getRequest()->getParam('order_id', 0));

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $query = 'SELECT message FROM log_message_queue WHERE message like \'{"id":"' . $orderId . '"%\' and routing_key = "sale.order.create" LIMIT 1';

            $order = $read->fetchOne(
                $query
            );

            $result = array('result' => 'ok', 'msg' => 'success', 'data' => $order);
        } catch (Exception $e) {
            $result = array('result' => 'error', 'msg' => $e->getMessage(), 'errorCode' => $e->getCode());
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function getOrdersInTimeAction()
    {
        try {
            $fromTime = date('Y-m-d H:i:s', $this->getRequest()->getParam('from'));
            $toTime = date('Y-m-d H:i:s', $this->getRequest()->getParam('to'));
            $limit = ($this->getRequest()->getParam('limit') != null) ? intval($this->getRequest()->getParam('limit')) : 50;
            $page = ($this->getRequest()->getParam('page') != null) ? intval($this->getRequest()->getParam('page')) : 1;

            $orders = Mage::getResourceModel('sales/order_grid_collection'); // get collection
            /* join table and query filter by created_at */
            $orders
                ->join(array('oi' => 'sales/order'),
                    'oi.entity_id=main_table.entity_id',
                    array(
                        'created_at_order' => 'oi.created_at',
                        'updated_at_order' => 'oi.updated_at'
                    ),
                    null, 'inner')
                ->join(array('shipping' => 'sales/order_address'),
                    'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"',
                    array(
                        'shipping_city' => 'shipping.city',
                        'shipping_region' => 'shipping.region'
                    ),
                    null, 'left')
                ->addFieldToFilter('oi.created_at', array('from' => $fromTime, 'to' => $toTime))
                ->setPageSize($limit)
                ->setCurPage($page);

            $data = array();
            foreach ($orders as $order) {
                $comments = array();

                $history = $order->getStatusHistoryCollection(true);

                foreach ($history as $commentObj) {
                    $comment = $commentObj->getComment() == null ? 'Chờ xử lý' : $commentObj->getComment();
                    array_push($comments, strip_tags($comment));
                }
                $itemData = array(
                    'id' => $order['entity_id'],
                    'code' => $order['increment_id'],
                    'purchaseOn' => strtotime($order['created_at_order']),
                    'shipToName' => $order['shipping_name'],
                    'province' => $order['shipping_region'],
                    'district' => $order['shipping_city'],
                    'grandTotal' => $order['grand_total'],
                    'status' => $order['status'],
                    'comment' => $comments,
                    'updatedAt' => strtotime($order['updated_at_order']),
                );
                array_push($data, $itemData);
            }

            $result = array('result' => 'ok', 'msg' => 'success', 'totalRecord' => $orders->getSize(), 'data' => $data);
        } catch (Exception $e) {
            $result = array('result' => 'error', 'msg' => $e->getMessage(), 'errorCode' => $e->getCode());
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function getOrderTekshopByMonthAction()
    {
        try {
            $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
            $date = new DateTime('now', $timeZone);
            $date->modify('first day of this month');
            $date->setTime(0, 0, 0);
            $from_monthly = date('Y-m-d 23:59:59', $date->getTimestamp());

            $date->modify('last day of this month');
            $date->setTime(23, 59, 59);
            $to_monthly = date('Y-m-d 23:59:59', $date->getTimestamp());

            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $query = "select DATE_ADD(a.created_at,INTERVAL 7 hour) purchase_on, DATE_ADD(d.created_at,INTERVAL 7 hour) confirmed_at, a.entity_id, a.increment_id, b.sku, b.name, b.product_type, round(b.qty_ordered) qty_ordered,  
                        round(b.price) price, round(a.grand_total) grand_total, a.status, round(a.total_item_count) total_item_count, b.parent_item_id,
                        case when a.store_id in (20,21,23) then 'website' else 'mobile' end 'platform', c.region, a.affiliate_code
                        from sales_flat_order a join sales_flat_order_item b on a.entity_id = b.order_id
                        join sales_flat_order_address c on a.entity_id = c.parent_id and c.address_type = 'shipping'
                        JOIN
                        (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                        group by parent_id) d on a.entity_id = d.parent_id
                        where DATE_ADD(d.created_at,INTERVAL 7 hour) > '$from_monthly' and DATE_ADD(d.created_at,INTERVAL 7 hour) < '$to_monthly'
                        and a.store_id in (20,21,23) and a.status not in ('canceled', 'delivery_failed', 'no_product', 'reject', 'pending', 'stock_empty', 'stock_transfer', 'waiting_from_supplier')
                        limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }

    public function create_order_tekshopAction()
    {
        try {
            if ($this->getRequest()->getMethod() != "POST") {
                throw new Exception('Method not allow', 102);
            }
            $json = json_decode($this->getRequest()->getRawBody(), true);
            $data = $json['data'];
            $provinceCode = $data['province_code'];
            $inputProducts = $data['products'];
            $transaction = Mage::getModel('ved_adminapi/transaction')
                ->getCollection()
                ->addFieldToFilter('transaction_id', $data['transaction_id'])
                ->addFieldToFilter('status', 1)
                ->getFirstItem();
            if (!$transaction->isEmpty())
                throw new Exception('Oder is exits', 999);
            /**
             * @var Ved_Gorders_Model_Directorycountryregion $region
             * @var Mage_Core_Model_Store $store
             */
            $region = Mage::getModel('ved_gorders/directorycountryregion')
                ->loadByAttribute('province_code', $provinceCode);
            $store = Mage::getModel('core/store')->load(20);
            if (isset($data['discount_amount']) && $data['discount_amount'] > 0) {
                $data['discount_amount'] = (-1) * $data['discount_amount'];
            }
            /**
             * Create Order Model
             * @var Mage_Sales_Model_Order $order
             */
            $order = Mage::getModel('sales/order');
            $order->setData(array_merge($data, [
                'store_name' => $store->getName(),
                'customer_firstname' => $data['customer_name'],
                'shipping_description' => 'Thanh toán khi nhận hàng',
                'store_id' => $store->getId(),
                'store_currency_code' => 'VND',
                'order_currency_code' => 'VND',
                'created_by_id' => $data['created_by_id'],
                'created_by_name' => $data['created_by_name'],
                'base_total_due' => $data['grand_total'],
                'base_grand_total' => $data['grand_total'],
                'base_shipping_amount' => 0,
            ]));
            /**
             * @var Mage_Sales_Model_Order_Payment $payment
             */
            $payment = Mage::getModel('sales/order_payment')->save();
            $order->setPayment($payment->setMethod('free'))
                ->setStoreId($store->getId())
                ->setStoreName($store->getName());
            $order->save();
            $totalQuantity = 0;
            $subTotal = 0;
            foreach ($inputProducts as $inputProduct) {
                /**
                 * @var Mage_Catalog_Model_Resource_Product_Collection $products
                 * @var Mage_Catalog_Model_Product $value
                 * @var Mage_Catalog_Model_Resource_Product_Collection $productCollection
                 */
                $productCollection = Mage::getModel('catalog/product')
                    ->getCollection();
                $product = $productCollection->addAttributeToFilter('entity_id', $inputProduct['product_id'])
                    ->addStoreFilter(20)
                    ->addAttributeToFilter('type_id', 'simple')
                    ->getFirstItem();
                if (!isset($product) || !$product->getId())
                    throw new Exception('Khong tim thay san pham ' . $inputProduct['product_id'] . ' tren he thong', 100);
                if (isset($inputProduct) && (int)$inputProduct['quantity'] > 0) {
                    $orderItem = Mage::getModel('sales/order_item');
                    $orderItem->setData(array_merge($inputProduct, [
                        'product_id' => $product->getId(),
                        'order_id' => $order->getId(),
                        'qty_ordered' => $inputProduct['quantity'],
                        'name' => Mage::getModel('teko_amp/varchar')->getProductName($product->getId()),
                        'sku' => $product->getSku(),
                        'state' => 'new',
                        'product_type' => $product->getTypeId(),
                        'status' => 'pending',
                        'shipping_description' => 'Thanh toán khi nhận hàng',
                        'row_total' => (float)$inputProduct['quantity'] * (float)$inputProduct['price']
                    ]));
                    $subTotal += (float)$inputProduct['quantity'] * (float)$inputProduct['price'];
                    $totalQuantity += $inputProduct['quantity'];
                    $orderItem->save();
                }
            }
            /**
             * @var Mage_Sales_Model_Order_Address $orderBilling
             */
            $orderBilling = Mage::getModel('sales/order_address');
            $cityBilling = $this->helper->getRegionFromCityCode($data['billing']['address_code']);
            $orderBilling->setData(array_merge($data['billing'], [
                'address_type' => 'billing',
                'firstname' => $data['billing']['name'],
                'parent_id' => $order->getId(),
                'region_id' => $cityBilling->getRegionId(),
                'region' => $cityBilling->getRegion()->getDefaultName(),
                'city' => $cityBilling->getName(),
                'postcode' => $cityBilling->getCode()
            ]));
            $orderBilling->save();
            /**
             * @var Mage_Sales_Model_Order_Address $orderShipping
             */
            $orderShipping = Mage::getModel('sales/order_address');
            $cityShipping = $this->helper->getRegionFromCityCode($data['shipping']['address_code']);
            $orderShipping->setData(array_merge($data['shipping'], [
                'address_type' => 'shipping',
                'firstname' => $data['billing']['name'],
                'parent_id' => $order->getId(),
                'region_id' => $cityShipping->getRegionId(),
                'region' => $cityShipping->getRegion()->getDefaultName(),
                'city' => $cityShipping->getName(),
                'postcode' => $cityBilling->getCode()
            ]));
            $orderShipping->save();
            $newOrder = Mage::getModel('sales/order')->load($order->getId());
            $newOrder->setShippingAddressId($orderShipping->getId())
                ->setState(Mage_Sales_Model_Order::STATE_NEW, true, 'Create by ' . $data['created_by_name'])
                ->setSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setBaseSubtotalInclTax($subTotal)
                ->setTotalQtyOrdered($totalQuantity)
                ->save();
            $result = array('result' => 'success', 'oder' => [
                'entity_id' => $order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'region_id' => $region->getRegionId(),
                'region_name' => $region->getDefaultName(),
            ]);
            $transaction->setOrderId($order->getId())
                ->setTransactionId($data['transaction_id'])
                ->setSource('VNP')
                ->setCreatedAt(now())
                ->setStatus(true)
                ->save();
            Mage::log($json, null, 'create_order_vnp');
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array('result' => 'error', 'msg' => $e->getMessage(), 'errorCode' => $e->getCode());
        }
        header('Content-Type: application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

    public function update_deposit_statusAction()
    {
        $rawBody = json_decode($this->getRequest()->getRawBody(), true);
        $data = $rawBody['data'];
        $order = new Ved_AdminApi_Model_Order();
        $order->load($data['order_id']);
        if ((int)$order->getDepositAmount() == (int)$data['deposit_amount']) {
            $order->setIsConfirmDeposit(Ved_AdminApi_Model_Order::DEPOSIT_CONFIRM_TRUE)->save();
        } else {
            $order->setIsConfirmDeposit(Ved_AdminApi_Model_Order::DEPOSIT_CONFIRM_FAIL)->save();
        }
    }

    public function source_orderAction()
    {
        try {
            $request = $this->getRequest();
            $from = $this->getTimeFromTimeString($request->get('from'));
            $to = $this->getTimeFromTimeString($request->get('to'));
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $query = "
                    SELECT 
                        if(source.source is null, '(direct)',source.source) AS source,
                        COUNT(*) AS total_order,
                        SUM(CASE
                            WHEN order_history.parent_id IS NOT NULL THEN 1
                            ELSE (IF(new_order_history.parent_id IS NULL,
                                0,
                                1))
                        END) AS total_confirm,
                       SUM(main_table.grand_total) grand_total,
					   SUM(CASE
                            WHEN order_history.parent_id IS NOT NULL THEN main_table.grand_total
                            ELSE (IF(new_order_history.parent_id IS NULL,
                                0,
                                main_table.grand_total))
                        END) AS grand_total_confirm
                    FROM
                        sales_flat_order AS main_table
                            LEFT JOIN
                        (SELECT 
                            *
                        FROM
                            sales_flat_order_status_history
                        WHERE
                            created_at > '{$from}'
                                AND status = 'telephone_confirm'
                        GROUP BY parent_id) AS order_history ON order_history.parent_id = main_table.entity_id
                            LEFT JOIN
                        sales_order_utm_source AS source ON source.order_id = main_table.entity_id
                            LEFT JOIN
                        (SELECT 
                            MAX(entity_id) entity_id,
                                increment_id,
                                original_increment_id
                        FROM
                            sales_flat_order
                        WHERE
                            original_increment_id IS NOT NULL AND created_at >= '{$from}'
                        GROUP BY original_increment_id) AS `new_order` ON main_table.increment_id = new_order.original_increment_id
                            LEFT JOIN
                        (SELECT 
                            *
                        FROM
                            sales_flat_order_status_history
                        WHERE
                            created_at > '{$from}'
                                AND status = 'telephone_confirm'
                        GROUP BY parent_id) AS new_order_history ON new_order_history.parent_id = new_order.entity_id
                    WHERE
                        (main_table.store_id IN (20 , 21))
                            AND (main_table.created_at >= '{$from}')
                            AND (main_table.created_at <= '{$to}')
                            AND (main_table.original_increment_id IS NULL)
                    GROUP BY if(source.source is null, '(direct)',source.source)
                    ORDER BY main_table.entity_id DESC;
                ";

            $result = $read->fetchAll($query);
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
        header('Content-Type: application/json');
        $this->getResponse()->setBody(json_encode($result));
    }


    private function getTimeFromTimeString($timeString)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $timeString, new DateTimeZone('Asia/Ho_Chi_Minh'));
        if (!$date) throw  new Exception("Date time format is: Y-m-d H:i:s");
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    public function getOrderTekshopAction()
    {
        try {
            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $query = "select DATE_ADD(a.created_at,INTERVAL 7 hour) purchase_on, DATE_ADD(d.created_at,INTERVAL 7 hour) confirmed_at, a.entity_id, a.increment_id, b.sku, b.name, b.product_type, round(b.qty_ordered) qty_ordered,  
                        round(b.price) price, round(a.grand_total) grand_total, a.status, round(a.total_item_count) total_item_count, b.parent_item_id,
                        case when a.store_id = 23 then 'website' else 'mobile' end 'platform', c.region, a.affiliate_code
                        from sales_flat_order a join sales_flat_order_item b on a.entity_id = b.order_id
                        join sales_flat_order_address c on a.entity_id = c.parent_id and c.address_type = 'shipping'
                        JOIN
                        (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                        group by parent_id) d on a.entity_id = d.parent_id
                        where a.store_id in (20,21,23) and a.status not in ('canceled', 'delivery_failed', 'no_product', 'reject', 'pending', 'stock_empty', 'stock_transfer', 'waiting_from_supplier')
                        limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }


    public function getDepositOrderAction()
    {
        try {
            $limit = intval($this->getRequest()->getParam('limit', 50));
            $offset = intval($this->getRequest()->getParam('offset', 0));

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $query = "select DATE_ADD(a.created_at,INTERVAL 7 hour) purchase_on, DATE_ADD(d.created_at,INTERVAL 7 hour) confirmed_at, a.entity_id, a.increment_id, round(a.grand_total) grand_total, a.status, round(a.total_item_count) total_item_count, 
                         c.region, c.firstname, c.telephone, round(deposit_amount) deposit_amount, deposit_method, comment
                        from sales_flat_order a 
                        join sales_flat_order_address c on a.entity_id = c.parent_id and c.address_type = 'shipping'
                        LEFT JOIN
                        (select parent_id, min(created_at) created_at from sales_flat_order_status_history where status = 'telephone_confirm'
                        group by parent_id) d on a.entity_id = d.parent_id
                        left join (select parent_id, GROUP_CONCAT(comment,\"\n\") comment from sales_flat_order_status_history where status = 'telephone_confirm'
                        group by parent_id) e on a.entity_id = e.parent_id
                        where a.status not in ('canceled') and deposit_amount > 0
                        order by a.entity_id desc
                        limit $offset, $limit";

            $order = $read->fetchAll($query);

            $result = $order;
        } catch (Exception $e) {
            $result = array();
        }
        $this->getResponse()->setBody(json_encode($result));
    }
}