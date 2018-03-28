<?php
class Ved_SupplierNew_Block_Adminhtml_Suppliernew_Switcher extends Mage_Adminhtml_Block_Template
{
    const XPATH_HINT_KEY = 'supplier_switcher';
    var $_storeVarName = 'supplier';
    var $_hasDefaultOption = "";
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('supplier/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Supplier Views'));
    }
    public function getSuppliers()
    {
        $collection = Mage::getModel('suppliernew/supplier')->getResourceCollection();
        return $collection->load();
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption()
    {
        if (null !== $this->_hasDefaultOption) {
            return true;
        }
        return false;
    }
    public function getDefaultOption(){
        return $this->_hasDefaultOption;
    }
    public function getUseConfirm(){
        return false;
    }
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_storeVarName => null));
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getSupplierCode(){
        return $this->getRequest()->getParam($this->_storeVarName);
    }
    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function getHintUrl()
    {
        if (null === $this->_hintUrl) {
            $this->_hintUrl = Mage::helper('core/hint')->getHintByCode(self::XPATH_HINT_KEY);
        }
        return $this->_hintUrl;
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<a'
                . ' href="'. $this->escapeUrl($url) . '"'
                . ' onclick="this.target=\'_blank\'"'
                . ' title="' . $this->__('What is this?') . '"'
                . ' class="link-store-scope">'
                . $this->__('What is this?')
                . '</a>';
        }
        return $html;
    }
}
