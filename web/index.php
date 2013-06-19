<?php

include __DIR__ . "/../vendor/autoload.php";


$slimConfig = include __DIR__ . "/../protected/slim_config.php";
$twigConfig = include __DIR__ . "/../protected/twig_config.php";
$solariumConfig = include __DIR__ . "/../protected/solarium_config.php";

$app = new \Slim\Slim($slimConfig);

\Slim\Extras\Views\Twig::$twigOptions = $twigConfig;

$app->view(new \Slim\Extras\Views\Twig());

$app->get('/', function () use ($app) {
    $app->render('index.html.twig');
});

$client = new Solarium\Client($solariumConfig);

/**
 * Ping
 */
$app->get('/ping', function () use ($app, $client) {
    $data = array();

    $ping = $client->createPing();
    $data['solariumVersion'] = Solarium\Client::VERSION;

    try {
        $result = $client->ping($ping);
        $data['success'] = true;
        $data['result'] = $result->getData();
    } catch (Solarium\Exception $e) {
        $data['success'] = false;
    }

    $app->render('ping.html.twig', $data);
});

/**
 * Simple search
 */
$app->get('/simple_search', function () use ($app, $client) {
    $data = array();

    $query = $client->createQuery($client::QUERY_SELECT);
    $data['result'] = $client->execute($query);

    $app->render('simple_search.html.twig', $data);
});

$app->get('/facet_field', function () use ($app, $client) {
    $data = array();
    $query = $client->createSelect();
    $facetSet = $query->getFacetSet();
    $facetSet->createFacetField('stock')->setField('inStock');

    $data['resultSet'] = $client->select($query);
    $data['facets'] = $data['resultSet']->getFacetSet()->getFacet('stock');

    $app->render('facet_field.html.twig', $data);
});

$app->run();
