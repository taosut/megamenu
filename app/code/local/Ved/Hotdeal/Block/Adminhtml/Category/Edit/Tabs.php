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
class Ved_Hotdeal_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructs current object
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('hotdeal_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('tv_faq')->__('Category Information'));
    }

    /**
     * Prepares the page layout
     *
     * Adds the tabs to the left tab menu.
     *
     * @return Ved_Hotdeal_Block_Adminhtml_Category_Edit_Tabs
     */
    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();

        $this->addTab(
            'main_section',
            array(
                'label' => Mage::helper('tv_faq')->__('General information'),
                'title' => Mage::helper('tv_faq')->__('General information'),
                'content' => $this->getLayout()->createBlock('ved_hotdeal/adminhtml_category_edit_tab_form')->toHtml(),
                'active' => true,
            )
        );
        return $return;
    }
}
