<?php

namespace App\Controller;

use App\DTO\ArticleDataDTO;
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
            $articleData = new ArticleDataDTO();
            $articleData->setTheme($formData['theme'] ?? '');
            $articleData->setTitle($formData['title'] ?? '');
            $articleData->setSizeFrom(2);
            $articleData->setWord1($formData['word1'] ?? '');

            if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && $unregisteredUsersRepository->checkIP($request->getClientIp()) === true) {
                $articleObject = $articleGeneratorService->generateArticle(null, $articleData, [], false);
                $article = $articleObject->getArticle();

                $unregisteredUsersService->addIP($request->getClientIp());
            } elseif ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                $articleObject = $articleGeneratorService->generateArticle(null, $articleData, [], false);
                $article = $articleObject->getArticle();
            } else {
                $disabled = true;
            }
        }

        return $this->render('try.html.twig', [
            'title' => $articleData->getTitle() ?? null,
            'article' => $article ?? null,
            'disabled' => $disabled ?? false,
        ]);
    }
}
