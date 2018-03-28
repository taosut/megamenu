<?php
require_once('app/Mage.php');
require_once('vendor/autoload.php');
session_start();
$OAUTH2_CLIENT_ID = '825913221537-vkjocvv9ftch4tr175ubbnu95oars83u.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = '1s4EJHj5U0vvwzwDtOzLgU9o';
$GA_ID = "ga:143270307";
try {
    $client = new Google_Client();
    putenv('GOOGLE_APPLICATION_CREDENTIALS=google_serivice_key.json');
    $client->useApplicationDefaultCredentials();
    $client->setScopes('https://www.googleapis.com/auth/analytics');
    $analytics = new Google_Service_Analytics($client);
    /**
     * @var  Google_Service_Analytics_GaData $data
     */
    $data = $analytics->data_ga->get('ga:143270307', $_GET['startDate'], $_GET['endDate'], 'ga:transactions,ga:transactionRevenue', [
        'dimensions' => 'ga:source',
        'sort' => '-ga:transactions',
        'filters' => 'ga:transactions>0',
    ]);
    $rows = [];
    foreach ($data->getRows() as $key => $row) {
        $rows[] = [
            'source' => $row[0],
            'transactions' => $row[1],
            'transactionRevenue' => $row[2],
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($rows);
} catch (Exception $e) {
    echo "<pre>";
    echo($e->getTraceAsString());
    die();
}
