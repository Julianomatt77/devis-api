<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route(path: '/api/contact', name: 'send_contact_form')]
    public function contactMe(Request $request, EmailService $emailService): Response
    {
        $content = json_decode($request->getContent(), true);

        $from = $content['from'];
        $subject = $content['subject'];
        $to = 'contact@martin-julien-dev.fr';
        $message = '<h1>Envoyé depuis: '. $from.'</h1><p> '. $content['message'] .'</p>';


        $response = $emailService->sendEmail($to, $subject, $message, 'message envoyé');

        if ($response->getStatusCode() != 200) {
            return new JsonResponse(['error' => 'message non envoyé'], 500);
        }

        return new Response($response->getContent(),  200, []);
    }
}
