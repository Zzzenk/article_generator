<?php

namespace App\Controller;

use App\Form\ArticleCreateType;
use App\Repository\UserRepository;
use App\Service\ArticleGeneratorService;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleGeneratorController extends AbstractController
{
    #[Route('/dashboard/create_article', name: 'app_dashboard_create_article')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function input(Request $request, ArticleGeneratorService $articleGeneratorService, SubscriptionService $subscriptionService, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $subscriptionService->checkSubscription($user);

        $form = $this->createForm(ArticleCreateType::class);
        $form->handleRequest($request);

        if ($subscriptionService->checkDisabled2Hours($user) === false) {
            if ($form->isSubmitted() && $form->isValid()) {
                $imageFileName = $articleGeneratorService->generateImagesPaths($form);

                $articleObject = $articleGeneratorService->generateArticle($user, $form->getData(), $imageFileName, true);

                $article = $articleObject->getArticle();
                $keyword = $articleObject->getKeywords();
            }
        }

        return $this->render('dashboard/dashboard_create_article.html.twig', [
            'menuActive' => 'create_article',
            'articleCreateForm' => $form->createView(),
            'disabled' => $subscriptionService->checkDisabled2Hours($user) ?? null,
            'disabledFree' => $subscriptionService->checkDisabledFree() ?? null,
            'check2hours' => $userRepository->last2Hours($user) ?? null,
            'subscription' => $subscriptionService->checkSubscription($user),
            'keyword' => $keyword ?? null,
            'article' => $article ?? null,
        ]);
    }
}
