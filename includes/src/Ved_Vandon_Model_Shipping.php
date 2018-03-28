<?php
class Ved_Vandon_Model_Shipping  extends Mage_Core_Model_Abstract
{
     public function __construct()
    {
        parent::_construct();
        $this->_init('vandon/shipping', 'order_id');
        $read = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
        $write = Mage::getSingleton( 'core/resource' )->getConnection( 'core_write' ); // To write to the database
        $this->read = $read;
    }
    
    function order_detail($order_id){
        //return 1;
        $query = "SELECT
                    sfo.total_qty_ordered AS product_amount,
                    sfo.total_paid AS total_price,
                    sfo.base_shipping_amount AS delivery_fee,
                    sfog.increment_id AS reference_no,
                    sfog.entity_id AS reference_id,
                    sfog.shipping_name AS recipient_name,
                    sfog.billing_name AS recipient_username,
                    sfog.created_at AS ref_order_created, oa_shipping.telephone AS recipient_phone,
                    oa_shipping.street AS recipient_address,
                    oa_shipping.region AS recipient_province,
                    oa_shipping.city AS recipient_district,
                    dcr. CODE AS recipient_province_id

                FROM      sales_flat_order_grid sfog
                LEFT JOIN sales_flat_order sfo ON sfog.entity_id = sfo.entity_id
                LEFT JOIN sales_flat_order_address oa_shipping ON oa_shipping.parent_id = sfo.entity_id AND oa_shipping.address_type = 'shipping' 
                LEFT JOIN sales_flat_order_address oa_billing ON oa_shipping.parent_id= sfo.entity_id AND oa_shipping.address_type = 'billing' 
                LEFT JOIN directory_country_region dcr ON dcr.default_name = oa_shipping.region
                 
                WHERE sfog.entity_id = {$order_id}
                    ";
         $result = $this->read->query( $query );       
        while ( $row = $result->fetch() ) {
            $rows= $row;
        }
         
        $product_query = "SELECT * FROM sales_flat_order_item fi WHERE order_id = '$order_id'";  
        $product_result = $this->read->query( $product_query );
        $i = 0; 
        while ($item = $product_result->fetch()){            
            $rows['product'][$i] = $item;
            $i++;
        }
        var_dump($rows);die();
        return $rows;
    }
    
    function call_api($data){
        $params = "";
        foreach ($data AS $key=>$value) {
            if ($value != "") {
                if ($params != "") $params .= "&";
                $params .= $key . "=" . urlencode($value);
            }
        }
        $params .= "&token=C2E591F41D44CC2D35417FE4065660D3";
        $url = "http://test.vandon.ved.com.vn/ws/zomart/transportation/create?" . $params;
        var_dump($url);die();
        $tuCurl = curl_init($url); 
        curl_setopt($tuCurl, CURLOPT_POST, 1);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS,$params);  
        curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION  ,1); 
        curl_setopt($tuCurl, CURLOPT_HEADER      ,0);  // DO NOT RETURN HTTP HEADERS 
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL    
        curl_setopt($tuCurl, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($tuCurl);  
        $result = json_decode($response, true);

        curl_close($tuCurl);
        return $result;
    }
}
?>