<?php

class GetPriceProductAsia
{
    /**
     * @use command.php
     */
    public function run()
    {
        $offset = 0;
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->query("
                TRUNCATE TABLE asia_response_api_update_temp;
            ");
        do {
            $client = new Varien_Http_Client(Mage::getConfig()->getNode('global/phongvu_api_url') . 'product/web-price/');
            $response = $client->setMethod(Varien_Http_Client::GET);
            $response->setParameterGet('offset', $offset);
            $result = $response->request();
            $rawBody = $result->getRawBody();
            $data = json_decode($rawBody);
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $write->query("INSERT INTO asia_response_api_update_temp (`sku`, `price`, `status`) VALUES ({$value->sku},{$value->price}, {$value->status});");
                }
                $count = count($data);

            } else {
                $count = 0;
            }
            $offset = $offset + 100;
            echo "Running ...: $offset at: " . now() . PHP_EOL;

        } while ($count > 0);

        echo "Done ... at: " . now() . PHP_EOL;
    }
}