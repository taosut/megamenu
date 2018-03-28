<?php
require_once './config.php';

$dsn = 'mysql:host='.HOST.';dbname='.DB;
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

$dbh = new PDO($dsn, USER, PASSWORD, $options);

$nrOfBatch = 100;
$batch = 1;
$endFlag = false;

do {
    $count  = 0;
    echo "Start batch nr $batch.\n";
    $offset = $nrOfBatch * ($batch - 1);

    $query = "SELECT entity_id, value FROM catalog_product_entity_media_gallery LIMIT $offset,$nrOfBatch";
    $stmt = $dbh->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    foreach ($results as $result) {
        $count ++;
        $filePath = IMAGE_DIR . 'catalog/product' .$result['value'];
        $path = $result['value'];

        if( (!is_file($filePath) || !filesize($filePath) )
            && strpos($path, 'uploads') !== false){
            echo $filePath . ' is not exist. Product_id is '.$result['entity_id']." \n";
            echo "url path:  https://phongvu.vn" . $path . "\n";
            echo 'file path: ' . IMAGE_DIR . 'catalog/product' . $path . "\n";

            @unlink($filePath);
            @mkdir(dirname(IMAGE_DIR . 'catalog/product' . $path), 0755, true);

            $response = file_get_contents("https://phongvu.vn" . $path,
                false, 
                stream_context_create($arrContextOptions));
            echo substr($response, 0, 10) . "\n";
            file_put_contents(IMAGE_DIR . 'catalog/product' . $path , $response);
        }
    }

    $batch ++;
    if ($count < $nrOfBatch) {
        $endFlag = true;
    }

} while (!$endFlag);