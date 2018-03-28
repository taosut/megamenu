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
class Ved_Hotdeal_Block_Adminhtml_Catalog_Hotdeal_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('hotdealGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('hotdeal_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('ved_hotdeal/hotdeal')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
        ));
        $this->addColumn('title',
            array(
                'header'=> Mage::helper('catalog')->__('Title'),
                'index' => 'title',
                'type'  => 'text',
        ));

        $this->addColumn('link',
            array(
                'header'=> Mage::helper('catalog')->__('Link'),
                'index' => 'link',
                'type'  => 'text',
        ));

        $this->addColumn('date_from',
            array(
                'header'=> Mage::helper('catalog')->__('Date_from'),
                'width' => '150px',
                'index' => 'date_from',
                'type'  => 'datetime',
        ));

        $this->addColumn('date_to',
            array(
                'header'=> Mage::helper('catalog')->__('Date_to'),
                'width' => '150px',
                'index' => 'date_to',
                'type'  => 'datetime',
        ));

        $this->addColumn('position',
            array(
                'header'=> Mage::helper('catalog')->__('Position'),
                'width' => '10px',
                'index' => 'position',
                'type'  => 'number',
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '10px',
                'index' => 'status',
                'type'  => 'options',
                'options'=>array(
                    0 =>'Inactive',
                    1 => 'Active'
                ),
        ));
       $this->addColumn('full_size', array(
            'header'       => Mage::helper('catalog')->__('Image Full Size'),
            'index'        => 'full_size',
            'type'  => 'text',
            'renderer'  => 'Ved_Hotdeal_Block_Adminhtml_Catalog_Renderer_HotdealImage',
            'filter' => false
        ));
       $this->addColumn('small_size', array(
            'header'       => Mage::helper('catalog')->__('Image Small Size'),
            'index'        => 'small_size',
            'type'  => 'text',
            'renderer'  => 'Ved_Hotdeal_Block_Adminhtml_Catalog_Renderer_HotdealImage',
            'filter' => false
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'=>$row->getId())
        );
    }
}
