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

function getDocumentDefault() {
    return [
                ['key' => 'id',         'label' => 'ID'],
                ['key' => 'name',       'label' => 'Name'],
                ['key' => 'cat',        'label' => 'Categories (Comma separated)'],
                ['key' => 'price',      'label' => 'Price'],
           ];
}

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
        $data['result']  = $result->getData();
    } catch (Solarium\Exception $e) {
        $data['success'] = false;
    }

    $app->render('ping.html.twig', $data);
});

/**
 * Simple search
 */
$app->get('/simple_search', function () use ($app, $client) {
    $data  = array();

    $query = $client->createSelect();

    $q  = $app->request->get('field_query');
    $s  = $app->request->get('field_sort');
    $st = $app->request->get('field_sort_type');

    $query->setQuery('*:*');
    if ($q) {
        $query->setQuery($q);
    }

    if ($s) {
        if (! $st OR $st == 'asc') {
            $query->addSort($s, $query::SORT_ASC);
            $st = 'asc';
        } else {
            $query->addSort($s, $query::SORT_DESC);
        }
    }
    $data['result'] = $client->execute($query);
    $data['q']  = $q;
    $data['s']  = $s;
    $data['st'] = $st;

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
    $data['facets']    = $data['resultSet']->getFacetSet()->getFacet('stock');

    $app->render('facet_field.html.twig', ['document' => $data]);
});

/**
 * Adding documents
 */
$app->get('/document', function () use ($app) {
    $app->render('document/form.html.twig', ['document' => getDocumentDefault()]);
});

$app->post('/document', function() use ($app, $client) {
    $updateQuery     = $client->createUpdate();

    $document        = $updateQuery->createDocument();

    $postDocument    = $app->request->post('fields');

    $document->id    = $postDocument['id'];
    $document->name  = $postDocument['name'];
    $document->cat   = explode(',', $postDocument['cat']);
    $document->price = (float) $postDocument['price'];

    $updateQuery->addDocument($document);
    $updateQuery->addCommit();

    $client->update($updateQuery);

    $app->render('document/success.html.twig', array());
});

$app->put('/document', function() use ($app, $client) {
    $updateQuery     = $client->createUpdate();

    $document        = $updateQuery->createDocument();
    $document->id    = $app->request->post('field_id');
    $document->name  = $app->request->post('field_name');
    $document->cat   = explode(',', $app->request->post('field_categories'));
    $document->price = (float) $app->request->post('field_price');

    $updateQuery->addDocument($document);
    $updateQuery->addCommit();

    $client->update($updateQuery);

    $app->render('document/success.html.twig', array());
});

/**
 * Running the app
 */
$app->run();
