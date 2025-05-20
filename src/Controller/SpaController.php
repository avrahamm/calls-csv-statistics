<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->redirectToRoute('app');
    }

    #[Route('/app/{reactRouting}', name: 'app', defaults: ['reactRouting' => null], requirements: ['reactRouting' => '.+'])]
    #[Route('/statistics', name: 'statistics')]
    #[Route('/upload_calls', name: 'upload_calls')]
    public function index(): Response
    {
        return $this->render('spa/index.html.twig');
    }
}
