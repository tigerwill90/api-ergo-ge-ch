<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 22:01
 */

$app->get('/events', \Ergo\Controllers\ReadEvents::class);

$app->group('/documents', function (\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ListDocuments::class);
    $app->get('/{name}', \Ergo\Controllers\DownloadDocuments::class);
});

$app->group('/images', function (\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ListImages::class);
    $app->get('/{name}', \Ergo\Controllers\DownloadImage::class);
});

$app->group('/categories', function(\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ReadCategories::class);
    $app->post('', \Ergo\Controllers\CreateCategory::class);
    $app->put('/{id:[0-9]+}', \Ergo\Controllers\UpdateCategory::class);
    $app->get('/{id:[0-9]+}', \Ergo\Controllers\ReadCategory::class);
    $app->delete('/{id:[0-9]+}', \Ergo\Controllers\DeleteCategory::class);
});

$app->group('/offices', function(\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ReadOffices::class);
    $app->post('', \Ergo\Controllers\CreateOffice::class);
    $app->delete('/{id:[0-9]+}', \Ergo\Controllers\DeleteOffice::class);
    $app->put('/{id:[0-9]+}', \Ergo\Controllers\UpdateOffice::class);
    $app->get('/{attribute}', \Ergo\Controllers\ReadOffice::class);
    $app->get('/{id:[0-9]+}/therapists', \Ergo\Controllers\ReadTherapistsOffice::class);
    $app->get('/{id:[0-9]+}/categories', \Ergo\Controllers\ReadCategoriesOffice::class);
});

$app->group('/therapists', function(\Slim\App $app) {
    $app->get('/{id:[0-9]+}', \Ergo\Controllers\ReadTherapist::class);
    $app->post('', \Ergo\Controllers\CreateTherapist::class);
    $app->put('/{id:[0-9]+}', \Ergo\Controllers\UpdateTherapist::class);
    $app->delete('/{id:[0-9]+}', \Ergo\Controllers\DeleteTherapist::class);
});

$app->get('/auth', \Ergo\Controllers\Authentication::class);

$app->group('/users', function (\Slim\App $app) {
    $app->get('', \Ergo\Controllers\ReadUsers::class);
    $app->get('/{attribute}', \Ergo\Controllers\ReadUser::class);
    $app->post('', \Ergo\Controllers\CreateUser::class);
    $app->patch('/{id:[0-9]+}', \Ergo\Controllers\UpdateUser::class);
    $app->delete('/{id:[0-9]+}', \Ergo\Controllers\DeleteUser::class);
});

$app->post('/emails/send', \Ergo\Controllers\SendContactMail::class);