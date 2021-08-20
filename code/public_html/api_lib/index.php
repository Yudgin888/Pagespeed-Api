<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(3600);

require_once __DIR__ . '/../../vendor/autoload.php';

$api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

$cred_file = __DIR__ . '/cred.json';
if (!file_exists($cred_file)) {
    echo 'Credentials file not found';
    die;
}

$cred = null;
try {
    $cred = json_decode(file_get_contents($cred_file), true, 512, JSON_THROW_ON_ERROR);
} catch (Exception $ex) {
    echo $ex->getMessage();
    die;
}

$client = new Google\Client();
$client->setApplicationName('Pagespeeder');
$client->setDeveloperKey($cred['api_key']);

$httpClient = $client->authorize();

$url = '';
$end_point = $api . '?strategy=mobile&fields=lighthouseResult&url=' . $url;
$response = $httpClient->get($end_point);
$content = $response->getBody()->getContents();

die;