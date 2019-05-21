<?php
require __DIR__ . '/../vendor/autoload.php';

$envLoader = new \Dotenv\Dotenv(__DIR__ . '/../');
$envLoader->load();
$envLoader->required('TIMEZONE')->notEmpty();
$envLoader->required('API_KEY_DIR')->notEmpty();
$envLoader->required('GOOGLE_APPLICATION_CREDENTIALS')->notEmpty();
$envLoader->required('CALENDAR_ID')->notEmpty();
$envLoader->required('DEBUG')->notEmpty()->isBoolean();
$envLoader->required('ORIGIN')->notEmpty();
$envLoader->required('API_VERSION')->notEmpty();
$envLoader->required('DB_NAME')->notEmpty();
$envLoader->required('DB_USER')->notEmpty();
$envLoader->required('DB_PASSWORD')->notEmpty();
$envLoader->required('DB_HOST')->notEmpty();
$envLoader->required('API_SECRET')->notEmpty();
$envLoader->required('TOKEN_EXPIRATION')->isInteger()->notEmpty();
$envLoader->required('SMTP_SERVER')->notEmpty();
$envLoader->required('SMTP_USER')->notEmpty();
$envLoader->required('SMTP_PASSWORD')->notEmpty();
$envLoader->required('SMTP_PORT')->isInteger()->notEmpty();
$envLoader->required('ADDRESS_FROM')->notEmpty();
$envLoader->required('RECAPTCHA_SECRET')->notEmpty();
$envLoader->required('SCHEME')->notEmpty()->allowedValues(['http', 'https']);
$envLoader->required('DOMAIN_NAME')->notEmpty();
$envLoader->required('FQDN')->notEmpty();
$envLoader->required('COOKIE_EXPIRATION')->notEmpty()->isInteger();
$envLoader->required('COOKIE_NAME')->notEmpty();
date_default_timezone_set(getenv('TIMEZONE'));

$app = new \Slim\App([
   'settings' => [
       'displayErrorDetails' => filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
   ]
]);

require __DIR__ . '/config/dependencies.php';
require __DIR__ . '/config/errorsHandler.php';
require __DIR__ . '/config/middleware.php';
require __DIR__ . '/routes/public.php';

$app->run();