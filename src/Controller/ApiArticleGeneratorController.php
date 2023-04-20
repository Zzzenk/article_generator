<?php

namespace App\Controller;

use App\Service\ArticleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiArticleGeneratorController extends AbstractController
{
    #[Route('/api/article', name: 'app_api_article_generator')]
    public function index(Request $request, ArticleGeneratorService $articleGeneratorService): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $token = substr($request->headers->get('Authorization'), 7);

        $generated = $articleGeneratorService->validateApiRequest($parameters, $token);

        if (isset($generated['error'])) {
            return $this->json([
                'error' => $generated['error'],
            ]);
        } else {
            return $this->json([
                'title' => $generated['title'],
                'article' => $generated['article'],
            ]);
        }


    }
}
