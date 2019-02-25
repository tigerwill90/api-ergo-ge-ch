<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 27.11.2018
 * Time: 21:59
 */
use Psr\Container\ContainerInterface;

$container = $app->getContainer();

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