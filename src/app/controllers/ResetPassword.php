<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Business\User;
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

final class ResetPassword
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var UsersDao  */
    private $usersDao;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var Auth  */
    private $auth;

    /** @var Mailer  */
    private $mailer;

    /** @var LoggerInterface  */
    private $logger;

    private const TIMEOUT = 5;

    private const HOUR_IN_SECONDS = 3600;

    public function __construct(ValidatorManagerInterface $validatorManager , UsersDao $usersDao, DataWrapper $dataWrapper, Auth $auth, Mailer $mailer, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->usersDao = $usersDao;
        $this->dataWrapper = $dataWrapper;
        $this->auth = $auth;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['reset_password'], $request)) {
            $email = $request->getParsedBody()['email'];

            try {
                $user = $this->usersDao->getUser($email);
                if ($user->getActive()) {
                    $exp = time() + self::HOUR_IN_SECONDS;
                    $resetJwt = $this->auth->generateUniqueResetJwt(self::TIMEOUT, $exp);
                    $user->setResetJwt($resetJwt);
                    try {
                        $this->usersDao->updateUser($user);
                    } catch (UniqueException $e) {
                        return $this->dataWrapper
                            ->addEntity(new Error(
                                Error::ERR_CONFLICT,
                                'An unexpected conflict occurred while updating the database',
                                [],
                                'Désolé, un conflit inopiné est survenu lors de la mise à jour de la base de données'
                            ))
                            ->addMeta()
                            ->throwResponse($response, 409);
                    }
                    try {
                        $this->mailer->sendEmail($this->generateTemplate($user), 'Réinitialisation de votre mot de passe', array($user->getEmail()));
                        $this->log('reset password email sended for email : ' . $email . ', ip : ' . $this->getUserIP());
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }
                } else {
                    $this->log('reset password attempt for an inactive email : ' . $email . ', ip : ' . $this->getUserIP());
                }
            } catch (NoEntityException $e) {
                $this->log('reset password attempt with unknown email : ' . $email . ', ip : ' . $this->getUserIP());
            }

            return $this->dataWrapper
                ->addArray(['message' => 'if this email address exist and is active, the reset password instruction has been sent'])
                ->addMeta()
                ->throwResponse($response);
        }

        $this->log('reset password attempt with missing or invalid email. Ip : ' . $this->getUserIP());

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

    public function generateTemplate(User $user) : string
    {
        $htmlTemplate = '
                            <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed&display=swap" rel="stylesheet">
                            <div style="padding: 20px 10px 20px 10px; font-family: \'Roboto Condensed\', sans-serif;">
                                <img src="%s" alt="ase" style="width: 50px">
                                <h3>Instruction pour réinitialiser votre mot de passe</h3>
                                <span>Bonjour %s %s</span>
                                <p>
                                    Suivez le lien ci-dessous pour réinitialiser votre mot de passe. Pour des raisons de sécurité, <b>le lien n\'est valide que pour une durée très limitée</b>. 
                                    N\'attendez pas !
                                </p>
                                <a href="%s" style="text-decoration: none;">Réinitialiser mon mot de passe</a>
                                <p>
                                    Evidemment, <b>si vous n\'êtes pas à l\'origine de cette demande</b>, nous vous prions de <a href="%s" style="text-decoration: none;">contacter</a> le comité de la 
                                    section genevoise de l’ASE dans les meilleurs délais <b>et de ne pas réinitialiser le mot de passe</b>. Merci d’avance.
                                </p>
                                <span>Avec nos meilleures salutations.</span>
                                <br>
                                <span>Le comité de la section genevoise de l’Association Suisse des Ergothérapeutes.</span>
                            </div>
                        ';

        $sanitizedTemplate = sprintf(
            $htmlTemplate,
            getenv('FQDN') . '/images/ase',
            ucfirst($user->getFirstname()),
            ucfirst($user->getLastname()),
            getenv('FRONTEND_FQDN') . '/reset?token=' . $user->getResetJwt(),
            getenv('FRONTEND_FQDN') . '/contact?subject=Alerte concernant la réinitialisation du mot de passe de mon compte',
        );

        return $sanitizedTemplate;
    }

    public function getUserIP() : string
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        }
        elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        }
        else {
            $ip = $remote;
        }

        return $ip;
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

