<?php
/**
 * Created by PhpStorm.
 * User: Loinp
 * Date: 1/10/2018
 * Time: 4:15 PM
 */
require_once '../app/Mage.php';
require_once '../vendor/autoload.php';

$nrOfBatch = 100;
$batch = 1;
$endFlag = false;

$fbClient = new \Facebook\Facebook([
    'app_id' => '1370408403078077',
    'app_secret' => '4263fb16c3edd41d0a382db0340b753c',
    'default_graph_version' => 'v2.10',
    'default_access_token' => '1370408403078077|q2RwhRIFNB3UeTHYjNHGAEFno6c'
]);

Mage::app()->setCurrentStore('admin');
$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

try {
    do {
        $count  = 0;
        echo "Start batch nr $batch.\n";
        $offset = $nrOfBatch * ($batch - 1);

        $query = "SELECT request_path FROM core_url_rewrite WHERE options != 'RP' AND store_id >= 20 LIMIT $offset, $nrOfBatch";
        $products_link = $readConnection->fetchAll($query);

        foreach ($products_link as $link) {
            $count ++;
            $url_product = "https://phongvu.vn/game/" . $link['request_path'];
            $response = $fbClient->post('?id=' . $url_product. '&scrape=true');
        }

        $batch ++;
        if ($count < $nrOfBatch) {
            $endFlag = true;
        }
    } while (!$endFlag);
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage() . "\n";
    exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage() . "\n";
    exit;
}
