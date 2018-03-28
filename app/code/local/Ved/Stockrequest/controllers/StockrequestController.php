<?php

class Ved_Stockrequest_StockrequestController extends Mage_Core_Controller_Front_Action
{
    public function saveRequestAction()
    {
        $user_name = $this->getRequest()->getParam('user_name');
        $phone_number = $this->getRequest()->getParam('phone_number');
        $request_content = $this->getRequest()->getParam('request_content');
        $product_id = $this->getRequest()->getParam('product_id');
        $product_name = $this->getRequest()->getParam('product_name');

        $data = array(
            'user_name' => $user_name,
            'phone_number' => $phone_number,
            'request_content' => $request_content,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'created_at' => date('Y-m-d H:i:s',time()),
            'status' => 1
        );

        $model = Mage::getModel('ved_stockrequest/stockrequest')->addData($data);

        try {
            $model->save(); //save data
            $result = array('success_message' => 'Gửi yêu cầu mua hàng thành công.');

            echo json_encode($result);
        } catch (Exception $e) {
            $result = array('error_message' => "Gửi yêu cầu mua hàng thất bại: " . $e->getMessage() . ".");
            echo json_encode($result);
        }
    }

}
