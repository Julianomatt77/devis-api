<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    /**
     * @param $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail($to, $subject, $message, $returnMessage): Response
    {
        $email = (new Email())
            ->from('contact@martin-julien-dev.fr')
            ->to($to)
            ->subject($subject)
            ->html($message);

        try {
            $this->mailer->send($email);
            return new JsonResponse(['message' => $returnMessage], 200, []);
        } catch (\Exception $e){
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}