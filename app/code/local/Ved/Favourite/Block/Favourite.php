<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 8/31/2017
 * Time: 5:31 PM
 */

class Ved_Favourite_Block_Favourite extends Mage_Core_Block_Template
{
    private $_frameLength = 5;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('favourite/index.phtml');
    }

    public function getUrlPage($page)
    {
        return Mage::getUrl('favourite/index') . '?p=' . $page;
    }

    private function getFrameLength()
    {
        return $this->_frameLength;
    }

    public function getRange($currentPage, $totalPage)
    {
        $start = 0;
        $end = 0;

        if ($totalPage <= $this->getFrameLength()) {
            $start = 1;
            $end = $totalPage;
        }
        else {
            $half = ceil($this->getFrameLength() / 2);
            if ($currentPage >= $half
                && $currentPage <= $totalPage - $half
            ) {
                $start  = ($currentPage - $half) + 1;
                $end = ($start + $this->getFrameLength()) - 1;
            }
            elseif ($currentPage < $half) {
                $start  = 1;
                $end = $this->getFrameLength();
            }
            elseif ($currentPage > ($totalPage - $half)) {
                $end = $totalPage;
                $start  = $end - $this->getFrameLength() + 1;
            }
        }

        return range($start, $end);
    }
}