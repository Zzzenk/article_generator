<?php

namespace App\Controller;

use App\Repository\ApiTokenRepository;
use App\Repository\ArticleContentRepository;
use App\Repository\UserRepository;
use App\Service\ArticleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiArticleGeneratorController extends AbstractController
{
    #[Route('/api/article', name: 'app_api_article_generator')]
    public function index(Request $request, ArticleGeneratorService $articleGeneratorService, UserRepository $userRepository, ArticleContentRepository $articleContentRepository, ApiTokenRepository $apiTokenRepository): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $token = substr($request->headers->get('Authorization'), 7);
        $theme = $articleContentRepository->findOneBy(['code' => $parameters['theme']]);

        if ($theme === null) {
            $reply = $this->json([
                'error' => 'Такой тематики не существует',
            ]);
        } elseif ($userRepository->checkDisabled2Hours($token)) {
            $reply = $this->json([
                'error' => 'Вы уже сгенерировали 2 статьи за последние 2 часа. Оформите подписку PRO, чтобы снять ограничения',
            ]);
        } elseif ($parameters['theme'] == null || $parameters['title'] == null) {
            $reply = $this->json([
                'error' => 'Отсутствуют обязательные параметры: theme, title',
            ]);
        } else {
            $requestArray = [
                "theme" => $parameters['theme'],
                "title" => $parameters['title'],
                "keyword0" => $parameters['keywords']['keyword0'],
                "keyword1" => $parameters['keywords']['keyword1'],
                "keyword2" => $parameters['keywords']['keyword2'],
                "keyword3" => $parameters['keywords']['keyword3'],
                "keyword4" => $parameters['keywords']['keyword4'],
                "keyword5" => $parameters['keywords']['keyword5'],
                "keyword6" => $parameters['keywords']['keyword6'],
                "sizeFrom" => $parameters['sizeFrom'] ?? null,
                "sizeTo" => $parameters['sizeTo'] ?? null,
                "word1" => $parameters['word1'] ?? null,
                "word1Count" => $parameters['word1Count'] ?? null,
                "word2" => $parameters['word2'] ?? null,
                "word2Count" => $parameters['word2Count'] ?? null,
                "images" => $parameters['images'] ?? null,
            ];

            if ($userRepository->checkSubscription($token) == 'FREE') {
                $parameters['keywords']['keyword1'] = null;
                $parameters['keywords']['keyword2'] = null;
                $parameters['keywords']['keyword3'] = null;
                $parameters['keywords']['keyword4'] = null;
                $parameters['keywords']['keyword5'] = null;
                $parameters['keywords']['keyword6'] = null;
                $requestArray['word2'] = null;
                $requestArray['word2Count'] = null;
                $requestArray['images'] = null;
            }

            $generated = $articleGeneratorService->generateArticle($apiTokenRepository->findOneBy(['token' => $token])->getUser(), $requestArray, $requestArray['images'], true);

            $reply = $this->json([
                'title' => $generated['title'],
                'article' => $generated['article'],
            ]);

        }

        return $reply;
    }
}
