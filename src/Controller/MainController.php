<?php

namespace App\Controller;

use App\Repository\UnregisteredUsersRepository;
use App\Service\ArticleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    private UnregisteredUsersRepository $unregisteredUsersRepository;

    public function __construct(UnregisteredUsersRepository $unregisteredUsersRepository)
    {
        $this->unregisteredUsersRepository = $unregisteredUsersRepository;
    }

    #[Route('/', name: 'app_homepage')]
    public function homepage()
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $path = '/dashboard/create_article';
        } else {
            $path = '/try';
        }
        return $this->render('homepage.html.twig', [
            'path' => $path,
        ]);
    }

    #[Route('/try', name: 'app_try')]
    public function try(Request $request, ArticleGeneratorService $articleGeneratorService)
    {
        if ($request->request->get('title') != null) {
            $requestArray = [
                'theme' => $request->request->get('theme'),
                'title' => $request->request->get('title'),
                'word1' => $request->request->get('word1'),
            ];

            if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && $this->unregisteredUsersRepository->checkIP('192.168.1.3') === true) {
                $article = $articleGeneratorService->generateArticle(null, $requestArray, null, false);
                $this->unregisteredUsersRepository->addIP($request->getClientIp());
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