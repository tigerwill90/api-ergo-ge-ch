<?php
require __DIR__ . '/../vendor/autoload.php';

$envLoader = new \Dotenv\Dotenv(__DIR__ . '/../');
$envLoader->load();
$envLoader->required('TIMEZONE')->notEmpty();
date_default_timezone_set(getenv('TIMEZONE'));

$app = new \Slim\App([
   'settings' => [
       'displayErrorDetails' => filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
   ]
]);

require __DIR__ . '/config/dependencies.php';
require __DIR__ . '/config/errorsHandler.php';
require __DIR__ . '/config/middlewares.php';
require __DIR__ . '/routes/public.php';

$app->run();