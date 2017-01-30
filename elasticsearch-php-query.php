<?php

use Elasticsearch\ClientBuilder;
require 'vendor/autoload.php';

$link1 = $_REQUEST['link'];
$link = explode('-', $link1);
$src = $link[0];
$dest = $link[1];

$hosts = [
        'host' => 'http://username:password@localhost:9200',
        'host' => 'http://username:password@otherhost:9200',
    ];
    $client = ClientBuilder::create()->setHosts($hosts)->build();
    $params = [
        'index' => 'latency-2017*',
        'type'  => 'latency',
        'client' => [ 'ignore' => [400, 404] ],
        'body' => [
            'query' => [
              'bool' => [
                    'must' => [
                        [ 'match' => [ 'data.host' => $src ] ],
                        [ 'match' => [ 'data.destination' => $dest ] ],
                    ]
                ]
            ],
            'sort' => [ "timestamp" => [ "order" => "desc" ]],
            'size' => 2016
        ]
    ];
    $a = $client->search($params);

    if ($a['hits']['total'] < 1)
    {
    	$params = [
        'index' => 'latency-2017*',
        'type'  => 'latency',
        'client' => [ 'ignore' => [400, 404] ],
        'body' => [
            'query' => [
              'bool' => [
                    'must' => [
                        [ 'match' => [ 'data.host' => $dest ] ],
                        [ 'match' => [ 'data.destination' => $src ] ],
                    ]
                ]
            ],
            'sort' => [ "timestamp" => [ "order" => "desc" ]],
            'size' => 2016
        ]
    ];
    $a = $client->search($params);
    }


// Hourly 288 data points
for ($x = 0; $x < 288; $x++) {
    $es['data'][] = $a['hits']['hits'][$x]['_source']['data']['avg_rtt'];
    $esMax['data'][] = $a['hits']['hits'][$x]['_source']['data']['max_rtt'];
    $esMin['data'][] = $a['hits']['hits'][$x]['_source']['data']['min_rtt'];
    $sucRate['data'][] = $a['hits']['hits'][$x]['_source']['data']['successRate'];
    $es['time'][] = $a['hits']['hits'][$x]['_source']['timestamp'];
}

// Weekly 2016 data points

?>
