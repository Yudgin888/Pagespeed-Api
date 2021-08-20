<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(3600);

$api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

$access_token = 'access_token';

$br = '<br>';
$strategies = [
    'mobile',
    'desktop'
];
$urls = [
    'http://example.com',
];

$total = [];
foreach ($urls as $url) {
    echo $url . $br;
    foreach ($strategies as $strategy) {
        $params = [
            'strategy=' . $strategy,
            'fields=lighthouseResult',
            'url=' . $url,
        ];
        if (!empty($access_token)) {
            $params[] = 'access_token=' . $access_token;
        }

        $end_point = $api . '?' . implode('&', $params);
        $result = get_web_page($end_point);
        if (!$result['errno']) {
            try {
                $content = json_decode($result['content'], true, 512, JSON_THROW_ON_ERROR);
                if (!isset($content['error'])) {
                    if (!isset($total[$strategy])) {
                        $total[$strategy] = [
                            'summary' => 0,
                            'count' => 0,
                        ];
                    }
                    $total[$strategy]['summary'] += $content['lighthouseResult']['categories']['performance']['score'];
                    $total[$strategy]['count']++;
                    echo $strategy . ' - ' . ($content['lighthouseResult']['categories']['performance']['score'] * 100) . $br;
                } else {
                    echo $content['error']['status'] . ' - ' . $content['error']['message'] . $br;
                }
            } catch (JsonException $e) {
                echo $e->getMessage() . $br;
            }
        } else {
            echo $result['errno'] . ' - ' . $result['errmsg'] . $br;
        }
        sleep(1);
    }
    echo '-------------------------------------------------------------' . $br;
}
echo $br . $br . 'Total:' . $br;
foreach ($total as $st => $val) {
    echo $st . ':' . $br;
    echo 'count: ' . $val['count'] . $br;
    echo 'avg: ' . ($val['summary'] / $val['count'] * 100) . $br;
}


function get_web_page($url)
{
    $user_agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36";

    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER => false,            // return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING => "",             // handle all encodings
        CURLOPT_USERAGENT => $user_agent,   // who am I
        CURLOPT_AUTOREFERER => true,        // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT => 120,             // timeout on response
        CURLOPT_MAXREDIRS => 1,             // stop after n redirects
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => false,    // SSL Cert checks
        //CURLOPT_CAINFO => $_SERVER['DOCUMENT_ROOT'] . "/cacert.pem",
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);

    $header['errno'] = $err;
    $header['errmsg'] = $errmsg;
    $header['content'] = $content;
    return $header;
}