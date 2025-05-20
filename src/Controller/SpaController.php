<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaController extends AbstractController
{
    #[Route('/app/{reactRouting}', name: 'app', defaults: ['reactRouting' => null], requirements: ['reactRouting' => '.+'])]
    public function index(): Response
    {
        return $this->render('spa/index.html.twig');
    }
}
