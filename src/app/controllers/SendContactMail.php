<?php

namespace Ergo\Controllers;

use Ergo\Business\Error;
use Ergo\Services\DataWrapper;
use Ergo\Services\Validators\ValidatorManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class SendContactMail
{
    /** @var ValidatorManagerInterface  */
    private $validatorManager;

    /** @var PHPMailer  */
    private $mailer;

    /** @var DataWrapper  */
    private $dataWrapper;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(ValidatorManagerInterface $validatorManager, PHPMailer $mailer, DataWrapper $dataWrapper, LoggerInterface $logger = null)
    {
        $this->validatorManager = $validatorManager;
        $this->mailer = $mailer;
        $this->dataWrapper = $dataWrapper;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if ($this->validatorManager->validate(['contact_email'], $request)) {
            try {
                $this->mailer->setFrom(getenv('ADDRESS_FROM'));
                $this->mailer->addAddress('sylvain.muller90@gmail.com');
                $this->mailer->isHTML();
                $this->mailer->Subject = 'Here is the subject';
                $this->mailer->Body    = 'This is the HTML message body <b>in bold!</b>';
                $this->mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
                $this->mailer->send();
                return $response;
            } catch (\Exception $e) {
                return $this->dataWrapper
                    ->addEntity(new Error(Error::ERR_BAD_REQUEST, $e->getMessage()))
                    ->throwResponse($response, 400);
            }
        }
        return $this->dataWrapper
            ->addEntity(new Error(
                Error::ERR_BAD_REQUEST,
                'The request could not be understood by the server due to malformed syntax',
                $this->validatorManager->getErrorsMessages()
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
