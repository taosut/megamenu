<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/12/2017
 * Time: 6:55 PM
 */

class Ved_Buildpc_Block_List extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('List my PC');
        $this->setTemplate('buildpc/list.phtml');
    }

    protected $_frameLength = 5;

    private function getFrameLength()
    {
        return $this->_frameLength;
    }

    public function getPageUrl($currentPage)
    {
        $pageUrl = Mage::getUrl('buildpc/saving/list') . '?p=' . $currentPage;
        return $pageUrl;
    }

    public function getRange($currentPage, $totalPage)
    {
        $start = 0;
        $end = 0;

        if ($totalPage <= $this->getFrameLength()) {
            $start = 1;
            $end = $totalPage;
        } else {
            $half = ceil($this->getFrameLength() / 2);
            if ($currentPage >= $half
                && $currentPage <= $totalPage - $half
            ) {
                $start = ($currentPage - $half) + 1;
                $end = ($start + $this->getFrameLength()) - 1;
            } elseif ($currentPage < $half) {
                $start = 1;
                $end = $this->getFrameLength();
            } elseif ($currentPage > ($totalPage - $half)) {
                $end = $totalPage;
                $start = $end - $this->getFrameLength() + 1;
            }
        }

        return range($start, $end);
    }
}