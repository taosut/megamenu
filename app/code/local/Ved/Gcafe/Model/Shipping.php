<?php
class Ved_Vandon_Model_Shipping  extends Mage_Core_Model_Abstract
{
     public function __construct()
    {
        parent::_construct();
        $this->_init('vandon/shipping', 'order_id');
        $read  = Mage::getSingleton( 'core/resource' )-> getConnection( 'core_read' ); // To read from the database
        $write = Mage::getSingleton( 'core/resource' )-> getConnection( 'core_write' ); // To write to the database
        $this->read = $read;
        $this->write = $write;
    }
    
    function order_detail($order_id){
        
        $sales_flat_order_grid    = Mage::getSingleton( 'core/resource' )->getTableName( 'sales_flat_order_grid' );  
        $sales_flat_order         = Mage::getSingleton( 'core/resource' )->getTableName( 'sales_flat_order' );  
        $sales_flat_order_address = Mage::getSingleton( 'core/resource' )->getTableName( 'sales_flat_order_address' );  
        $directory_country_region = Mage::getSingleton( 'core/resource' )->getTableName( 'directory_country_region' );  
        $sales_flat_order_item    = Mage::getSingleton( 'core/resource' )->getTableName( 'sales_flat_order_item' ); 
         
        $catalog_product_entity_int    = Mage::getSingleton( 'core/resource' )->getTableName( 'catalog_product_entity_int' );  
        $eav_attribute                 = Mage::getSingleton( 'core/resource' )->getTableName( 'eav_attribute' );  
        $eav_attribute_option          = Mage::getSingleton( 'core/resource' )->getTableName( 'eav_attribute_option' );  
        $eav_attribute_option_value    = Mage::getSingleton( 'core/resource' )->getTableName( 'eav_attribute_option_value' );  
        $supplier                      = Mage::getSingleton( 'core/resource' )->getTableName( 'supplier' );  
        $directory_country_region      = Mage::getSingleton( 'core/resource' )->getTableName( 'directory_country_region' );  
        $directory_city                = Mage::getSingleton( 'core/resource' )->getTableName( 'directory_city' );  
         
        $query = "SELECT
                    sfo.total_qty_ordered AS product_amount,
                    sfog.grand_total AS total_price,
                    sfo.base_shipping_amount AS delivery_fee,
                    sfog.increment_id AS reference_no,
                    sfog.entity_id AS reference_id,
                    sfog.shipping_name AS recipient_name,
                    sfog.billing_name AS recipient_username,
                    sfog.created_at AS ref_order_created, 
                    oa_shipping.telephone AS recipient_phone,
                    oa_shipping.street AS recipient_address,
                    oa_shipping.region AS recipient_province,
                    oa_shipping.city AS recipient_district,
                    dcr.code AS recipient_province_id ,
                    dc.code AS recipient_district_id

                FROM      ".$sales_flat_order_grid." sfog
                LEFT JOIN ".$sales_flat_order." sfo ON sfog.entity_id = sfo.entity_id
                LEFT JOIN ".$sales_flat_order_address." oa_shipping ON oa_shipping.parent_id = sfo.entity_id AND oa_shipping.address_type = 'shipping' 
                LEFT JOIN ".$sales_flat_order_address." oa_billing ON oa_shipping.parent_id= sfo.entity_id AND oa_shipping.address_type = 'billing' 
                LEFT JOIN ".$directory_country_region." dcr ON dcr.default_name = oa_shipping.region
                LEFT JOIN ".$directory_city." as dc ON oa_shipping.city = dc.name      
                 
                WHERE sfog.entity_id = {$order_id}
                    ";
                  // echo $query; die();
         $result = $this->read->query( $query );       
        while ( $row = $result->fetch() ) {
            $rows= $row;
        }
         
         $product_query = "SELECT 
                                i.name , i.weight, i.qty_ordered as quantity, i.price 
                                , eaov.value as vendor_name
                                , s.supplier_contact as vendor_contact_name
                                , s.supplier_address as vendor_address
                                , s.supplier_code as vendor_code
                                , s.supplier_mobile as vendor_phone
                                , s.supplier_province as vendor_province
                                , s.supplier_district as vendor_district
                                , cr.code as vendor_province_id
                                , s.supplier_id as vendor 
                                , dc.code as vendor_district_id    
                            FROM ".$sales_flat_order_item." as i
                            LEFT JOIN ".$catalog_product_entity_int." as ei ON i.product_id = ei.entity_id
                            INNER JOIN ".$eav_attribute." as ea ON ea.attribute_id = ei.attribute_id AND ea.attribute_code = 'supplier'
                            INNER JOIN ".$eav_attribute_option." eao ON eao.attribute_id = ea.attribute_id AND eao.option_id = ei.`value`
                            INNER JOIN ".$eav_attribute_option_value." eaov ON eaov.option_id = eao.option_id
                            LEFT JOIN ".$supplier." s ON s.supplier_name = eaov.value
                            LEFT JOIN ".$directory_country_region." as cr ON cr.default_name = s.supplier_province
                            LEFT JOIN ".$directory_city." as dc ON dc.name = s.supplier_district
                            WHERE i.order_id = '$order_id'";

        //echo $product_query;die();
        $product_result = $this->read->query( $product_query );
        $i = 0; 
        while ($item = $product_result->fetch()){           
            $item['vendor_email']       = 'a@gmail.com';                      
            $item['length']             = 0;            
            $item['width']              = 0;            
            $item['height']             = 0;            
            $rows['products'][$i] = $item;
            $i++;
        }
        //var_dump($rows);die();
        return $rows;
    }
    
