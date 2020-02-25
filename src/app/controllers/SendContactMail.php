<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;

final class SendContactMail
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var PHPMailer  */
    private $mailer;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var ReCaptcha  */
    private $reCaptcha;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, PHPMailer $mailer, DataWrapper $dataWrapper, ReCaptcha $reCaptcha, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->mailer = $mailer;
        $this->dataWrapper = $dataWrapper;
        $this->reCaptcha = $reCaptcha;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['contact_email'], $request)) {
            $params = $request->getParsedBody();
            $resp = $this->reCaptcha->verify($params['token']);
            // Recaptcha verification, accept a bad score in dev mode
            $score = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 0 : 0.5;
            if ($resp->isSuccess() && $resp->getAction() === 'social' && $resp->getScore() > $score) {
                $htmlTemplate = '
                                    <span>Bonjour, cet email provient de la plateforme ASE !</span>
                                    <br>
                                    <h3>Sujet : %s</h3>
                                    <h3><u>Message</u> : </h3>
                                    <p style="word-wrap: break-word">%s</p>
                                    <span><b>Envoyé par :</b> %s</span>
                                    <br>
                                    <span><b>Adresse email :</b> %s</span>
                                    <br>
                                    <br>
                                    <span><b>Informations complémentaires :</b> </span>
                                    <br>
                                    <span><b>Adresse IP :</b> %s</span>
                                    <br>
                                    <span><b>Score de la requête :</b> %s</span>
                                ';
                // TODO use mailer service
                try {
                    $this->mailer->setFrom($params['email']);
                    $this->mailer->addAddress(getenv('ADDRESS_TO'));
                    $this->mailer->isHTML();
                    $this->mailer->CharSet = 'UTF-8';
                    $this->mailer->Subject = htmlspecialchars($params['subject']);
                    $this->mailer->Body = sprintf(
                        $htmlTemplate,
                        htmlspecialchars($params['subject']),
                        htmlspecialchars($params['message']),
                        htmlspecialchars($params['name']),
                        htmlspecialchars($params['email']),
                        $this->getUserIP(),
                        $resp->getScore()
                    );
                    $this->mailer->AltBody = $params['message'];
                    $this->mailer->send();
                    return $response;
                } catch (\Exception $e) {
                    return $this->dataWrapper
                        ->addEntity(new Error(
                            Error::ERR_BAD_REQUEST, $e->getMessage(),
                            [],
                            'Une erreur est survenue, l\'email n\'a pas été envoyé'
                        ))
                        ->addMeta()
                        ->throwResponse($response, 400);
                }

            } else {
                $this->log(print_r($resp, true));
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_TOO_MANY_REQUEST, 'The service is blocked due to suspicious activity',
                        [],
                        'Le service est temporairement bloqué'
                    ))
                    ->addMeta()
                    ->throwResponse($response, 429);
            }
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
     * @return string
     */
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
