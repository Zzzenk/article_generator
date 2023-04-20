<?php

namespace App\Controller;

use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use App\Service\DashboardService;
use App\Service\ModuleService;
use App\Service\SubscriptionService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(GeneratedArticlesRepository $generatedArticlesRepository, DashboardService $dashboardService, SubscriptionService $subscriptionService): Response
    {
        $user = $this->getUser();
        $allArticles = $generatedArticlesRepository->findBy(['user' => $user->getId()]);
        $articlesThisMonth = $dashboardService->lastCreatedArticles($user);

        if ($user->getSubscriptionExpiresAt() == null || $user->getSubscriptionExpiresAt() > (new \DateTime('+3 days'))) {
            $interval = '';
        } else {
            $interval = (new \DateTime('now'))->diff($user->getSubscriptionExpiresAt())->format('%a дней');
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'menuActive' => 'dashboard',
            'subscription' => $subscriptionService->checkSubscription($user),
            'expiresIn' => $interval,
            'articlesThisMonth' => count($articlesThisMonth),
            'totalArticles' => count($allArticles),
            'latestArticle' => end($allArticles),
        ]);
    }

    #[Route('/templates', name: 'app_dashboard_templates')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templates(Request $request, PaginatorInterface $paginator, ModuleRepository $moduleRepository): Response
    {
        $pagination = $paginator->paginate(
            $moduleRepository->findBy(['user' => $this->getUser()->getId()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('dashboard/dashboard_templates.html.twig', [
            'menuActive' => 'modules',
            'pagination' => $pagination,
        ]);
    }

    #[Route('/template/add', name: 'app_dashboard_template_add')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templateAdd(Request $request, ModuleService $moduleService): Response
    {
        $moduleService->addTemplate($this->getUser(), $request->query->get('title'), $request->query->get('code'));
        $this->addFlash('success', 'Шаблон успешно добавлен');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/template/delete/{id}', name: 'app_dashboard_template_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templateDelete(Request $request, ModuleService $moduleService): Response
    {
        $moduleService->deleteTemplate($request->attributes->get('id'));
        $this->addFlash('success', 'Шаблон успешно удален');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/history', name: 'app_dashboard_history')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function history(PaginatorInterface $paginator, Request $request, GeneratedArticlesRepository $generatedArticlesRepository): Response
    {
        $generatedArticles = $generatedArticlesRepository->findBy(['user' => $this->getUser()->getId()]);

        $pagination = $paginator->paginate(
            $generatedArticles,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('dashboard/dashboard_history.html.twig', [
            'menuActive' => 'history',
            'pagination' => $pagination,
            'generatedArticles' => $generatedArticles,
        ]);
    }


    #[Route('/article_detail/{id}', name: 'app_dashboard_article_detail')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function article_detail(Request $request, GeneratedArticlesRepository $generatedArticlesRepository, DashboardService $dashboardService): Response
    {
        $generatedArticle = $generatedArticlesRepository->findOneBy(['id' => $request->get('id')]);
        $repeatParams = explode(',', implode(' ', $dashboardService->getArticleTemplate($request->get('id'))[0]));
        $repeatParams = str_replace('_', '=',implode('&', $repeatParams));

        return $this->render('dashboard/dashboard_article_detail.html.twig', [
            'menuActive' => 'article_detail',
            'keyword' => explode(',', $generatedArticle->getKeywords()),
            'article' => $generatedArticle->getArticle(),
            'repeatParams' => $repeatParams,
            ]);
    }
}