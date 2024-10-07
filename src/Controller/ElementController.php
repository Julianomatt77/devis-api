<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ElementController extends AbstractController
{
    #[Route('/element', name: 'app_element')]
    public function index(): Response
    {
        return $this->render('element/index.html.twig', [
            'controller_name' => 'ElementController',
        ]);
    }
}
