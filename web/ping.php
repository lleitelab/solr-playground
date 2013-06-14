<?php

include __DIR__ . "/../vendor/autoload.php";
$config = include __DIR__ . "/config.php";

echo "<h1>Solarium Version: " . Solarium\Client::VERSION . "</h1> ";

$client = new Solarium\Client($config);

$ping = $client->createPing();

try {
    $result = $client->ping($ping);
    echo "Ping query successful! <br />";
    echo "<pre>";
    var_dump($result->getData());
} catch (Solarium\Exception $e) {
    echo "Ping failed";
}
