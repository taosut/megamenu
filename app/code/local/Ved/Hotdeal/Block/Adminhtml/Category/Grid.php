<?php
/**
 * FAQ accordion for Magento

 */

/**
 * FAQ accordion for Magento
 *
 * Website: www.abc.com 
 * Email: honeyvishnoi@gmail.com
 */
class Ved_Hotdeal_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor of Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('hotdeal_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Preparation of the data that is displayed by the grid.
     *
     * @return Ved_Hotdeal_Block_Admin_Grid Self
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ved_hotdeal/category_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Preparation of the requested columns of the grid
     *
     * @return Ved_Hotdeal_Block_Admin_Grid Self
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array (
                'header' => Mage::helper('tv_faq')->__('Category #'), 
                'width' => '80px', 
                'type' => 'text', 
                'index' => 'id' ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id',
                    array (
                            'header' => Mage::helper('cms')->__('Store view'), 
                            'index' => 'store_id', 
                            'type' => 'store', 
                            'store_all' => true, 
                            'store_view' => true, 
                            'sortable' => false, 
                            'filter_condition_callback' => array (
                                    $this,
                                    '_filterStoreCondition' )
                    ));
        }
        
        $this->addColumn(
            'name',
            array(
                'header' => Mage::helper('tv_faq')->__('Category Name'), 
                'index' => 'name'
            )
        );

        $this->addColumn(
            'position',
            array(
                'header' => Mage::helper('tv_faq')->__('Position Name'),
                'index' => 'position',
                'renderer' => 'Ved_Hotdeal_Block_Adminhtml_Category_Renderer_Position',
                'sortable' => false,
                'type' => 'options',
                'options' => array(
                    '1' => Mage::helper('cms')->__('Top'),
                    '2' => Mage::helper('cms')->__('Bottom'),
                    '3' => Mage::helper('cms')->__('Left'),
                    '4' => Mage::helper('cms')->__('Right'))
            )
        );
        
        $this->addColumn('is_active', 
                array (
                        'header' => Mage::helper('cms')->__('Active'), 
                        'index' => 'is_active', 
                        'type' => 'options', 
                        'width' => '70px', 
                        'options' => array (
                                0 => Mage::helper('cms')->__('No'), 
                                1 => Mage::helper('cms')->__('Yes') )
                ));
        
        $this->addColumn(
            'action', 
            array (
                    'header' => Mage::helper('tv_faq')->__('Action'), 
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array (
                        array (
                            'caption' => Mage::helper('tv_faq')->__('Edit'), 
                            'url' => array (
                                'base' => '*/*/edit'
                            ), 
                            'field' => 'category_id'
                        ),
                    ),
                    'filter' => false, 
                    'sortable' => false, 
                    'index' => 'stores', 
                    'is_system' => true,
            )
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Helper function to do after load modifications
     *
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Helper function to add store filter condition
     *
     * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        
        $this->getCollection()->addStoreFilter($value);
    }
    
    /**
     * Helper function to reveive on row click url
     *
     * @param Ved_Hotdeal_Model_Faq $row Current rows dataset
     * @return string URL for current row's onclick event
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array (
                'id' => $row->getId() ));
    }

    /**
     * Helper function to receive grid functionality urls for current grid
     *
     * @return string Requested URL
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/grid',
            array (
                '_current' => true,
            )
        );
    }
}
