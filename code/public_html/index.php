<?php

use GuzzleHttp\Exception\GuzzleException;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(3600);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ClientHelper.php';

$client = ClientHelper::getClient();
$httpClient = $client->authorize();

$strategies = [
    'mobile',
    'desktop'
];
$urls = [
    'http://example.com',
];

$result = [
    'urls' => [],
    'total' => [],
];
foreach ($urls as $url) {
    foreach ($strategies as $strategy) {
        $params = [
            'strategy=' . $strategy,
            'fields=lighthouseResult',
            'url=' . $url,
        ];

        $end_point = ClientHelper::$API . '?' . implode('&', $params);
        try {
            $response = $httpClient->get($end_point);
            $content = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (!isset($content['error'])) {
                $result['urls'][$url][$strategy] = $content['lighthouseResult']['categories']['performance']['score'];

                if (!isset($result['total'][$strategy])) {
                    $result['total'][$strategy] = [
                        'summary' => 0,
                        'count' => 0
                    ];
                }
                $result['total'][$strategy]['summary'] += $content['lighthouseResult']['categories']['performance']['score'];
                $result['total'][$strategy]['count']++;
            } else {
                $result['urls'][$url][$strategy] = $content['error']['status'] . ' - ' . $content['error']['message'];
            }
        } catch (GuzzleException | Exception $e) {
            echo $e->getMessage();
            die;
        }
    }
}

foreach ($result['urls'] as $url => $data) {
    echo $url . '<br>';
    foreach ($data as $strategy => $val) {
        echo $strategy . ' - ' . ($val * 100) . '<br>';
    }
    echo '-----------------------<br>';
}
echo '###########################<br>';
echo 'Total:<br>';
echo '###########################<br>';
foreach ($result['total'] as $strategy => $data) {
    echo $strategy . ':<br>';
    echo 'avg - ' . ($data['summary'] / $data['count'] * 100) . '<br>';
    echo 'count - ' . $data['count'] . '<br>';
    echo '-----------------------<br>';
}
die;
