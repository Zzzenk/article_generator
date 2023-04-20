<?php

namespace App\Controller;

use App\Form\ArticleCreateType;
use App\Service\ArticleGeneratorService;
use App\Service\DashboardService;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleGeneratorController extends AbstractController
{
    #[Route('/dashboard/create_article', name: 'app_dashboard_create_article')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function input(Request $request, ArticleGeneratorService $articleGeneratorService, SubscriptionService $subscriptionService, DashboardService $dashboardService): Response
    {
        $user = $this->getUser();
        $subscriptionService->checkSubscription($user);

        $form = $this->createForm(ArticleCreateType::class);
        $form->handleRequest($request);

        if ($subscriptionService->checkDisabled2Hours($user) === false) {
            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile|null $imageFile */
                $imageFile = $form->get('images')->getData() ?? null;
                $imageLinks = $form->get('imageLink')->getData() ?? null;

                $imageFileName = $articleGeneratorService->imageHandler($imageFile, $imageLinks);

                $task = $form->getData();
                $article = $articleGeneratorService->generateArticle($user, $task, $imageFileName, true);
            }
        }

        return $this->render('dashboard/dashboard_create_article.html.twig', [
            'menuActive' => 'create_article',
            'articleCreateForm' => $form->createView(),
            'disabled' => $subscriptionService->checkDisabled2Hours($user) ?? null,
            'disabledFree' => $subscriptionService->checkDisabledFree() ?? null,
            'check2hours' => $dashboardService->last2Hours($user) ?? null,
            'subscription' => $subscriptionService->checkSubscription($user),
            'keyword' => $article['keywords'] ?? null,
            'article' => $article['article'] ?? null,
        ]);
    }
}
