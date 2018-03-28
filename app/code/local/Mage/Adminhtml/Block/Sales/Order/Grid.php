<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $collection
            ->join(array('oi'=>'sales/order'),
                'oi.entity_id=main_table.entity_id',
                array(
                    'qty_ordered' => 'oi.total_qty_ordered'
                ),
                null,'inner')
            ->join(array('shipping' => 'sales/order_address'),
                'main_table.entity_id = shipping.parent_id AND shipping.address_type != "billing"',
                array(
                    'shipping_telephone'       => 'shipping.telephone',
                    'shipping_address'=> new Zend_Db_Expr('concat(shipping.street,",",shipping.city,",",shipping.region) '),
                    'shipping_city'       => 'shipping.city',
                ),
                null,'left')
            ->join(array('billing' => 'sales/order_address'),
                'main_table.entity_id = billing.parent_id AND billing.address_type = "billing"',
                array(
                    'billing_telephone'       => 'billing.telephone'
                ),
                null,'left')
//            ->join(array('products' => 'sales/order_item'),
//                'main_table.entity_id = products.order_id',
//                array(
//                    'product_lists'       => new Zend_Db_Expr('concat("<table>", group_concat(concat("<tr><td>",FORMAT(products.qty_ordered,0)," </td><td>", products.name , "</td></tr>") SEPARATOR " "),"</table>")')
//                ),
//                null,'inner')
//            ->getSelect()->group('main_table.entity_id')
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index'=>'oi.increment_id'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                'filter_index'=>'oi.store_id'
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter_index'=>'oi.created_at'
        ));

//        $this->addColumn('billing_name', array(
//            'header' => Mage::helper('sales')->__('Bill to Name'),
//            'index' => 'billing_name',
//        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index'=>'shipping_name'
        ));

        $this->addColumn('shipping_telephone', array(
            'header' => Mage::helper('sales')->__('Shipping Telephone'),
            'index' => 'shipping_telephone',
            'type'  => 'text',
            'filter_index'=>'shipping.telephone',

        ));
        $this->addColumn('shipping_address', array(
            'header' => Mage::helper('sales')->__('Shipping Address'),
            'index'  => 'shipping_address',
            'type'  => 'text',
            'filter' => false,
        ));
        $this->addColumn('city', array(
            'header' => Mage::helper('sales')->__('City'),
            'index'  => 'shipping_city',
            'type' =>'text',
            'filter_index'=>'shipping.city'
        ));
//        $this->addColumn('product_lists', array(
//            'header'       => Mage::helper('sales')->__('Items Ordered'),
//            'index'        => 'product_lists',
//            'type'  => 'text',
//            'filter' => false
//
//            // 'filter_index' => '(SELECT GROUP_CONCAT(\' \', x.name) FROM sales_flat_order_item x WHERE main_table.entity_id = x.order_id AND x.product_type != \'configurable\')'
//        ));

        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Items Total'),
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'filter_index'=>'oi.qty_ordered',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
            'filter_index'=>'oi.base_grand_total'
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            'filter_index'=>'oi.order_currency_code'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter_index'=>'oi.status'
        ));

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
//            $this->addColumn('action',
//                array(
//                    'header'    => Mage::helper('sales')->__('Action'),
//                    'width'     => '50px',
//                    'type'      => 'action',
//                    'getter'     => 'getId',
//                    'actions'   => array(
//                        array(
//                            'caption' => Mage::helper('sales')->__('View'),
//                            'url'     => array('base'=>'*/sales_order/view'),
//                            'field'   => 'order_id'
//                        )
//                    ),
//                    'filter'    => false,
//                    'sortable'  => false,
//                    'index'     => 'stores',
//                    'is_system' => true,
//            ));
//        }
//        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label'=> Mage::helper('sales')->__('Cancel'),
                'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/verify')) {
            $this->getMassactionBlock()->addItem('verify_order', array(
                'label'=> Mage::helper('sales')->__('Verify'),
                'url'  => $this->getUrl('*/sales_order/massVerify'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ready')) {
            $this->getMassactionBlock()->addItem('ready_order', array(
                'label'=> Mage::helper('sales')->__('Ready'),
                'url'  => $this->getUrl('*/sales_order/massReady'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ship')) {
            $this->getMassactionBlock()->addItem('ship_order', array(
                'label'=> Mage::helper('sales')->__('Ship'),
                'url'  => $this->getUrl('*/sales_order/massShip'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                'label'=> Mage::helper('sales')->__('Hold'),
                'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                'label'=> Mage::helper('sales')->__('Unhold'),
                'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/reject')) {
            $this->getMassactionBlock()->addItem('reject_order', array(
                'label'=> Mage::helper('sales')->__('Reject'),
                'url'  => $this->getUrl('*/sales_order/massReject'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/complete')) {
            $this->getMassactionBlock()->addItem('complete_order', array(
                'label'=> Mage::helper('sales')->__('Complete'),
                'url'  => $this->getUrl('*/sales_order/massComplete'),
            ));
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/revert')) {
            $this->getMassactionBlock()->addItem('return_order', array(
                'label'=> Mage::helper('sales')->__('Revert'),
                'url'  => $this->getUrl('*/sales_order/massRevert'),
            ));
        }
        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label'=> Mage::helper('sales')->__('Print Packet List'),
            'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
            'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
            'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
            'label'=> Mage::helper('sales')->__('Print Invoices'),
            'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));


        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}
