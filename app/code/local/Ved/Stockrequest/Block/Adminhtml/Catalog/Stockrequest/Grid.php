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
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ved_Stockrequest_Block_Adminhtml_Catalog_Stockrequest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('stockrequestGrid');
        $this->setDefaultSort('stock_request_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('stockrequest_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ved_stockrequest/stockrequest')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /*protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }*/

    protected function _prepareColumns()
    {
        $this->addColumn('stock_request_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'stock_request_id',
                'filter' => false
            ));
        $this->addColumn('user_name',
            array(
                'header'=> Mage::helper('catalog')->__('Họ tên người yêu cầu'),
                'index' => 'user_name',
                'type'  => 'text',
            ));

        $this->addColumn('phone_number',
            array(
                'header'=> Mage::helper('catalog')->__('Số điện thoại'),
                'index' => 'phone_number',
                'type'  => 'text',
            ));

        $this->addColumn('request_content',
            array(
                'header'=> Mage::helper('catalog')->__('Nội dung yêu cầu'),
                'index' => 'request_content',
                'type'  => 'text',
                'string_limit' => '500'
            ));

        $this->addColumn('product_name',
            array(
                'header'=> Mage::helper('catalog')->__('Sản phẩm yêu cầu'),
                'index' => 'product_name',
                'type'  => 'text',
            ));

        $this->addColumn('created_at',
            array(
                'header'=> Mage::helper('catalog')->__('Ngày yêu cầu'),
                'index' => 'created_at',
                'type'  => 'datetime',
            ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('stock_request_id');
        $this->getMassactionBlock()->setFormFieldName('product');

//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'=> Mage::helper('catalog')->__('Delete'),
//             'url'  => $this->getUrl('*/*/massDelete'),
//             'confirm' => Mage::helper('catalog')->__('Are you sure?')
//        ));
//
//        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('catalog')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('catalog')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

//    public function getRowUrl($row)
//    {
//        return $this->getUrl('*/*/edit', array(
//                'id'=>$row->getId())
//        );
//    }
}
