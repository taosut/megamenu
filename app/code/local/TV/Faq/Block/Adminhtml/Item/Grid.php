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
class TV_Faq_Block_Adminhtml_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor of Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('faq_grid');
        $this->setUseAjax(false);
        $this->setDefaultSort('creation_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Preparation of the data that is displayed by the grid.
     *
     * @return TV_Faq_Block_Admin_Grid Self
     */
    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('tv_faq/faq_collection');
        $this->setCollection($collection);
        #Mage::Log($collection->getData());
        return parent::_prepareCollection();
    }

    /**
     * Preparation of the requested columns of the grid
     *
     * @return TV_Faq_Block_Admin_Grid Self
     */
    protected function _prepareColumns()
    {
        $this->addColumn('faq_id', array (
                'header' => Mage::helper('tv_faq')->__('FAQ #'), 
                'width' => '80px', 
                'type' => 'text', 
                'index' => 'faq_id' ));
        
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
                                    '_filterStoreCondition' ) ));
        }
        
        $this->addColumn('question', array (
                'header' => Mage::helper('tv_faq')->__('Question'), 
                'index' => 'question' ));
        
        $this->addColumn('is_active', 
                array (
                        'header' => Mage::helper('tv_faq')->__('Status'), 
                        'index' => 'is_active', 
                        'type' => 'options', 
                        'width' => '70px', 
                        'options' => array (
                                0 => Mage::helper('tv_faq')->__('No'), 
                                1 => Mage::helper('tv_faq')->__('Yes') ) ));
        
        $this->addColumn('action', 
                array (
                        'header' => Mage::helper('tv_faq')->__('Action'), 
                        'width' => '50px', 
                        'type' => 'action', 
                        'getter' => 'getId', 
                        'actions' => array (
                                array (
                                        'caption' => Mage::helper('tv_faq')->__('Edit'), 
                                        'url' => array (
                                                'base' => 'adminhtml/faq/edit' ), 
                                        'field' => 'faq_id' ) ), 
                        'filter' => false, 
                        'sortable' => false, 
                        'index' => 'stores', 
                        'is_system' => true ));
        
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
     * @param TV_Faq_Model_Faq $row Current rows dataset
     * @return string URL for current row's onclick event
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/faq/edit', array (
                'faq_id' => $row->getFaqId() ));
    }

    /**
     * Helper function to receive grid functionality urls for current grid
     *
     * @return string Requested URL
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/faq/index', array (
                '_current' => true ));
    }
}
