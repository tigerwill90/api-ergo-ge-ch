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
    return new \Ergo\Controllers\ReadEvents($c->get('calendarClient'), $c->get('dataWrapper'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\CreateCategory
 */
$container[\Ergo\Controllers\CreateCategory::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateCategory
{
    return new \Ergo\Controllers\CreateCategory($c->get('validationManager'), $c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\UpdateCategory
 */
$container[\Ergo\Controllers\UpdateCategory::class] = static function (ContainerInterface $c) : \Ergo\Controllers\UpdateCategory
{
    return new \Ergo\Controllers\UpdateCategory($c->get('validationManager'), $c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DeleteCategory
 */
$container[\Ergo\Controllers\DeleteCategory::class] = static function (ContainerInterface $c) : \Ergo\Controllers\DeleteCategory
{
    return new \Ergo\Controllers\DeleteCategory($c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\ReadUsersOffices
 */
$container[\Ergo\Controllers\ReadUsersOffices::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadUsersOffices
{
    return new \Ergo\Controllers\ReadUsersOffices($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\CreateOffice
 */
$container[\Ergo\Controllers\CreateOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateOffice
{
    return new \Ergo\Controllers\CreateOffice($c->get('validationManager'), $c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\UpdateOffice
 */
$container[\Ergo\Controllers\UpdateOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\UpdateOffice
{
    return new \Ergo\Controllers\UpdateOffice($c->get('validationManager'), $c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DeleteOffice
 */
$container[\Ergo\Controllers\DeleteOffice::class] = static function (ContainerInterface $c) : \Ergo\Controllers\DeleteOffice
{
  return new \Ergo\Controllers\DeleteOffice($c->get('officesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\CreateTherapist
 */
$container[\Ergo\Controllers\CreateTherapist::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateTherapist
{
    return new \Ergo\Controllers\CreateTherapist($c->get('validationManager'), $c->get('therapistsDao'), $c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\UpdateTherapist
 */
$container[\Ergo\Controllers\UpdateTherapist::class] = static function (ContainerInterface $c) : \Ergo\Controllers\UpdateTherapist
{
    return new \Ergo\Controllers\UpdateTherapist($c->get('validationManager'), $c->get('therapistsDao'), $c->get('categoriesDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DeleteTherapist
 */
$container[\Ergo\Controllers\DeleteTherapist::class] = static function (ContainerInterface $c) : \Ergo\Controllers\DeleteTherapist
{
    return new \Ergo\Controllers\DeleteTherapist($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\ReadTherapists
 */
$container[\Ergo\Controllers\ReadTherapists::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ReadTherapists
{
    return new \Ergo\Controllers\ReadTherapists($c->get('therapistsDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\CreateEvent
 */
$container[\Ergo\Controllers\CreateEvent::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateEvent
{
    return new \Ergo\Controllers\CreateEvent($c->get('validationManager'), $c->get('eventsDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\Token
 */
$container[\Ergo\Controllers\Token::class] = static function (ContainerInterface $c) : \Ergo\Controllers\Token
{
    return new \Ergo\Controllers\Token($c->get('authenticationService'), $c->get('usersDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\RevokeUser
 */
$container[\Ergo\Controllers\RevokeUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\RevokeUser
{
    return new \Ergo\Controllers\RevokeUser( $c->get('usersDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\DisconnectUser
 */
$container[\Ergo\Controllers\DisconnectUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\DisconnectUser
{
    return new \Ergo\Controllers\DisconnectUser($c->get('authenticationService'), $c->get('usersDao'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\CreateUser
 */
$container[\Ergo\Controllers\CreateUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\CreateUser
{
    return new \Ergo\Controllers\CreateUser($c->get('validationManager') ,$c->get('usersDao'), $c->get('officesDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('mailer'), $c->get('appDebug'));
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
 * @return \Ergo\Controllers\ActivateUser
 */
$container[\Ergo\Controllers\ActivateUser::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ActivateUser
{
    return new \Ergo\Controllers\ActivateUser($c->get('validationManager') ,$c->get('usersDao'), $c->get('authenticationService'), $c->get('dataWrapper'), $c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\ResetPassword
 */
$container[\Ergo\Controllers\ResetPassword::class] = static function (ContainerInterface $c) : \Ergo\Controllers\ResetPassword
{
    return new \Ergo\Controllers\ResetPassword($c->get('validationManager') ,$c->get('usersDao'), $c->get('dataWrapper'), $c->get('authenticationService'), $c->get('mailer'), $c->get('appDebug'));
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

/**
 * @param ContainerInterface $c
 * @return \Ergo\Controllers\SendContactMail
 */
$container[\Ergo\Controllers\SendContactMail::class] = static function (ContainerInterface $c) : \Ergo\Controllers\SendContactMail
{
    return new \Ergo\Controllers\SendContactMail($c->get('validationManager'), $c->get('phpMailer'), $c->get('dataWrapper'), $c->get('reCaptcha'), $c->get('appDebug'));
};

/** ----------------- DOMAINS ----------------- */
/**
 * @param ContainerInterface $c
 * @return \Ergo\Domains\EventsDao
 */
$container['eventsDao'] = static function (ContainerInterface $c) : \Ergo\Domains\EventsDao
{
    return new \Ergo\Domains\EventsDao($c->get('pdo'), $c->get('appDebug'));
};

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
 * @param ContainerInterface $c
 * @return \Ergo\Services\CalendarClient
 */
$container['calendarClient'] = static function (ContainerInterface $c) : \Ergo\Services\CalendarClient
{
  return new \Ergo\Services\CalendarClient($c->get('appDebug'));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\DataWrapper
 */
$container['dataWrapper'] = static function (ContainerInterface $c) : \Ergo\Services\DataWrapper
{
    return new \Ergo\Services\DataWrapper($c->get('appDebug'));
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
        ->add('update_user', [$c->get('userUpdateParameter')])
        ->add('contact_email', [$c->get('contactSendMailParameter')])
        ->add('office', [$c->get('officeParameter')])
        ->add('therapist', [$c->get('therapistParameter')])
        ->add('category', [$c->get('categoryParameter')])
        ->add('update_password_token', [$c->get('updatePasswordTokenParameter')])
        ->add('reset_password', [$c->get('resetPassword')])
        ->add('create_event', [$c->get('eventParameter')]);
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['userCreateParameter'] = static function () : \Ergo\Services\Validators\Validator
{
      $validator = new \Ergo\Services\Validators\ParameterValidator();
      return $validator
          ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(true))
          ->add('roles', new \Ergo\Services\Validators\Rules\RolesRule(true))
          ->add('first_name', new \Ergo\Services\Validators\Rules\NameRule(true))
          ->add('last_name', new \Ergo\Services\Validators\Rules\NameRule(true))
          ->add('offices_id', new \Ergo\Services\Validators\Rules\IntArrayRule(false));
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
        ->add('active', new \Ergo\Services\Validators\Rules\BoolRule(false))
        ->add('offices_id', new \Ergo\Services\Validators\Rules\IntArrayRule(false));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['officeParameter'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('name', new \Ergo\Services\Validators\Rules\OfficeNameRule(true))
        ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(false))
        ->add('web', new \Ergo\Services\Validators\Rules\UrlRule(false))
        ->add('contacts', new \Ergo\Services\Validators\Rules\ContactsRule(true));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['therapistParameter'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('first_name', new \Ergo\Services\Validators\Rules\NameRule(true))
        ->add('last_name', new \Ergo\Services\Validators\Rules\NameRule(true))
        ->add('title', new \Ergo\Services\Validators\Rules\TitleRule(true))
        ->add('home', new \Ergo\Services\Validators\Rules\BoolRule(true))
        ->add('emails', new \Ergo\Services\Validators\Rules\EmailsRule(true))
        ->add('phones', new \Ergo\Services\Validators\Rules\PhonesRule(true))
        ->add('categories', new \Ergo\Services\Validators\Rules\NotEmptyIntArray(true))
        ->add('office_id', new \Ergo\Services\Validators\Rules\IdRule(true));
};

$container['categoryParameter'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('name', new \Ergo\Services\Validators\Rules\NameRule(true))
        ->add('description', new \Ergo\Services\Validators\Rules\DescriptionRule(false));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['contactSendMailParameter'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('name', new \Ergo\Services\Validators\Rules\NameRule(true))
        ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(true))
        ->add('subject', new \Ergo\Services\Validators\Rules\SubjectRule(true))
        ->add('message', new \Ergo\Services\Validators\Rules\MessageRule(true))
        ->add('token', new \Ergo\Services\Validators\Rules\TokenRule(true));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['eventParameter'] = static function() : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('title', new \Ergo\Services\Validators\Rules\EventTitleRule(true))
        ->add('subtitle', new \Ergo\Services\Validators\Rules\EventTitleRule(false))
        ->add('img_alt', new \Ergo\Services\Validators\Rules\AltRule(true))
        ->add('img_name', new \Ergo\Services\Validators\Rules\ImgNameRule(true))
        ->add('description', new \Ergo\Services\Validators\Rules\EventDescriptionRule(true))
        ->add('date', new \Ergo\Services\Validators\Rules\DateRule(false))
        ->add('url', new \Ergo\Services\Validators\Rules\UrlRule(false));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['updatePasswordTokenParameter'] = static function() : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('token', new \Ergo\Services\Validators\Rules\TokenRule(true))
        ->add('password', new \Ergo\Services\Validators\Rules\PasswordRule(true));
};

/**
 * @return \Ergo\Services\Validators\Validator
 */
$container['resetPassword'] = static function () : \Ergo\Services\Validators\Validator
{
    $validator = new \Ergo\Services\Validators\ParameterValidator();
    return $validator
        ->add('email', new \Ergo\Services\Validators\Rules\EmailRule(true));
};

/**
 * @param ContainerInterface $c
 * @return \Ergo\Services\Mailer
 */
$container['mailer'] = static function(ContainerInterface $c) : \Ergo\Services\Mailer
{
    return new \Ergo\Services\Mailer($c->get('phpMailer'), $c->get('appDebug'));
};

/**
 * @return \PHPMailer\PHPMailer\PHPMailer
 */
$container['phpMailer'] = static function () : \PHPMailer\PHPMailer\PHPMailer
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug    = 0;
    $mail->isSMTP();
    $mail->Host         = getenv('SMTP_SERVER');
    $mail->SMTPAuth     = true;
    $mail->Username     = getenv('SMTP_USER');
    $mail->Password     = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure   = 'tls';
    $mail->Port         = (int) getenv('SMTP_PORT');
    return $mail;
};

/**
 * @return \ReCaptcha\ReCaptcha
 */
$container['reCaptcha'] = static function() : \ReCaptcha\ReCaptcha
{
    return new \ReCaptcha\ReCaptcha(getenv('RECAPTCHA_SECRET'));
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