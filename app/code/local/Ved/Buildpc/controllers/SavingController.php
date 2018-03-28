<?php
/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 9/21/2017
 * Time: 5:13 PM
 */
class Ved_Buildpc_SavingController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $bpArray = $this->getRequest()->getParam('bpArray');
            $name = htmlspecialchars(strip_tags($this->getRequest()->getParam('name')));
            $price = 0;

            // check if duplicate existed PC
            $allBuildpc = Mage::getModel('Ved_Buildpc/buildpc')->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId());

            $existed = false;
            $buildPc_id = 0;
            foreach ($allBuildpc as $buildpc) {
                $isPC = true;
                foreach ($bpArray as $bpEle) {
                    $item = Mage::getModel('Ved_Buildpc/detail')->getCollection()
                        ->addFieldToFilter('parent_id', $buildpc->getId())
                        ->addFieldToFilter('category_id', intval($bpEle['catId']))
                        ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
                        ->addFieldToFilter('product_id', intval($bpEle['productId']));
                    if (!$item->count()) {
                        $isPC = false;
                        break;
                    }
                }
                if ($isPC) {
                    $buildPc_id = $buildpc->getId();
                    $existed = true;
                    break;
                }
            }

            if ($existed) {
                $result = array(
                    'status' => 'existed',
                    'name' => Mage::getModel('Ved_Buildpc/buildpc')->load($buildPc_id)->getName()
                );
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
            // end

            // create new PC
            foreach ($bpArray as $bpEle) {
                $price += intval($bpEle['productPrice']) * intval($bpEle['productQty']);
            }
            $buildpc = Mage::getModel('Ved_Buildpc/buildpc');
            $buildpc->setData(array(
                'name' => $name,
                'customer_id' => $customer->getId(),
                'price' => $price,
                'created_at' => now(),
                'updated_at' => now()
            ))->save();
            foreach ($bpArray as $bpEle) {
                $buildpc->saveItem(intval($bpEle['productId']), intval($bpEle['catId']), intval($bpEle['productQty']));
            }
            $result = array('status' => 'completed');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }

    public function editAction()
    {
        try {
            $bpArray = $this->getRequest()->getParam('bpArray');
            $name = htmlspecialchars(strip_tags($this->getRequest()->getParam('name')));
            $id = $this->getRequest()->getParam('id');

            $myPC = Mage::getModel('Ved_Buildpc/buildpc')->load($id);

            $price = 0;
            foreach ($bpArray as $bpEle) {
                $price += intval($bpEle['productPrice']) * intval($bpEle['productQty']);
            }
            $myPC->addData(array(
                'name' => $name,
                'price' => $price,
                'updated_at' => now()
            ))->save();

            $products = Mage::getModel('Ved_Buildpc/detail')->getCollection()
                ->addFieldToFilter('parent_id', $id)
                ->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            foreach ($products as $product) {
                $product->delete();
            }

            foreach($bpArray as $bpEle) {
                $myPC->saveItem(intval($bpEle['productId']), intval($bpEle['catId']), intval($bpEle['productQty']));
            }
            $result = array('status' => 'completed');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }

    public function listAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $itemPerPage = 5;
            $currentPage = $this->getRequest()->getParam('p') ? $this->getRequest()->getParam('p') : 1;
            $customer = $session->getCustomer();
            $list = Mage::getModel('Ved_Buildpc/buildpc')->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId());

            $totalItems = $list->getSize();
            $totalPage = 1;
            if($totalItems > $itemPerPage){
                if($totalItems % $itemPerPage){
                    $totalPage = (int) ($totalItems/$itemPerPage) + 1;
                }
                else {
                    $totalPage = (int) ($totalItems/$itemPerPage);
                }
            }
            $list = $list->setPageSize($itemPerPage)->setCurPage($currentPage)->load();

            Mage::register('totalPage', $totalPage);
            Mage::register('listPC', $list);
            Mage::register('currentPage', $currentPage);

            $this->loadLayout();
//            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('buildpc/list'));
            $this->renderLayout();
        }
        else {
            $this->_redirect('/');
        }
    }

    public function loadMoreAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $itemPerPage = 10;
            $customer = $session->getCustomer();
            $currentPage = (int)$this->getRequest()->getParam('currentPage');
            $myPcs = Mage::getModel('Ved_Buildpc/buildpc')->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId())
                ->setPageSize($itemPerPage)
                ->setCurPage($currentPage + 1)
                ->load();
            Mage::register('myPcs', $myPcs);
            $this->loadLayout();
            $result = $this->getLayout()->createBlock('buildpc/buildpcMore')->toHtml();
            echo $result;
        }
        else {
            $this->_redirect('/');
        }
    }

    public function removeAction()
    {
        try {
            $session = Mage::getSingleton('customer/session');
            if ($session->isLoggedIn()) {
                #if the user is new, create new wishlist and add
                $myPC_id = $this->getRequest()->getParam('myPC_id');

                $thisPC = Mage::getModel('Ved_Buildpc/buildpc')->load(intval($myPC_id));
                $thisPC->delete();
                $result = array(
                    'status' => 'success',
                );
                $this->getResponse()->setBody(json_encode($result));
            } else {
                $result = array('status' => "not_login");
                $this->getResponse()->setBody(json_encode($result));
            }
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }

    public function loadItemsAction()
    {
        try {
            $session = Mage::getSingleton('customer/session');
            $helper = Mage::helper('catalog/image');
            if ($session->isLoggedIn()) {
                #if the user is new, create new wishlist and add
                $myPC_id = $this->getRequest()->getParam('pc');
                $thisPC = Mage::getModel('Ved_Buildpc/buildpc')->load(intval($myPC_id));
                $items = $thisPC->getAllItem();
                $result = array();
                foreach ($items as $item) {
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($item->getProductId());
                    $data = array(
                        'url' => $product->getProductUrl(),
                        'name' => $product->getName(),
                        'quantity' => $item->getQuantity(),
                        'image_url' => (string) $helper->init($product, 'small_image')->resize(150, 150)
                    );
                    array_push($result,$data);
                }
                Mage::register('items', $result);
                $this->loadLayout();
                $result = $this->getLayout()->createBlock('buildpc/More')->toHtml();
                echo $result;
            } else {
                $result = array('status' => "not_login");
                $this->getResponse()->setBody(json_encode(array('items' => $result)));
            }
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }
}