<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 22:01
 */

$app->get('/independents', \Ergo\Controllers\ReadIndependents::class);
$app->get('/events', \Ergo\Controllers\ReadEvents::class);
$app->group('/documents', function (\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ListDocuments::class);
    $app->get('/{name}', \Ergo\Controllers\DownloadDocuments::class);
});
$app->group('/categories', function(\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ReadCategories::class);
    $app->get('/{id:[0-9]+}', \Ergo\Controllers\ReadCategory::class);
});
$app->group('/offices', function(\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ReadOffices::class);
    $app->get('/{attribute}', \Ergo\Controllers\ReadOffice::class);
    $app->get('/{id:[0-9]+}/therapists', \Ergo\Controllers\ReadTherapistsOffice::class);
    $app->get('/{id:[0-9]+}/categories', \Ergo\Controllers\ReadCategoriesOffice::class);
});
$app->group('/therapists', function(\Slim\App $app) {
    $app->get('/{id:[0-9]+}', \Ergo\Controllers\ReadTherapist::class);
});