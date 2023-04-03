<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{


    private UserRepository $userRepository;
    private Security $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard()
    {
        return $this->render('dashboard/dashboard.html.twig');
    }

    #[Route('/dashboard/subscription', name: 'app_dashboard_subscription')]
    public function subscription()
    {
        return $this->render('dashboard/dashboard_subscription.html.twig');
    }

    #[Route('/dashboard/profile', name: 'app_dashboard_profile')]
    public function profile()
    {
        $email = $this->security->getUser()->getUserIdentifier();
        $firstName = $this->userRepository->findOneBy(['email' => $email], [])->getFirstName();
        return $this->render('dashboard/dashboard_profile.html.twig', [
            'email' => $this->security->getUser()->getUserIdentifier(),
            'firstName' => $this->userRepository->findOneBy(['email' => $email], [])->getFirstName(),
        ]);
    }

    #[Route('/dashboard/modules', name: 'app_dashboard_modules')]
    public function modules()
    {
        return $this->render('dashboard/dashboard_modules.html.twig');
    }

    #[Route('/dashboard/history', name: 'app_dashboard_history')]
    public function history()
    {
        return $this->render('dashboard/dashboard_history.html.twig');
    }

    #[Route('/dashboard/create_article', name: 'app_dashboard_create_article')]
    public function create_article()
    {
        return $this->render('dashboard/dashboard_create_article.html.twig');
    }

    #[Route('/dashboard/article_detail', name: 'app_dashboard_article_detail')]
    public function article_detail()
    {
        return $this->render('dashboard/dashboard_article_detail.html.twig');
    }
}