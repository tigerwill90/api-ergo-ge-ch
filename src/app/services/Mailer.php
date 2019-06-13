<?php

namespace Ergo\Services;

use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

class Mailer
{
    /** @var PHPMailer  */
    private $mailer;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(PHPMailer $mailer, LoggerInterface $logger = null)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * @param string $sanitizedTemplate
     * @param string $subject
     * @param string[] $recipients
     * @throws \Exception
     */
    public function sendEmail(string $sanitizedTemplate, string $subject, array $recipients) : void
    {
        try {
            $this->mailer->setFrom(getenv('ADDRESS_FROM'));
            foreach ($recipients as $recipient) {
                $this->mailer->addAddress($recipient);
            }
            $this->mailer->isHTML();
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Subject = htmlspecialchars($subject);
            $this->mailer->Body = $sanitizedTemplate;
            $this->mailer->send();
        } catch (\Exception $e) {
            throw $e;
        }
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