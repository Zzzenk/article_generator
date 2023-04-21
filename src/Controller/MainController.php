<?php

namespace App\Controller;

use App\Repository\UnregisteredUsersRepository;
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
    public function try(Request $request, ArticleGeneratorService $articleGeneratorService, UnregisteredUsersService $unregisteredUsersService, UnregisteredUsersRepository $unregisteredUsersRepository): Response
    {
        if ($request->request->get('title') != null) {
            $requestArray = [
                'theme' => $request->request->get('theme'),
                'title' => $request->request->get('title'),
                'word1' => $request->request->get('word1'),
                'sizeFrom' => 2,
            ];

            if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && $unregisteredUsersRepository->checkIP($request->getClientIp()) === true) {
                $articleObject = $articleGeneratorService->generateArticle(null, $requestArray, [], false);
                $article = $articleObject->getArticle();

                $unregisteredUsersService->addIP($request->getClientIp());
            } elseif ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                $articleObject = $articleGeneratorService->generateArticle(null, $requestArray, [], false);
                $article = $articleObject->getArticle();
            } else {
                $disabled = true;
            }
        }

        return $this->render('try.html.twig', [
            'title' => $requestArray['title'] ?? null,
            'article' => $article ?? null,
            'disabled' => $disabled ?? false,
        ]);
    }
}
