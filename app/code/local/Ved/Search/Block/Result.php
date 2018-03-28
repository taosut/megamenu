<?php

class Ved_Search_Block_Result extends Mage_Core_Block_Template
{
    protected $_frameLength = 5;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('search/result.phtml');
    }

    protected function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = $this->__("Search results for: '%s'", Mage::registry('keyWord'));

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }

        // modify page title
        $title = $this->__("Search results for: '%s'", Mage::registry('keyWord'));
        $this->getLayout()->getBlock('head')->setTitle($title);
        return parent::_prepareLayout();
    }

    private function getFrameLength()
    {
        return $this->_frameLength;
    }

    public function getSearchPageUrlBuildPC($keyWord, $_page, $priceFrom = null, $priceTo = null, $catId)
    {
        $searchPageUrl = Mage::getUrl('search/index/result') . '?q=' . $keyWord;
//        if ($priceFrom !== '') {
//            $searchPageUrl .= '&price_from=' . $priceFrom;
//        }
//        if ($priceTo !== '') {
//            $searchPageUrl .= '&price_to=' . $priceTo;
//        }
        return $searchPageUrl . '&p=' . $_page . '&cat=' . $catId;
    }

    public function getSearchPageUrl($keyWord, $page, $priceFrom = null, $priceTo = null)
    {
        $searchPageUrl = Mage::getUrl('search/index/result') . '?q=' . $keyWord;
//        if ($priceFrom !== '') {
//            $searchPageUrl .= '&price_from=' . $priceFrom;
//        }
//        if ($priceTo !== '') {
//            $searchPageUrl .= '&price_to=' . $priceTo;
//        }

        return $searchPageUrl . '&p=' . $page;
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