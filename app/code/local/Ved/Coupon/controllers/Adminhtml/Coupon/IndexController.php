<?php

class Ved_Coupon_Adminhtml_Coupon_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserId = $admin_user_session->getUser()->getUserId();
        $access = false;
        $accessIds = explode(",", Mage::getStoreConfig('promo/ved_coupon/approve_permission'));
        if(in_array($adminUserId,$accessIds)){
            $access = true;
        }
        Mage::register('access', $access);
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('ved_coupon/adminhtml_couponlist');
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function getCouponListAction()
    {
        $totalPage = 0;
        $itemPerPage = 10;
        $params = Mage::app()->getRequest()->getParams();
        $coupon = $params['coupon'];
        $page = intval($params['page']);
        $sale_name = $params['sale_name'];
        $request_order = $params['request_order'];
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserId = $admin_user_session->getUser()->getUserId();
        $couponLists = Mage::getModel('ved_coupon/coupon_request')
            ->getCollection();
        $couponLists->setOrder('id', 'DESC');
        $access = false;
        $accessIds = explode(",", Mage::getStoreConfig('promo/ved_coupon/approve_permission'));
        if(in_array($adminUserId,$accessIds)){
            $access = true;
        }
        if(!$access) {
            $couponLists = $couponLists->addFieldToFilter('admin_user_id',$adminUserId);
        }
        if($coupon){
            $couponLists = $couponLists->addFieldToFilter('coupon_code',$coupon);
        }
        if($sale_name){
            $couponLists = $couponLists->addFieldToFilter('sale_name',$sale_name);
        }
        if($request_order){
            $couponLists = $couponLists->addFieldToFilter('request_order',$request_order);
        }
        $totalItems = $couponLists->getSize();
        if($totalItems%$itemPerPage > 0){
            $totalPage = (int)($totalItems/$itemPerPage) + 1;
        }else{
            $totalPage = $totalItems/$itemPerPage;
        }
        if($page>1){
            $couponLists = $couponLists->setPageSize($itemPerPage)->setCurpage($page)->getData();
        }else{
            $couponLists = $couponLists->setPageSize($itemPerPage)->setCurPage(1)->getData();
        };
        $result = array(
            'totalItems' => $totalItems,
            'data'       => $couponLists,
            'total_page' => $totalPage
        );
        $this->getResponse()->setBody(json_encode($result));
    }

    public function createRequestAction()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserId = $admin_user_session->getUser()->getUserId();
        $params = $this->getRequest()->getParams();
        if($admin_user_session->getUser()->getName() == $params['sale_name']){
            $order_id = $params['request_order'];
            $order = Mage::getModel('sales/order')->load($order_id);
            if(!$order->getId()){
                $message = ("Không tồn tại đơn hàng với mã: ". $order_id);
                $this->getResponse()->setBody(json_encode(array(
                    "status" => "error",
                    "message" => $message
                )));
            }else{
                $coupon = Mage::getModel('ved_coupon/coupon_request');
                $newRequest = array(
                    'sale_name' => $params['sale_name'],
                    'request_order' => $order_id,
                    'status' => 0,
                    'coupon_code' => $params['coupon'],
                    'discount_amount' => $params["discount_amount"],
                    'date_request' => date('Y-m-d H:i:s',time()),
                    'date_approve' => "",
                    'admin_user_id' => $adminUserId
                );
                try{
                    $coupon->setData($newRequest)->save();
                }catch(Exception $e){
                    $message = ("Xảy ra lỗi khi thêm yêu cầu tạo Coupon vào cơ sở dữ liệu");
                    $this->getResponse()->setBody(json_encode(array(
                        "status" => "error",
                        "message" => $message
                    )));
                    return false;
                }
                $this->getResponse()->setBody(json_encode(array(
                    "status" => "success"
                )));
            }
        }else{
            $message = ("Tên người tạo coupon không chính xác với tài khoản hiện tại");
            $this->getResponse()->setBody(json_encode(array(
                "status" => "error",
                "message" => $message
            )));
        }
        return true;
    }

    public function approveCouponAction()
    {
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminUserId = $admin_user_session->getUser()->getUserId();
        $accessIds = explode(",", Mage::getStoreConfig('promo/ved_coupon/approve_permission'));
        if(in_array($adminUserId,$accessIds)){
            $params = $this->getRequest()->getParams();
            $requestList = $params['params'];
            foreach($requestList as $requestId){
                $couponRequest = Mage::getModel('ved_coupon/coupon_request')->load($requestId);
                if(!$couponRequest->getStatus()){
                    try{
                        $this->createCoupon($couponRequest);
                        $couponRequest->setDateApprove(date('Y-m-d H:i:s',time()))->save();
                        $couponRequest->setStatus(1)->save();
                    }catch (Exception $e){
                        $message = ("Xảy ra lỗi khi thêm yêu cầu tạo Coupon vào cơ sở dữ liệu");
                        $this->getResponse()->setBody(json_encode(array(
                            "status" => "error",
                            "message" => $message
                        )));
                        return false;
                    }
                }else{
                    $message = ("Yêu cầu tạo coupon đã được chấp thuận từ trước");
                    $this->getResponse()->setBody(json_encode(array(
                        "status" => "error",
                        "message" => $message
                    )));
                    return false;
                }
            }
            $this->getResponse()->setBody(json_encode(array(
                "status" => "success"
            )));
            return true;
        }
    }

    private function createCoupon($couponRequest)
    {
        // All customer group ids
        $customerGroupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
        // SalesRule Rule model
        $rule = Mage::getModel('salesrule/rule');
        $live = Mage::getStoreConfig('promo/ved_coupon/coupon_live_time');
        // Rule data
        $rule->setName($couponRequest->getCouponCode())
            ->setDescription('Tạo theo yêu cầu của ' .$couponRequest->getSaleName().' theo đơn hàng '.$couponRequest->getRequestOrder())
            ->setToDate(date('Y-m-d H:i:s',time()+ ($live*84600)))
            ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->setCouponCode($couponRequest->getCouponCode())
            ->setUsesPerCustomer(1)
            ->setUsesPerCoupon(1)
            ->setCustomerGroupIds($customerGroupIds)
            ->setIsActive(1)
            ->setConditionsSerialized('')
            ->setActionsSerialized('')
            ->setStopRulesProcessing(0)
            ->setIsAdvanced(1)
            ->setProductIds('')
            ->setSortOrder(0)
            ->setSimpleAction(Mage_SalesRule_Model_Rule::CART_FIXED_ACTION)
            ->setDiscountAmount($couponRequest->getDiscountAmount())
            ->setDiscountQty(1)
            ->setDiscountStep(0)
            ->setSimpleFreeShipping('0')
            ->setApplyToShipping('0')
            ->setIsRss(0)
            ->setWebsiteIds(array(20))
            ->setStoreLabels(array());
        $rule->save();
    }
}