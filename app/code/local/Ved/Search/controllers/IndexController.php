<?php

class Ved_Search_IndexController extends Mage_Core_Controller_Front_Action
{
    /** Search result PV **/
    public function resultAction()
    {
        $storeCode = Mage::app()->getStore()->getId();
        $keyWord = $this->getRequest()->getParam('q');
        $searchOption = $this->getRequest()->getParam('option');
        if ($searchOption == 0) {
            $searchOption = null;
        }

        $keyWord = str_replace(str_split('\\/:*"<>;'), ' ', $keyWord);
        $keyWord = addslashes($keyWord);
        $keyWord = trim($keyWord);
        $catIdSearch = $this->getRequest()->getParam('cat');
        if (is_null($catIdSearch)) {
            $catIdSearch = $searchOption;
        }

        $currentPage = $this->getRequest()->getParam('p') ? (int)$this->getRequest()->getParam('p') : 1;
//        $priceFrom = $this->getRequest()->getParam('price_from');
//        $priceTo = $this->getRequest()->getParam('price_to');

        $itemsPerPage = 20;
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = $this->scoutingQuery($keyWord, $storeCode, $catIdSearch) . ' LIMIT ' . $itemsPerPage;
        if ($currentPage > 1) {
            $query .= ' OFFSET ' . ($currentPage - 1) * $itemsPerPage;
        }
        $result = $readConnection->query($query);
        $rows = $result->fetchAll();
        $entityIds = array();
        foreach ($rows as $row) {
            array_push($entityIds, $row['entity_id']);
        }
        $productsCollection =
            Mage::getModel('catalog/product')
                ->getCollection()
                ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->addStoreFilter($storeCode)
                ->addAttributeToFilter('entity_id', array('in' => $entityIds))
                ->addAttributeToFilter('price', array('gt' => 0));

//        if ($priceFrom || $priceTo) {
//            $productsCollection->addFieldToFilter('price', array('from' => $priceFrom, 'to' => $priceTo));
//        }
//
//        if ($manufacturer) {
//            $productsCollection->addAttributeToFilter('sku', array('like' => '%TEK%'));
//        }

        $products = $productsCollection->setPageSize($itemsPerPage)->setCurPage($currentPage);

        $searchType = 1;
//        $totalItems = $productsCollection->getSize();
        $currentQuery = $this->scoutingQuery($keyWord, $storeCode, $catIdSearch);
        $totalItems = $this->countResultQuery($currentQuery);
        $totalPage = ((int)($totalItems / $itemsPerPage));
        if (($totalItems % $itemsPerPage) != 0) {
            $totalPage = intval($totalPage) + 1;
        }
        Mage::register('keyWord', $keyWord);
        Mage::register('$_products', $products);
        Mage::register('searchType', $searchType);
        Mage::register('totalPage', $totalPage);
        Mage::register('currentPage', $currentPage);
        Mage::register('catIdSearch', $catIdSearch);
//        Mage::register('priceFrom', $priceFrom);
//        Mage::register('priceTo', $priceTo);
        $query = Mage::getModel('catalogsearch/query');
        // Check StoreCode
        $queryData = $query->getCollection()->addFieldToFilter('query_text', $keyWord)->addFieldToFilter('store_id', $storeCode)->getData();
        if (count($queryData)) {
            //Update query
            $query->load($queryData[0]['query_id']);
            $query->setData('num_results', $totalPage);
            $query->setData('popularity', $queryData[0]['popularity'] + 1);
            try {
                $query->save();
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        } else {
            //Insert new query
            $newQuery = array(
                'query_text' => $keyWord,
                'popularity' => 1,
                'store_id' => $storeCode,
                'display_in_terms' => 1,
                'is_active' => 1,
                'is_processed' => 0,
            );
            try {
                $query->setData($newQuery)->save();
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /** Search result Tekshop **/
    public function indexAction()
    {
        $storeCode = Mage::app()->getStore()->getId();
        $keyWord = $this->getRequest()->getParam('q');
        $keyWord = str_replace(str_split('\\/:*"<>;'), ' ', $keyWord);
        $keyWord = addslashes($keyWord);
        $keyWord = trim($keyWord);
        Mage::register('keyWord', $keyWord);
        $itemsPerPage = 20;
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $readConnection->query($this->scoutingQuery($keyWord, $storeCode) . ' LIMIT ' . $itemsPerPage);
        $rows = $result->fetchAll();
        if ($rows) {
            $entityIds = array();
            foreach ($rows as $row) {
                array_push($entityIds, $row['entity_id']);
            }
            $products =
                Mage::getModel('catalog/product')
                    ->getCollection()
                    ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addUrlRewrite()
                    ->addStoreFilter($storeCode)
                    ->addAttributeToFilter('entity_id', array('in' =>
                        $entityIds))->addAttributeToFilter('price', array('gt' => 0))->load();
            $searchType = 1;
            $currentQuery = $this->scoutingQuery($keyWord, $storeCode);
            Mage::register('$_products', $products);
            Mage::register('searchType', $searchType);
        } else {
            $currentQuery = $this->scoutingQuery($keyWord, $storeCode);
            Mage::register('$_products', null);
            $this->loadLayout();
            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('search/result'));
            $this->renderLayout();
//            $entityIds = array();
//            $result = $readConnection->query($this->revelantSearchQuery($keyWord,$storeCode).' LIMIT '.$itemsPerPage);
//
//            $currentQuery = $this->revelantSearchQuery($keyWord,$storeCode);
//            $rows = $result->fetchAll();
//            if($rows) {
//                foreach($rows as $row)
//                {
//                    array_push($entityIds,$row['entity_id']);
//                }
//                $products =
//                    Mage::getModel('catalog/product')
//                        ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
//                        ->getCollection()
//                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
//                        ->addUrlRewrite()
//                        ->addStoreFilter($storeCode)
//                        ->addAttributeToFilter('entity_id', array('in' =>
//                            $entityIds))->load();
//                $searchType=2;
//                Mage::register('searchType',$searchType);
//                Mage::register('$_products', $products);
//            }else{
//                $currentQuery = $this->scoutingQuery($keyWord,$storeCode);
//                Mage::register('$_products',null);
//                $this->loadLayout();
//                $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('search/result'));
//                $this->renderLayout();
//            }
        }
        $totalItems = $this->countResultQuery($currentQuery);
        $totalPage = ((int)($totalItems / 20));
        if (($totalItems % 20) != 0) {
            $totalPage = intval($totalPage) + 1;
        }
        Mage::register('totalPage', $totalPage);
        $query = Mage::getModel('catalogsearch/query');
        // Check StoreCode
        $queryData = $query->getCollection()->addFieldToFilter('query_text', $keyWord)->addFieldToFilter('store_id', $storeCode)->getData();
        if (count($queryData)) {
            //Update query
            $query->load($queryData[0]['query_id']);
            $query->setData('num_results', $totalPage);
            $query->setData('popularity', $queryData[0]['popularity'] + 1);
            try {
                $query->save();
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        } else {
            //Insert new query
            $newQuery = array(
                'query_text' => $keyWord,
                'popularity' => 1,
                'store_id' => $storeCode,
                'display_in_terms' => 1,
                'is_active' => 1,
                'is_processed' => 0,
            );
            try {
                $query->setData($newQuery)->save();
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }
        // Check search in modal (build pc)
        $catIdSearch = $this->getRequest()->getParam('cat');
        Mage::register('catIdSearch', $catIdSearch);
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('search/result'));
        $this->renderLayout();
    }

    public function loadMoreAction()
    {
        $itemsPerPage = 20;
        $storeCode = Mage::app()->getStore()->getId();
        $keyWord = $this->getRequest()->getParam('keyWord');
        $searchType = $this->getRequest()->getParam('searchType');
        $currentPage = (int)$this->getRequest()->getParam('currentPage');
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        if ($searchType == 1) {
            $thisQuery = $this->scoutingQuery($keyWord, $storeCode);
            $result = $readConnection->query($thisQuery . ' LIMIT ' . $itemsPerPage . ' OFFSET ' . $currentPage * $itemsPerPage);

        } elseif ($searchType == 2) {
            $thisQuery = $this->revelantSearchQuery($keyWord, $storeCode);
            $result = $readConnection->query($thisQuery . ' LIMIT ' . $itemsPerPage . ' OFFSET ' . $currentPage * $itemsPerPage);
        }
        $rows = (isset($result)) ? $result->fetchAll() : [];
        $entityIds = array();
        foreach ($rows as $row) {
            array_push($entityIds, $row['entity_id']);
        }
        $products =
            Mage::getModel('catalog/product')
                ->getCollection()
                ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->addStoreFilter($storeCode)
                ->addAttributeToFilter('entity_id', array('in' =>
                    $entityIds))->load();
//        $_products = Mage::getResourceModel('catalog/product_collection');
//        $_products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
//        $_products
//            ->addMinimalPrice()
//            ->addFinalPrice()
//            ->addTaxPercents()
//            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
//            ->addUrlRewrite()
//            ->addStoreFilter($storeCode)
//            ->addAttributeToFilter('name',
//                array(
//                    'like' => $queryWord
//                )
//            )
//            ->setPageSize(20)
//            ->setCurPage($currentPage+1);
        Mage::register('productsCollections', $products);
        $this->loadLayout();
        $result = $this->getLayout()->createBlock('search/resultmore')->toHtml();
        $this->getResponse()->setBody(($result));
    }

    public function indexModalAction()
    {
        $keyWord = $this->getRequest()->getParam('q');

        $queryWord = "";

        $words = explode(" ", trim($keyWord));
        foreach ($words as $word) {
            if ($word != "") {
                $queryWord = $queryWord . '%' . $word . '%';
            }
        }

        Mage::register('queryWord', $queryWord);
        Mage::register('keyWord', $keyWord);

        $currentPage = $this->getRequest()->getParam('currentPage');
        Mage::register('currentPage', $currentPage);

        // Check search in modal (build pc)
        $catIdSearch = $this->getRequest()->getParam('cat');
        Mage::register('catIdSearch', $catIdSearch);

        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('search/resultmodal'));
        $this->renderLayout();
    }

    public function searchPosAction()
    {
        $keyWord = $this->getRequest()->getParam('q');

        $queryWord = "";

        $words = explode(" ", trim($keyWord));
        foreach ($words as $word) {
            if ($word != "") {
                $queryWord = $queryWord . '%' . $word . '%';
            }
        }

        $_products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addAttributeToFilter('name',
                array(
                    'like' => $queryWord
                )
            )
            ->setPageSize(10)
            ->setCurPage(1);

        Mage::register('$_products', $_products);

        $resultBlock = $this->getLayout()->createBlock('search/resultpos');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['result_pos' => $resultBlock->toHtml()]));
    }

    private function getQueryWord($keyword)
    {
        $queryWord = "";

        $words = explode(" ", trim($keyword));
        foreach ($words as $word) {
            if ($word != "") {
                $queryWord = $queryWord . '%' . $word;
            }
        }
        return $queryWord . '%';
    }

    public function getSearchSuggestionAction()
    {
        $keyWord = $this->getRequest()->getParam('q');
        $queryArr = array();
        array_push($queryArr, $this->getQueryWord($keyWord));
        $synonyms = json_decode(Mage::getModel('core/variable')->loadByCode('symnonymous_keyword')->getValue('plain'))->key;

        $keyWord = Mage::helper('catalog/product_url')->format(strtolower($keyWord));
        $queryWord = $this->getQueryWord($keyWord);
        array_push($queryArr, $queryWord);

        foreach ($synonyms as $synonym) {
            $index = Mage::helper('catalogsearch')->isContain($keyWord, $synonym);
            if ($index !== null) {
                foreach ($synonym as $key => $value)
                    if ($index != $key) {
                        $searchWord = str_replace($synonym[$index], $value, $keyWord);
                        $queryWord = $this->getQueryWord($searchWord);
                        array_push($queryArr, $queryWord);
                    }
                break;
            }
        }
        $arrCondition = array();
        foreach ($queryArr as $query) {
            array_push($arrCondition, array('like' => $query));
        }

        $likeCond = '';
        foreach ($queryArr as $index => $query) {
            $likeCond .= "(data_index LIKE '$query')" . ($index + 1 !=  count($queryArr) ? ' OR ' : '');
        }

        $_products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addUrlRewrite()
            ->addAttributeToFilter('price', array('gt' => 0))
            ->addAttributeToFilter('instock', array('nin' => array(2, 5)))
            ->setPageSize(5)
            ->setCurPage(1);

        $_products->getSelect()
            ->join(
                array('ft' => 'catalogsearch_fulltext'),
                "ft.product_id = e.entity_id and ft.store_id = " . Mage::app()->getStore()->getId(),
                array('data_index' => 'ft.data_index')
            )->where($likeCond);


        Mage::register('suggestion_products', $_products);

        $resultBlock = $this->getLayout()->createBlock('search/resultsuggestion');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['result_suggestion' => $resultBlock->toHtml(), 'result_suggestion_count' => count($_products)]));
    }

    public function subscribeEmailAction()
    {
        try {
            $subscribeEmail = $this->getRequest()->getParam('subscribe_email');
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($subscribeEmail);
            if ($subscriber->getId()) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['error' => true, 'error_message' => 'Địa chỉ Email ' . $subscribeEmail . ' đã đăng ký nhận bản tin Phong Vũ rồi!']));
            } else {
                Mage::getModel('newsletter/subscriber')->subscribe($subscribeEmail);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['success' => true, 'success_message' => 'Đăng ký nhận bản tin Phong Vũ cho Email ' . $subscribeEmail . ' thành công!']));
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(['error' => true, 'error_message' => 'Có lỗi xảy ra khi đăng ký nhận bản tin, vui lòng thử lại sau!']));
        }
    }

    public function escapeHtml($data, $allowedTags = null)
    {
        return Mage::helper('core')->escapeHtml($data, $allowedTags);
    }

    public function getImageLabel($product = null, $mediaAttributeCode = 'image')
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $label = $product->getData($mediaAttributeCode . '_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }

//    public function breakAndSearchQuery($keyWord,$storeId)
//    {
//        $matchKeyWord="";
//        $words = explode(" ",$keyWord);
//        foreach ($words as $word)
//        {
//            if($word!=" ")
//            {
//                $matchKeyWord = $matchKeyWord." ".$word;
//            }
//        }
//        $coreQuery =
//            "SELECT
//             e. NAME,
//             e. entity_id,
//            MATCH (e. NAME) AGAINST ('".$matchKeyWord."' IN NATURAL LANGUAGE MODE) AS score
//            FROM
//                catalog_product_flat_".$storeId." AS e
//            HAVING
//                score > 1
//            ORDER BY
//                score DESC";
//        return $coreQuery;
//    }

    public function scoutingQuery($keyWord, $storeId, $catId = null)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'warehouse_sku');
        $attr_id = $attribute->getId();
        $coreQuery =
            "SELECT
                1 AS status,
                e.entity_id,
                e.name,
                ev.`value`
                FROM
                    catalog_product_flat_" . $storeId . " AS e ";
        $coreQuery .= "INNER JOIN `catalog_product_entity_varchar` AS ev ON ev.entity_id = e.entity_id AND ev.attribute_id = " . $attr_id;
        $connectedJoin = "INNER JOIN `catalog_category_product_index` AS `cat_index` ON cat_index.product_id = e.entity_id
            AND cat_index.store_id = " . $storeId . " AND cat_index.category_id = " . $catId;
        $connectedCondition = "WHERE e.visibility in (3,4)";
        $words = explode(" ", $keyWord);
        $conditionByName = array();
        foreach ($words as $word) {
            array_push($conditionByName, "e.`NAME` LIKE " . "'%" . $word . "%'");
        }
        $tempName = "";
        for ($i = 0; $i < count($conditionByName) - 1; $i++) {
            $tempName .= $conditionByName[$i] . " AND ";
        }
        $tempName .= $conditionByName[count($conditionByName) - 1];

        $tempSku = "ev.`value` LIKE " . "'%" . $keyWord . "%'";

        $connectedCondition .= ' AND (('.$tempName.') OR (' . $tempSku . '))';
        if ($catId != null) {
            $coreQuery = $coreQuery . " " . $connectedJoin . " " . $connectedCondition;
        } else $coreQuery = $coreQuery . " " . $connectedCondition;
        return $coreQuery;
    }

    public function revelantSearchQuery($keyWord, $storeId)
    {
        $matchKeyWord = "";
        $words = explode(" ", $keyWord);
        foreach ($words as $word) {
            if ($word != " ") {
                $matchKeyWord = $matchKeyWord . " " . $word;
            }
        }
        $coreQuery =
            "SELECT
                1 AS STATUS,
                e.entity_id,
                e.type_id,
                e. NAME,
                MATCH (e. NAME) AGAINST ('" . $matchKeyWord . "' IN NATURAL LANGUAGE MODE) AS score
            FROM
                catalog_product_flat_" . $storeId . " AS e
            HAVING
                score > 1
            ORDER BY
                score DESC";
        return $coreQuery;
    }

    public function countResultQuery($currentQuery)
    {
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $countQuery =
            "SELECT COUNT(*) FROM(" .
            $currentQuery
            . ") AS number";
        $result = $readConnection->query($countQuery)->fetch();
        return intval($result['COUNT(*)']);
    }
}