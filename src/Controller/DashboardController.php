<?php

namespace App\Controller;

use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;
    private ModuleRepository $moduleRepository;
    private GeneratedArticlesRepository $generatedArticlesRepository;

    public function __construct(UserRepository $userRepository,
                                Security $security,
                                ModuleRepository $moduleRepository,
                                GeneratedArticlesRepository $generatedArticlesRepository)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->moduleRepository = $moduleRepository;
        $this->generatedArticlesRepository = $generatedArticlesRepository;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard()
    {
        $user = $this->security->getUser();
        $allArticles = $this->generatedArticlesRepository->findBy(['user' => $user->getId()]);
        $articlesThisMonth = $this->generatedArticlesRepository->lastCreatedArticles($user);

        if ($user->getSubscriptionExpiresAt() == null || $user->getSubscriptionExpiresAt()->format('Y.m.d H:i:s') < (new \DateTime('3 days'))) {
            $interval = '';
        } else {
            $interval = (new \DateTime('now'))->diff($user->getSubscriptionExpiresAt())->format('%a дней');
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'menuActive' => 'dashboard',
            'subscription' => $this->userRepository->checkSubscription(null),
            'expiresIn' => $interval,
            'articlesThisMonth' => count($articlesThisMonth),
            'totalArticles' => count($allArticles),
            'latestArticle' => end($allArticles),
        ]);
    }

    #[Route('/dashboard/templates', name: 'app_dashboard_templates')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templates(Request $request, PaginatorInterface $paginator)
    {
        if (!$this->security->isGranted('ROLE_PRO')) {
            $disabled = 'disabled';
        }

        $user_id = $this->security->getUser()->getId();;
        $pagination = $paginator->paginate(
            $this->moduleRepository->findBy(['user' => $user_id]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('dashboard/dashboard_templates.html.twig', [
            'disabled' => $disabled ?? null,
            'menuActive' => 'modules',
            'pagination' => $pagination,
        ]);
    }

    #[Route('/dashboard/template/add', name: 'app_dashboard_template_add')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templateAdd(Request $request)
    {
        $user_id = $this->security->getUser()->getId();;
        $this->moduleRepository->addTemplate($user_id, $request->query->get('title'), $request->query->get('code'));
        $this->addFlash('success', 'Шаблон успешно добавлен');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/dashboard/template/delete/{id}', name: 'app_dashboard_template_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templateDelete(Request $request)
    {
        $user_id = $this->security->getUser()->getId();;
        $this->moduleRepository->deleteTemplate($request->attributes->get('id'), $user_id);
        $this->addFlash('success', 'Шаблон успешно удален');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/dashboard/history', name: 'app_dashboard_history')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function history(PaginatorInterface $paginator, Request $request)
    {
        $generatedArticles = $this->generatedArticlesRepository->findBy(['user' => $this->security->getUser()->getId()]);

        $pagination = $paginator->paginate(
            $generatedArticles, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('dashboard/dashboard_history.html.twig', [
            'menuActive' => 'history',
            'pagination' => $pagination,
            'generatedArticles' => $generatedArticles,
        ]);
    }


    #[Route('/dashboard/article_detail/{id}', name: 'app_dashboard_article_detail')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function article_detail(Request $request)
    {
        $article_id = $request->attributes->get('id');
        $generatedArticle = $this->generatedArticlesRepository->findOneBy(['id' => $article_id]);

        return $this->render('dashboard/dashboard_article_detail.html.twig', [
            'menuActive' => 'article_detail',
            'keyword' => explode(',', $generatedArticle->getKeywords()),
            'article' => $generatedArticle->getArticle(),
            ]);
    }
}