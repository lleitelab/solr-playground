<?php

include __DIR__ . "/../vendor/autoload.php";


$slimConfig = include __DIR__ . "/../protected/slim_config.php";
$twigConfig = include __DIR__ . "/../protected/twig_config.php";
$solariumConfig = include __DIR__ . "/../protected/solarium_config.php";

$app = new \Slim\Slim($slimConfig);

$app->view()->parserOptions = $twigConfig;

$app->get('/', function () use ($app) {
    $app->render('main.html.twig');
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

    $query = $client->createSelect();

    $q = $app->request->get('field_query');

    $query->setQuery('*:*');
    if ($q) {
        $query->setQuery($q);
    }
    $data['result'] = $client->execute($query);
    $data['q'] = $q;

    $app->render('simple_search.html.twig', $data);
});

/**
 * Facets example
 */
$app->get('/facet_field', function () use ($app, $client) {
    $data = array();
    $query = $client->createSelect();
    $facetSet = $query->getFacetSet();
    $facetSet->createFacetField('stock')->setField('inStock');
    $facetSet->createFacetField('category')->setField('cat');

    $data['resultSet'] = $client->select($query);
    $data['facets'] = $data['resultSet']->getFacetSet()->getFacet('stock');

    $app->render('facet_field.html.twig', $data);
});

/**
 * Adding documents
 */
$app->get('/add', function () use ($app) {
    $app->render('add/form.html.twig');
});
$app->post('/add', function() use ($app, $client) {
    $updateQuery = $client->createUpdate();

    $document = $updateQuery->createDocument();
    $document->id = $app->request->post('field_id');
    $document->name = $app->request->post('field_name');
    $document->cat = explode(',', $app->request->post('field_categories'));
    $document->price = (float) $app->request->post('field_price');

    $updateQuery->addDocument($document);
    $updateQuery->addCommit();

    $client->update($updateQuery);

    $app->render('add/success.html.twig', array());
});

/**
 * Running the app
 */
$app->run();
