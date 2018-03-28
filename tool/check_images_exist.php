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

    foreach ($results as $result) {
        $count ++;
        $filePath = IMAGE_DIR . 'catalog/product' .$result['value'];

        if(!is_file($filePath)){
            echo $filePath . ' is not exist. Product_id is '.$result['entity_id']." \n";
        }
    }

    $batch ++;
    if ($count < $nrOfBatch) {
        $endFlag = true;
    }

} while (!$endFlag);