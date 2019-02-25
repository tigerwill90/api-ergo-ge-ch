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