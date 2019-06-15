<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\User;
use Ergo\Domains\OfficesDao;
use Ergo\Domains\UsersDao;
use Ergo\Exceptions\NoEntityException;
use Ergo\Exceptions\UniqueException;
use Ergo\Services\Auth;
use Ergo\Services\DataWrapper;
use Ergo\Services\Mailer;
use Ergo\Services\Validators\ValidatorManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class CreateUser
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var UsersDao  */
    private $usersDao;

    /** @var OfficesDao  */
    private $officesDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $auth;

    /** @var Mailer  */
    private $mailer;

    /** @var LoggerInterface  */
    private $logger;

    private const COOKIE_LENGTH = 100;

    private const RANDOM_PASSWORD_LENGTH = 25;

    private const TIMEOUT = 5;

    public function __construct(ValidatorManagerInterface $validatorManager , UsersDao $usersDao, OfficesDao $officesDao, DataWrapper $dataWrapper, Auth $auth, Mailer $mailer, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->officesDao = $officesDao;
        $this->dataWrapper = $dataWrapper;
        $this->auth = $auth;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $scopes = explode(' ', $request->getAttribute('token')['scope']);
        if (!in_array('admin', $scopes, true)) {
            return $this->dataWrapper
                ->addEntity(new Error(
                    Error::ERR_FORBIDDEN, 'Insufficient privileges to create a new user',
                    [],
                    'Action impossible, vous n\'avez pas les privilèges requis'
                    ))
                ->addMeta()
                ->throwResponse($response, 403);
        }

        if ($this->validatorManager->validate(['create_user'], $request)) {

            $cookieValue = $this->auth->generateUniqueCookieValue(self::TIMEOUT, self::COOKIE_LENGTH);

            // 3 days JWT
            $exp = time() + 259200;
            $resetJwt = $this->auth->generateUniqueResetJwt(self::TIMEOUT, $exp);

            $params = $request->getParsedBody();
            $data['email'] = $params['email'];
            $data['hashedPassword'] = $this->auth->hashPassword($this->auth->generateRandomValue(self::RANDOM_PASSWORD_LENGTH));
            $data['roles'] = implode(' ', $params['roles']);
            $data['firstname'] = $params['first_name'];
            $data['lastname'] = $params['last_name'];
            $data['active'] = false;
            $data['cookieValue'] = $cookieValue;
            $data['resetJwt'] = $resetJwt;
            $officesId = array_unique((array) $params['offices_id'], SORT_REGULAR);
            $user = new User($data, $officesId);

            if (!empty($officesId)) {
                try {
                    $officesName = $this->officesDao->getOfficeNameByOfficesId($officesId);
                    $user->setOfficesName($officesName);
                } catch (NoEntityException $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_NOT_FOUND, $e->getMessage(),
                             [],
                            'Impossible de créer cet utilisateur, le/les cabinet/s n\'existent pas'
                        ))
                        ->addMeta()
                        ->throwResponse($response, 404);
                }
            }

            try {
                $this->usersDao->createUser($user);
            } catch (UniqueException $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_CONFLICT,
                        'This user already exist',
                        [],
                        'Impossible de créer cet utilisateur, l\'adresse email existe déjà'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 409);
            }

            $send = true;
            try {
                $this->mailer->sendEmail($this->generateTemplate($user, $exp), 'Bienvenue sur la plateforme ASE', array($user->getEmail()));
            } catch (\Exception $e) {
                $this->log($e->getMessage());
                $send = false;
            }

            $userArray = $user->getEntity();
            $userArray['email_status'] = $send;

            return $this->dataWrapper
                ->addArray($userArray)
                ->addMeta()
                ->throwResponse($response, 201);
        }

        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages(),
                'Une erreur de validation est survenu'
            ))
            ->addMeta()
            ->throwResponse($response, 400);
    }

    /**
     * @param User $user
     * @param int $expiration
     * @return string
     * @throws \Exception
     */
    public function generateTemplate(User $user, int $expiration) : string
    {
        $htmlTemplate = '
                            <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed&display=swap" rel="stylesheet">
                            <div style="padding: 20px 10px 20px 10px; font-family: \'Roboto Condensed\', sans-serif;">
                                <img src="%s" alt="ase" style="width: 50px">
                                <h3>Création de votre compte ASE</h3>
                                <h4>
                                    %s %s, bienvenue sur la nouvelle plateforme de l\'association Suisse des ergothérapeutes - Section Genevoise !
                                </h4>
                                <p>
                                    Un administrateur viens de créer votre compte. <b>Pour finaliser votre inscription</b>, vous devez vous rendre sur la plateforme ASE
                                    et créer un nouveau mot de passe.
                                </p>
                                <a href="%s" style="text-decoration: none;">Suivez ce lien pour finaliser votre inscription</a>
                                <p>
                                    Pour des raisons de sécurité, le lien ci-dessus est actif jusqu\'à la date suivante : <b>%s</b>. Passez ce délais, vous ne
                                    pourrez plus activer votre compte. 
                                    Si vous avez dépassé la délais de validité, vous pouvez toujours <a href="%s" style="text-decoration: none;">faire une demande de réactivation</a> via notre formulaire de contact.
                                </p>
                                <span>Avec nos meilleurs salutation.</span>
                                <br>
                                <span>Le conseil d\'administration de l\'ASE.</span>
                            </div>
                        ';

        $date = new \DateTime();
        $date->setTimestamp($expiration);
        $sanitizedTemplate = sprintf(
            $htmlTemplate,
            getenv('FQDN') . '/images/ase',
            htmlspecialchars(ucfirst($user->getFirstname())),
            htmlspecialchars(ucfirst($user->getLastname())),
            getenv('FRONTEND_FQDN') . '/activate?token=' . $user->getResetJwt(),
            $date->format('d.m.Y H:i:s'),
            getenv('FRONTEND_FQDN') . '/contact?subject_id=1'
        );

        return $sanitizedTemplate;
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []) : void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message, $context);
        }
    }
}
