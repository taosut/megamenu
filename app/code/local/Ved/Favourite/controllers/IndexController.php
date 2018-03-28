<?php

class Ved_Favourite_IndexController extends Mage_Core_Controller_Front_Action
{
    public function loadMoreAction()
    {
        $currentPage = (int)$this->getRequest()->getParam('currentPage');
        $wishID      = $this->getRequest()->getParam('wishID');
        $items = Mage::getModel('wishlist/item')
            ->getCollection()
            ->addFieldToFilter('wishlist_id',$wishID)
            ->getData();
        $itemPerPage = 10;
        $products = array();
        foreach($items as $item){
            array_push($products,$item['product_id']);
        }
        $productCollection = Mage::getModel('catalog/product')
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
            ->getCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite()
            ->addStoreFilter(20)
            ->addAttributeToFilter('entity_id', array('in' =>
                $products))
            ->setPageSize($itemPerPage)
            ->setCurPage($currentPage+1)
            ->load();
        Mage::register('products', $productCollection);
        $this->loadLayout();
        $result = $this->getLayout()->createBlock('favourite/favouriteMore')->toHtml();
        echo $result;
    }

    public function indexAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $wishlistModel = Mage::getModel('wishlist/wishlist');
            $page = $this->getRequest()->getParam('p') ? $this->getRequest()->getParam('p') : 1;
            $wishlistCollection = $wishlistModel->getCollection();
            $customerWishList = $wishlistCollection
                ->addFieldToFilter('customer_id', $customerData->getId())
                ->getData();
            if (count($customerWishList)) {
                $wishlist = $customerWishList[0];
                $wishlistItemModel = Mage::getModel('wishlist/item');
                $wishlistItemCollection = $wishlistItemModel->getCollection();
                $items = $wishlistItemCollection
                    ->addFieldToFilter('wishlist_id', $wishlist['wishlist_id'])
                    ->getData();
                if(count($items))
                {
                    $itemPerPage = 8;
                    $products = array();
                    foreach($items as $item){
                        array_push($products,$item['product_id']);
                    }
                    $productCollection = Mage::getModel('catalog/product')
                        ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                        ->getCollection()
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addUrlRewrite()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addAttributeToFilter('entity_id', array('in' =>
                            $products));
                    $totalItems = $productCollection->getSize();
                    if($totalItems <= $itemPerPage){
                        $totalPage = 1;
                    }
                    else{
                        if($totalItems % $itemPerPage){
                            $totalPage = (int) ($totalItems/$itemPerPage) + 1;
                        }
                        else{
                            $totalPage = (int) ($totalItems/$itemPerPage);
                        }
                    }
                    $productCollection = $productCollection->setPageSize($itemPerPage)->setCurPage($page)->load();
                    $listisOwned = true;
                    Mage::register('totalPage', $totalPage);
                    Mage::register('products', $productCollection);
                    Mage::register('wishID',$wishlist['wishlist_id']);
                    Mage::register('owned',$listisOwned);
                    Mage::register('currentPage', $page);
                    $this->loadLayout();
//                    $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('favourite/favourite'));
                    $this->renderLayout();
                }
                else{
                    $this->loadLayout();
//                    $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('favourite/favourite'));
                    $this->renderLayout();
                }
            }
            else{
                $this->loadLayout();
//                $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('favourite/favourite'));
                $this->renderLayout();
            }
        }
        else{
            $this->_redirect('/');
        }
    }

    public function shareAction()
    {
        $sharingCode = $this->getRequest()->getParam('s');
        $wishlist = Mage::getModel('wishlist/wishlist')
            ->getCollection()
            ->addFieldToFilter('sharing_code', $sharingCode)
            ->getData();
        if (count($wishlist)) {
            $wishlist = $wishlist[0];

            $wishlistItems = Mage::getModel('wishlist/item')
                ->getCollection()
                ->addFieldToFilter('wishlist_id', $wishlist['wishlist_id'])
                ->getData();
            $items = array();
            $itemPerPage = 10;
            foreach ($wishlistItems as $wish) {
                array_push($items, $wish['product_id']);
            }
            $productCollection = Mage::getModel('catalog/product')
                ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                ->getCollection()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->addStoreFilter(20)
                ->addAttributeToFilter('entity_id', array('in' =>
                    $items))->load();
            $totalItems = $productCollection->getSize();
            if($totalItems <= $itemPerPage){
                $totalPage = 1;
            }
            else{
                if($totalItems % $itemPerPage){
                    $totalPage = (int) ($totalItems/$itemPerPage) + 1;
                }
                else{
                    $totalPage = (int) ($totalItems/$itemPerPage);
                }
            }
            $productCollection = $productCollection->setPageSize($itemPerPage)->setCurPage(1)->load();
            $listisOwned = false;



            if(Mage::getSingleton('customer/session')->isLoggedIn())
            {
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                if((int)$wishlist['customer_id']==(int)$customerId){
                    $listisOwned = true;
                }
            }
            Mage::register('totalPage', $totalPage);
            Mage::register('products', $productCollection);
            Mage::register('wishID', $wishlist['wishlist_id']);
            Mage::register('owned', $listisOwned);
            $this->loadLayout();
            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('favourite/favourite'));
            $this->renderLayout();
        } else {
            $this->_redirect('*/');
        }
    }

    public function addToWishlistAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            #if the user is new, create new wishlist and add
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $wishlistModel = Mage::getModel('wishlist/wishlist');
            $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
            $customerWishList = $wishlistCollection
                ->addFieldToFilter('customer_id', $customerData->getId())
                ->getData();
            if (!count($customerWishList)) {
                #create new wishlist
                $newWishlist = array(
                    'customer_id' => $customerData->getId(),
                    'shared' => 0
                );
                #check if sharingCode is unique or not
                while (1) {
                    $sharingCode = Mage::helper('core')->uniqHash();
                    $checkExistedCode = $wishlistCollection
                        ->addFieldToFilter('sharing_code', $sharingCode)
                        ->getData();
                    if (!count($checkExistedCode)) {
                        break;
                    }
                }
                $newWishlist['sharing_code'] = $sharingCode;
                try {
                    $wishlistModel->setData($newWishlist)->save();
                } catch (Exception $e) {
                    var_dump($e->getMessage());
                }
                $customerWishList = $wishlistCollection
                    ->addFieldToFilter('customer_id', $customerData->getId())
                    ->getData();
            }

            #add current item to wishlist
            $productId = $this->getRequest()->getParam('product');
            $wishlistItemModel = Mage::getModel('wishlist/item');
            $wishlistItemCollection = $wishlistItemModel->getCollection();
            $checkExistedItem = $wishlistItemCollection
                ->addFieldToFilter('wishlist_id', $customerWishList[0]['wishlist_id'])
                ->addFieldToFilter('product_id', $productId)
                ->getData();
            if (!count($checkExistedItem)) {
                $newWishlistItem = array(
                    'wishlist_id' => $customerWishList[0]['wishlist_id'],
                    'product_id' => $productId,
                    'store_id' => 20,
                    'description' => '',
                    'qty' => 1
                );
                try {
                    $wishlistItemModel->setData($newWishlistItem)->save();
                    $result = array('success' => "Thêm vào danh sách yêu thích thành công !");
                    $this->getResponse()->setBody(json_encode($result));
                } catch (Exception $e) {
                    $result = array('failed' => "Có lỗi xảy ra");
                    $this->getResponse()->setBody(json_encode($result));
                }
            } else {
                $result = array('success' => "Sản phẩm đã được thêm vào danh sách trước đó !");
                $this->getResponse()->setBody(json_encode($result));
            }
        } else {
            $result = array('failed' => "Vui lòng đăng nhập trước !");
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    public function removeFromDetailAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $wishlistItemModel = Mage::getModel('wishlist/item');
            $productId = $this->getRequest()->getParam('product');
            $customer = $session->getCustomer()->getId();
            $customerWishList = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFieldToFilter('customer_id',$customer)
                ->getData();
            if (count($customerWishList)) {
                $checkItem = $wishlistItemModel
                    ->getCollection()
                    ->addFieldToFilter('wishlist_id',$customerWishList[0]['wishlist_id'])
                    ->addFieldToFilter('product_id',$productId)
                    ->getData();
                if(count($checkItem))
                {
                    $wishlistItemModel->load($checkItem[0]['wishlist_item_id']);
                    $wishlistItemModel->delete();
                    $result = array(
                        'success' => "Đã xóa sản phẩm khỏi danh sách!",
                    );
                    $this->getResponse()->setBody(json_encode($result));
                }
                else{
                    $result = array('failed' => "Wishlist không tồn tại !");
                    $this->getResponse()->setBody(json_encode($result));
                }
            } else {
                $result = array('failed' => "Khách hàng chưa có wishlist !");
                $this->getResponse()->setBody(json_encode($result));
            }

        } else {
            $result = array('failed' => "Vui lòng đăng nhập trước !");
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    public function removeFromWishListAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            #if the user is new, create new wishlist and add
            $wishlistItemModel = Mage::getModel('wishlist/item');
            $productId = $this->getRequest()->getParam('product');
            $customer = $this->getRequest()->getParam('customer');
            $currentPage = (int)$this->getRequest()->getParam('currentPage');
            $customerWishList = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFieldToFilter('customer_id',$customer)
                ->getData();
            if (count($customerWishList)) {
                $checkItem = $wishlistItemModel
                    ->getCollection()
                    ->addFieldToFilter('wishlist_id',$customerWishList[0]['wishlist_id'])
                    ->addFieldToFilter('product_id',$productId)
                    ->getData();
                if(count($checkItem))
                {
                    $wishlistItemModel->load($checkItem[0]['wishlist_item_id']);
                    $wishlistItemModel->delete();
                    // Reload items
                    $items = Mage::getModel('wishlist/item')
                        ->getCollection()
                        ->addFieldToFilter('wishlist_id',$customerWishList[0]['wishlist_id'])
                        ->getData();
                    $itemPerPage = 10;
                    $products = array();
                    foreach($items as $item){
                        array_push($products,$item['product_id']);
                    }
                    $productCollection = Mage::getModel('catalog/product')
                        ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                        ->getCollection()
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addUrlRewrite()
                        ->addStoreFilter(20)
                        ->addAttributeToFilter('entity_id', array('in' =>
                            $products))
                        ->setPageSize($itemPerPage*$currentPage) #load all item from currentPage
                        ->setCurPage(1)
                        ->load();
                    Mage::register('products', $productCollection);
                    $this->loadLayout();
                    $data = $this->getLayout()->createBlock('favourite/favouriteMore')->toHtml();
                    //
                    $result = array(
                        'success' => "Đã xóa sản phẩm thành khỏi danh sách !",
                        'data' => $data
                    );
                    $this->getResponse()->setBody(json_encode($result));
                }
                else{
                    $result = array('failed' => "Wishlist không tồn tại !");
                    $this->getResponse()->setBody(json_encode($result));
                }
            } else {
                $result = array('failed' => "Khách hàng chưa có wishlist !");
                $this->getResponse()->setBody(json_encode($result));
            }

        } else {
            $result = array('failed' => "Vui lòng đăng nhập trước !");
            $this->getResponse()->setBody(json_encode($result));
        }
    }

    public function searchInWishlistAction()
    {
        $wishlistId = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('wishlistId'))));
        $searchQuery = trim(htmlspecialchars(strip_tags($this->getRequest()->getParam('searchQuery'))));
        $words = explode(' ',trim($searchQuery));
        $query = '';
        foreach ($words as $word)
        {
            if($word != ""){
                $query = $query . "%" . $word . "%";
            }
        }
        $wishlistItems = Mage::getModel('wishlist/item')
            ->getCollection()
            ->addFieldToFilter('wishlist_id', $wishlistId)
            ->getData();
        if(count($wishlistItems)){
            $products = array();
            foreach($wishlistItems as $item)
            {
                array_push($products,$item['product_id']);
            }
            $productCollection = Mage::getModel('catalog/product')
                ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                ->getCollection()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->addStoreFilter(20)
                ->addAttributeToFilter('name',
                    array(
                        'like' => $query
                    )
                )
                ->addAttributeToFilter('entity_id', array('in' =>
                    $products));
            if(count($productCollection->getData())){
                Mage::register('products', $productCollection->load());
                $this->loadLayout();
                $data = $this->getLayout()->createBlock('favourite/favouriteMore')->toHtml();
                $result = array(
                    'success' => 'Đã tìm thấy kết quả',
                    'data' => $data
                );
                $this->getResponse()->setBody(json_encode($result));

            }else{
                $result = array(
                    'notfound' => 'Không tìm thấy giỏ hàng tương ứng',
                );
                $this->getResponse()->setBody(json_encode($result));
            }
        }
        else{
            $result = array(
                'notfound' => 'Không tìm thấy giỏ hàng tương ứng'
            );
            $this->getResponse()->setBody(json_encode($result));
        }

    }

    public function removeFromListAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            #if the user is new, create new wishlist and add
            $wishlistItemModel = Mage::getModel('wishlist/item');
            $productId = $this->getRequest()->getParam('product_id');
            $customer_id = $this->getRequest()->getParam('customer_id');
            $currentPage = (int)$this->getRequest()->getParam('currentPage');
            $customerWishList = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFieldToFilter('customer_id',$customer_id)
                ->getData();
            if (count($customerWishList)) {
                $checkItem = $wishlistItemModel
                    ->getCollection()
                    ->addFieldToFilter('wishlist_id',$customerWishList[0]['wishlist_id'])
                    ->addFieldToFilter('product_id',$productId)
                    ->getData();
                if(count($checkItem))
                {
                    $wishlistItemModel->load($checkItem[0]['wishlist_item_id']);
                    $wishlistItemModel->delete();
                    // Reload items
                    $items = Mage::getModel('wishlist/item')
                        ->getCollection()
                        ->addFieldToFilter('wishlist_id',$customerWishList[0]['wishlist_id'])
                        ->getData();
                    $itemPerPage = 8;
                    $products = array();
                    foreach($items as $item){
                        array_push($products,$item['product_id']);
                    }
                    $productCollection = Mage::getModel('catalog/product')
                        ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
                        ->getCollection()
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addUrlRewrite()
                        ->addStoreFilter(20)
                        ->addAttributeToFilter('entity_id', array('in' => $products));

                    $totalItems = $productCollection->getSize();
                    if($totalItems <= $itemPerPage){
                        $totalPage = 1;
                    }
                    else{
                        if($totalItems % $itemPerPage){
                            $totalPage = (int) ($totalItems/$itemPerPage) + 1;
                        }
                        else{
                            $totalPage = (int) ($totalItems/$itemPerPage);
                        }
                    }

                    if ($totalItems % $itemPerPage == 0 && $currentPage > $totalPage) {
                        $currentPage--;
                    }

                    $productCollection = $productCollection->setPageSize($itemPerPage)->setCurPage($currentPage)->load();

                    Mage::register('totalPage', $totalPage);
                    Mage::register('products', $productCollection);
                    Mage::register('wishID', $customerWishList[0]['wishlist_id']);
                    Mage::register('owned',true);
                    Mage::register('currentPage', $currentPage);
//                    $this->loadLayout();
//                    $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('favourite/favourite'));
//                    $this->renderLayout();
//                    $this->loadLayout();
//                    $data = $this->getLayout()->createBlock('favourite/favourite')->toHtml();
//                    //
                    $result = array(
                        'success' => "Đã xóa sản phẩm thành khỏi danh sách !",
                        'data' => array(
                            'currentPage' => $currentPage
                        )
                    );
                    $this->getResponse()->setBody(json_encode($result));
                }
                else{
                    $result = array('failed' => "Wishlist không tồn tại !");
                    $this->getResponse()->setBody(json_encode($result));
                }
            } else {
                $result = array('failed' => "Khách hàng chưa có wishlist !");
                $this->getResponse()->setBody(json_encode($result));
            }

        } else {
            $result = array('failed' => "Vui lòng đăng nhập trước !");
            $this->getResponse()->setBody(json_encode($result));
        }
    }
}