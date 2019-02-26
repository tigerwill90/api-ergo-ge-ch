<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 21:59
 */
use Psr\Container\ContainerInterface;

$container = $app->getContainer();

/** ----------------- CONTROLLERS ----------------- */

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadIndependents
 */
$container[Ergo\Controllers\ReadIndependents::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadIndependents
{
   return new Ergo\Controllers\ReadIndependents($c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DownloadDocuments
 */
$container[\Ergo\Controllers\DownloadDocuments::class] = function (ContainerInterface $c)  : \Ergo\Controllers\DownloadDocuments
{
    return new \Ergo\Controllers\DownloadDocuments($c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadEvents
 */
$container[\Ergo\Controllers\ReadEvents::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadEvents
{
    return new \Ergo\Controllers\ReadEvents($c->get('calendarClient'), $c->get('dataWrapper'), $c->get('serverTimingManager'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ListDocuments
 */
$container[\Ergo\Controllers\ListDocuments::class] = function (ContainerInterface $c) : \Ergo\Controllers\ListDocuments
{
    return new \Ergo\Controllers\ListDocuments($c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategory
 */
$container[\Ergo\Controllers\ReadCategory::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadCategory
{
    return new \Ergo\Controllers\ReadCategory($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategories$
 */
$container[\Ergo\Controllers\ReadCategories::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadCategories
{
    return new \Ergo\Controllers\ReadCategories($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategoriesOffice
 */
$container[\Ergo\Controllers\ReadCategoriesOffice::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadCategoriesOffice
{
    return new \Ergo\Controllers\ReadCategoriesOffice($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadOffice
 */
$container[\Ergo\Controllers\ReadOffice::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadOffice
{
    return new \Ergo\Controllers\ReadOffice($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadOffices
 */
$container[\Ergo\Controllers\ReadOffices::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadOffices
{
    return new \Ergo\Controllers\ReadOffices($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadTherapist
 */
$container[\Ergo\Controllers\ReadTherapist::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadTherapist
{
    return new \Ergo\Controllers\ReadTherapist($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadTherapistsOffice
 */
$container[\Ergo\Controllers\ReadTherapistsOffice::class] = function (ContainerInterface $c) : \Ergo\Controllers\ReadTherapistsOffice
{
    return new \Ergo\Controllers\ReadTherapistsOffice($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/** ----------------- DOMAINS ----------------- */

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\CategoriesDao
 */
$container['categoriesDao'] = function (ContainerInterface $c) : \Ergo\domains\CategoriesDao
{
    return new \Ergo\domains\CategoriesDao($c->get('pdo'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\OfficesDao
 */
$container['officesDao'] = function (ContainerInterface $c) : \Ergo\domains\OfficesDao
{
    return new \Ergo\domains\OfficesDao($c->get('pdo'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\TherapistsDao
 */
$container['therapistsDao'] = function (ContainerInterface $c) : \Ergo\domains\TherapistsDao
{
    return new \Ergo\domains\TherapistsDao($c->get('pdo'), $c->get('appDebug'));
};

/** ----------------- SERVICES ----------------- */

/**
 * @return \Tigerwill90\ServerTiming\ServerTimingManager
 */
$container['serverTimingManager'] = function () : \Tigerwill90\ServerTiming\ServerTimingManager
{
    return new \Tigerwill90\ServerTiming\ServerTimingManager();
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\CalendarClient
 */
$container['calendarClient'] = function (ContainerInterface $c) : \Ergo\Services\CalendarClient
{
  return new \Ergo\Services\CalendarClient($c->get('appDebug'));
};

/**
 * @return \Ergo\Services\DataWrapper
 */
$container['dataWrapper'] = function () : \Ergo\Services\DataWrapper
{
    return new \Ergo\Services\DataWrapper();
};

/**
 * @return \Monolog\Logger
 */
$container['appDebug'] = function () : Monolog\Logger
{
    $log = new \Monolog\Logger('ergo_debug');
    $formatter = new \Monolog\Formatter\LineFormatter(
        "[%datetime%] [%level_name%]: %message% %context%\n",
        null,
        true,
        true
    );
    $stream = new \Monolog\Handler\StreamHandler(__DIR__ . '/../../logs/app.log', \Monolog\Logger::DEBUG);
    $stream->setFormatter($formatter);
    $log->pushHandler($stream);
    return $log;
};

/**
 * @return PDO
 */
$container['pdo'] = function () : PDO
{
    $pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';' . 'dbname=' . getenv('DB_NAME') . ';charset=utf8', getenv('DB_USER'), getenv('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
};