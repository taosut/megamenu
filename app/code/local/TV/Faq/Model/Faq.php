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
class TV_Faq_Model_Faq extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('tv_faq/faq');
    }

        public function isAllowHtml()
    {
        return $this->getAnswerHtml();
    }
}
