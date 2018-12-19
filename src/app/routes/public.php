<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 22:01
 */

$app->get('/independents', \Ergo\Controllers\ReadIndependents::class);
$app->get('/events', \Ergo\Controllers\ReadEvents::class);
$app->get('/documents/{name}', \Ergo\Controllers\DownloadDocuments::class);