<?php

include __DIR__ . "/../vendor/autoload.php";
$config = include __DIR__ . "/config.php";

$client = new Solarium\Client($config);

$query = $client->createQuery($client::QUERY_SELECT);

$resultSet = $client->execute($query);

echo "<h1>Simple Search</h1>";

echo "<p>Found documents: ".$resultSet->getNumFound()."</p>";

foreach ($resultSet as $document) {
    echo "<hr /><table>";

    foreach ($document as $field => $value) {
        if (is_array($value)) {
            $value = implode(", ", $value);
        }

        echo "<tr><th>{$field}</th><td>{$value}</td></tr>";
    }

    echo "</table>";
}
