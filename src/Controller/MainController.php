<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage()
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/try', name: 'app_try')]
    public function try()
    {
        return $this->render('try.html.twig');
    }
}