    function call_api($api, $data){
        $data['token'] = 'C2E591F41D44CC2D35417FE4065660D3';
        $url = "http://test.vandon.ved.com.vn/ws/zomart/transportation/".$api;

        $tuCurl = curl_init($url); 
        curl_setopt($tuCurl, CURLOPT_POST, 1);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, http_build_query($data));  
        curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION  ,1); 
        curl_setopt($tuCurl, CURLOPT_HEADER      ,0);  // DO NOT RETURN HTTP HEADERS 
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL    
        curl_setopt($tuCurl, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($tuCurl);  
        $result = json_decode($response, true);
        
        $logs = array();
        $logs['api']      = $api;
        $logs['response'] = $response;
        $logs['content']  = json_encode($data);
        $logs['ws_url']   = $url;

        $this->savelog_callws($logs);

        curl_close($tuCurl);
        return $result;
    }
    
    //save log when call ws vandon
    function savelog_callws($data){
        $userlogin = Mage::getSingleton('admin/session')->getUser();
        $uid       = $userlogin->getId();
        $username  = $userlogin->getName();
        $email     = $userlogin->getEmail();
        
        $time      = time();
        $ip        = $_SERVER['REMOTE_ADDR'];
        $server_ip = $_SERVER['SERVER_ADDR'];
        
        $sql = "INSERT INTO call_ws_vandon_log 
                VALUE ('', '{$data['api']}', '{$data['content']}', '{$time}', 
                        '{$data['response']}', {$uid}, '{$username}','{$email}', 
                        '{$ip}','{$server_ip}','{$data['ws_url']}')" ;

        $this->write->query( $sql );
    }
    
    function simple_crypt($key, $string, $action = 'encrypt'){
            $res = '';
            if($action !== 'encrypt'){
                $string = base64_decode($string);
            } 
            for( $i = 0; $i < strlen($string); $i++){
                    $c = ord(substr($string, $i));
                    if($action == 'encrypt'){
                        $c += ord(substr($key, (($i + 1) % strlen($key))));
                        $res .= chr($c & 0xFF);
                    }else{
                        $c -= ord(substr($key, (($i + 1) % strlen($key))));
                        $res .= chr(abs($c) & 0xFF);
                    }
            }
            if($action == 'encrypt'){
                $res = base64_encode($res);
            } 
            return $res;
    }
}
?>