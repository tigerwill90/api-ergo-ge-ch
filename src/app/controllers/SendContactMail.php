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
                                    <h3>Sujet : %s</h3>
                                    <h4>Message : </h4>
                                    <p>%s</p>
                                    <span>Envoyé par : %s</span>
                                    <br>
                                    <span>Adresse email : %s</span>
                                ';

                try {
                    $this->mailer->setFrom(getenv('ADDRESS_FROM'));
                    $this->mailer->addAddress(getenv('ADDRESS_FROM'));
                    $this->mailer->isHTML();
                    $this->mailer->CharSet = 'UTF-8';
                    $this->mailer->Subject = $params['subject'];
                    $this->mailer->Body    = sprintf($htmlTemplate, htmlspecialchars($params['subject']), htmlspecialchars($params['message']), htmlspecialchars($params['name']), htmlspecialchars($params['email']));
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
                        ->throwResponse($response, 400);
                }

            } else {
                return $this->dataWrapper
                    ->addEntity(new Error(
                        Error::ERR_TOO_MANY_REQUEST, 'The service is blocked due to suspicious activity',
                        [],
                        'Le service est temporairement bloqué'
                    ))
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
            ->throwResponse($response, 400);
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
