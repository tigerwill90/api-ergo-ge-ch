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
 * @return \Ergo\Controllers\DownloadDocuments
 */
$container[\Ergo\Controllers\DownloadDocuments::class] = static function (ContainerInterface $c)  : \Ergo\Controllers\DownloadDocuments
{
    return new \Ergo\Controllers\DownloadDocuments($c->get('fileUtility'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DownloadImage
 */
$container[\Ergo\Controllers\DownloadImage::class] = static function (ContainerInterface $c)  : \Ergo\Controllers\DownloadImage
{
    return new \Ergo\Controllers\DownloadImage($c->get('fileUtility'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadEvents
 */
$container[\Ergo\Controllers\ReadEvents::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadEvents
{
    return new \Ergo\Controllers\ReadEvents($c->get('calendarClient'), $c->get('dataWrapper'), $c->get('serverTimingManager'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ListDocuments
 */
$container[\Ergo\Controllers\ListDocuments::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ListDocuments
{
    return new \Ergo\Controllers\ListDocuments($c->get('dataWrapper'), $c->get('fileUtility'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ListImages
 */
$container[\Ergo\Controllers\ListImages::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ListImages
{
    return new \Ergo\Controllers\ListImages($c->get('dataWrapper'), $c->get('fileUtility'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategory
 */
$container[\Ergo\Controllers\ReadCategory::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadCategory
{
    return new \Ergo\Controllers\ReadCategory($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategories$
 */
$container[\Ergo\Controllers\ReadCategories::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadCategories
{
    return new \Ergo\Controllers\ReadCategories($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadCategoriesOffice
 */
$container[\Ergo\Controllers\ReadCategoriesOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadCategoriesOffice
{
    return new \Ergo\Controllers\ReadCategoriesOffice($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadOffice
 */
$container[\Ergo\Controllers\ReadOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadOffice
{
    return new \Ergo\Controllers\ReadOffice($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadOffices
 */
$container[\Ergo\Controllers\ReadOffices::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadOffices
{
    return new \Ergo\Controllers\ReadOffices($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadTherapist
 */
$container[\Ergo\Controllers\ReadTherapist::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadTherapist
{
    return new \Ergo\Controllers\ReadTherapist($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadTherapistsOffice
 */
$container[\Ergo\Controllers\ReadTherapistsOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadTherapistsOffice
{
    return new \Ergo\Controllers\ReadTherapistsOffice($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\Authentication
 */
$container[\Ergo\Controllers\Authentication::class] = static function (ContainerInterface $c) : \Ergo\Controllers\Authentication
{
    return new \Ergo\Controllers\Authentication($c->get('usersDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\CreateUser
 */
$container[\Ergo\Controllers\CreateUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateUser
{
    return new \Ergo\Controllers\CreateUser($c->get('validationManager') ,$c->get('usersDao'), $c->get('officesDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\UpdateUser
 */
$container[\Ergo\Controllers\UpdateUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\UpdateUser
{
    return new \Ergo\Controllers\UpdateUser($c->get('validationManager') ,$c->get('usersDao'), $c->get('authenticationService'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DeleteUser
 */
$container[\Ergo\Controllers\DeleteUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\DeleteUser
{
    return new \Ergo\Controllers\DeleteUser($c->get('usersDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadUsers
 */
$container[\Ergo\Controllers\ReadUsers::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadUsers
{
    return new \Ergo\Controllers\ReadUsers($c->get('usersDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ReadUser
 */
$container[\Ergo\Controllers\ReadUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadUser
{
    return new \Ergo\Controllers\ReadUser($c->get('usersDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/** ----------------- DOMAINS ----------------- */

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\CategoriesDao
 */
$container['categoriesDao'] = static function (ContainerInterface $c) : \Ergo\domains\CategoriesDao
{
    return new \Ergo\domains\CategoriesDao($c->get('pdo'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\OfficesDao
 */
$container['officesDao'] = static function (ContainerInterface $c) : \Ergo\domains\OfficesDao
{
    return new \Ergo\domains\OfficesDao($c->get('pdo'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\domains\TherapistsDao
 */
$container['therapistsDao'] = static function (ContainerInterface $c) : \Ergo\domains\TherapistsDao
{
    return new \Ergo\domains\TherapistsDao($c->get('pdo'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Domains\UsersDao
 */
$container['usersDao'] = static function (ContainerInterface $c) : \Ergo\Domains\UsersDao
{
    return new \Ergo\Domains\UsersDao($c->get('pdo'), $c->get('appDebug'));
};

/** ----------------- SERVICES ----------------- */

/**
 * @return \Tigerwill90\ServerTiming\ServerTimingManager
 */
$container['serverTimingManager'] = static function () : \Tigerwill90\ServerTiming\ServerTimingManager
{
    return new \Tigerwill90\ServerTiming\ServerTimingManager();
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\CalendarClient
 */
$container['calendarClient'] = static function (ContainerInterface $c) : \Ergo\Services\CalendarClient
{
  return new \Ergo\Services\CalendarClient($c->get('appDebug'));
};

/**
 * @return \Ergo\Services\DataWrapper
 */
$container['dataWrapper'] = static function () : \Ergo\Services\DataWrapper
{
    return new \Ergo\Services\DataWrapper();
};

/**
 * @return \Ergo\Services\FileUtility
 */
$container['fileUtility'] = static function () : \Ergo\Services\FileUtility
{
    return new \Ergo\Services\FileUtility();
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\Auth
 */
$container['authenticationService'] = static function (ContainerInterface $c) : \Ergo\Services\Auth
{
    return new \Ergo\Services\Auth($c->get('usersDao'), $c->get('randomGenerator'), $c->get('appDebug'));
};

/**
 * @return \RandomLib\Generator
 */
$container['randomGenerator'] = static function () : \RandomLib\Generator
{
    $factory = new \RandomLib\Factory();
    return $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\Validators\ValidatorManagerInterface
 */
$container['validationManager'] = static function (ContainerInterface $c) : \Ergo\Services\Validators\ValidatorManagerInterface
{
    $validatorManager = new \Ergo\Services\Validators\ValidatorManager();
    return $validatorManager
        ->add('create_user', [$c->get('userCreateParameter')])
        ->add('update_user', [$c->get('userUpdateParameter')]);
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['userCreateParameter'] = static function () : \Ergo\Services\Validators\Validator
{
      $validator = new \Ergo\Services\Validators\ParameterValidator();
      return $validator
          ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(true))
          ->add('password', new \Ergo\Services\Validators\Rules\PasswordRule(true))
          ->add('roles', new \Ergo\Services\Validators\Rules\RolesRule(true))
          ->add('first_name', new \Ergo\Services\Validators\Rules\NameRule(true))
          ->add('last_name', new \Ergo\Services\Validators\Rules\NameRule(true))
          ->add('active', new \Ergo\Services\Validators\Rules\ActiveRule(true))
          ->add('offices_id', new \Ergo\Services\Validators\Rules\OfficesIdRule(false));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['userUpdateParameter'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(false))
        ->add('password', new \Ergo\Services\Validators\Rules\PasswordRule(false))
        ->add('roles', new \Ergo\Services\Validators\Rules\RolesRule(false))
        ->add('first_name', new \Ergo\Services\Validators\Rules\NameRule(false))
        ->add('last_name', new \Ergo\Services\Validators\Rules\NameRule(false))
        ->add('active', new \Ergo\Services\Validators\Rules\ActiveRule(false))
        ->add('offices_id', new \Ergo\Services\Validators\Rules\OfficesIdRule(false));
};

/**
 * @return \Monolog\Logger
 */
$container['appDebug'] = static function () : Monolog\Logger
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