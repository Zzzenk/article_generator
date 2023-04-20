<?php

namespace App\Controller;

use App\Service\ArticleGeneratorService;
use App\Service\UnregisteredUsersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        $path = '/try';
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $path = '/dashboard/create_article';
        }
        
        return $this->render('homepage.html.twig', [
            'path' => $path,
        ]);
    }

    #[Route('/try', name: 'app_try')]
    public function try(Request $request, ArticleGeneratorService $articleGeneratorService, UnregisteredUsersService $unregisteredUsersService): Response
    {
        if ($request->request->get('title') != null) {
            $requestArray = [
                'theme' => $request->request->get('theme'),
                'title' => $request->request->get('title'),
                'word1' => $request->request->get('word1'),
            ];

            if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && $unregisteredUsersService->checkIP($request->getClientIp()) === true) {
                $article = $articleGeneratorService->generateArticle(null, $requestArray, null, false);
                $unregisteredUsersService->addIP($request->getClientIp());
            } elseif ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                $article = $articleGeneratorService->generateArticle(null, $requestArray, null, false);
            } else {
                $disabled = 'disabled';
            }
        }

        return $this->render('try.html.twig', [
            'title' => $requestArray['title'] ?? null,
            'article' => $article['article'] ?? null,
            'disabled' => $disabled ?? null,
        ]);
    }
}