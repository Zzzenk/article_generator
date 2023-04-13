<?php

namespace App\Controller;

use App\Repository\ApiTokenRepository;
use App\Repository\ArticleContentRepository;
use App\Repository\UserRepository;
use App\Service\ArticleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiArticleGeneratorController extends AbstractController
{
    private ArticleGeneratorService $articleGeneratorService;
    private UserRepository $userRepository;
    private ArticleContentRepository $articleContentRepository;
    private ApiTokenRepository $apiTokenRepository;

    public function __construct(ArticleGeneratorService  $articleGeneratorService,
                                UserRepository           $userRepository,
                                ArticleContentRepository $articleContentRepository,
                                ApiTokenRepository $apiTokenRepository)
    {
        $this->articleGeneratorService = $articleGeneratorService;
        $this->userRepository = $userRepository;
        $this->articleContentRepository = $articleContentRepository;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    #[Route('/api/article', name: 'app_api_article_generator')]
    public function index(): JsonResponse
    {
        $token = 'e5010f8185e4582f404b49de3c760e51c735a42f';
        $user = $this->apiTokenRepository->findOneBy(['token' => $token])->getUser();
        $subscription = $this->userRepository->checkSubscription($token);
        $themeCode = 'tech';
        $theme = $this->articleContentRepository->findOneBy(['code' => $themeCode]);


        if ($theme === null) {
            $reply = $this->json([
                'error' => 'Такой тематики не существует',
            ]);
        } elseif ($this->userRepository->checkDisabled2Hours($token)) {
            $reply = $this->json([
                'error' => 'Вы уже сгенерировали 2 статьи за последние 2 часа. Оформите подписку PRO, чтобы снять ограничения',
            ]);
        } else {
            $requestArray = [
                "theme" => $theme->getTheme(),
                "title" => "Заголовок",
                "keyword0" => null,
                "keyword1" => null,
                "keyword2" => null,
                "keyword3" => null,
                "keyword4" => null,
                "keyword5" => null,
                "keyword6" => null,
                "sizeFromField" => null,
                "sizeToField" => null,
                "promotedWord1" => null,
                "promotedWord1Count" => null,
                "promotedWord2" => null,
                "promotedWord2Count" => null,
                "imageLink" => null,
            ];

            if ($subscription == 'FREE') {
                $requestArray['keyword1'] = null;
                $requestArray['keyword2'] = null;
                $requestArray['keyword3'] = null;
                $requestArray['keyword4'] = null;
                $requestArray['keyword5'] = null;
                $requestArray['keyword6'] = null;
                $requestArray['promotedWord2'] = null;
                $requestArray['promotedWord2Count'] = null;
                $requestArray['imageLink'] = null;
            }

            $generated = $this->articleGeneratorService->generateArticle($user, $requestArray, null, true);
            $article = $this->articleGeneratorService->prepareArticleForApi($generated);

            $reply = $this->json([
                'title' => $generated['title'],
                'article' => $article,
            ]);

        }

        return $reply;
    }
}
