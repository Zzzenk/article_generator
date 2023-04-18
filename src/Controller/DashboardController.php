<?php

namespace App\Controller;

use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function dashboard(UserRepository $userRepository, Security $security, GeneratedArticlesRepository $generatedArticlesRepository): Response
    {
        $user = $security->getUser();
        $allArticles = $generatedArticlesRepository->findBy(['user' => $user->getId()]);
        $articlesThisMonth = $generatedArticlesRepository->lastCreatedArticles($user);

        if ($user->getSubscriptionExpiresAt() == null || $user->getSubscriptionExpiresAt() < (new \DateTime('3 days'))->format('Y.m.d H:i:s')) {
            $interval = '';
        } else {
            $interval = (new \DateTime('now'))->diff($user->getSubscriptionExpiresAt())->format('%a дн.');
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'menuActive' => 'dashboard',
            'subscription' => $userRepository->checkSubscription(null),
            'expiresIn' => $interval,
            'articlesThisMonth' => count($articlesThisMonth),
            'totalArticles' => count($allArticles),
            'latestArticle' => end($allArticles),
        ]);
    }

    #[Route('/dashboard/templates', name: 'app_dashboard_templates')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templates(Request $request, PaginatorInterface $paginator, Security $security, ModuleRepository $moduleRepository): Response
    {
        if (!$security->isGranted('ROLE_PRO')) {
            $disabled = 'disabled';
        }

        $user_id = $security->getUser()->getId();;
        $pagination = $paginator->paginate(
            $moduleRepository->findBy(['user' => $user_id]), /* query NOT result */
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
    public function templateAdd(Request $request, Security $security, ModuleRepository $moduleRepository): Response
    {
        $user_id = $security->getUser()->getId();;
        $moduleRepository->addTemplate($user_id, $request->query->get('title'), $request->query->get('code'));
        $this->addFlash('success', 'Шаблон успешно добавлен');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/dashboard/template/delete/{id}', name: 'app_dashboard_template_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function templateDelete(Request $request, Security $security, ModuleRepository $moduleRepository): Response
    {
        $user_id = $security->getUser()->getId();;
        $moduleRepository->deleteTemplate($request->attributes->get('id'), $user_id);
        $this->addFlash('success', 'Шаблон успешно удален');

        return $this->redirectToRoute('app_dashboard_templates');
    }

    #[Route('/dashboard/history', name: 'app_dashboard_history')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function history(PaginatorInterface $paginator, Request $request, Security $security, GeneratedArticlesRepository $generatedArticlesRepository): Response
    {
        $generatedArticles = $generatedArticlesRepository->findBy(['user' => $security->getUser()->getId()]);

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
    public function article_detail(Request $request, GeneratedArticlesRepository $generatedArticlesRepository): Response
    {
        $generatedArticle = $generatedArticlesRepository->findOneBy(['id' => $request->get('id')]);
        $repeatParams = explode(',', implode(' ', $generatedArticlesRepository->getArticleTemplate($request->get('id'))[0]));
        $repeatParams = str_replace('_', '=',implode('&', $repeatParams));

        return $this->render('dashboard/dashboard_article_detail.html.twig', [
            'menuActive' => 'article_detail',
            'keyword' => explode(',', $generatedArticle->getKeywords()),
            'article' => $generatedArticle->getArticle(),
            'repeatParams' => $repeatParams,
            ]);
    }
}