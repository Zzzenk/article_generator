<?php

namespace App\Controller;


use App\Form\ArticleCreateType;
use App\Repository\GeneratedArticlesRepository;
use App\Repository\UserRepository;
use App\Service\ArticleGeneratorService;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleGeneratorController extends AbstractController
{
    public function __construct(
        private $targetDirectory
    ) {
    }

    #[Route('/dashboard/create_article', name: 'app_dashboard_create_article')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function input(Request $request, FileUploader $fileUploader, ArticleGeneratorService $articleGeneratorService, UserRepository $userRepository, Security $security, GeneratedArticlesRepository $generatedArticlesRepository): Response
    {
        $userRepository->checkSubscription(null);
        $user = $security->getUser();

        $form = $this->createForm(ArticleCreateType::class);
        $form->handleRequest($request);

        if ($userRepository->checkDisabled2Hours(null) === false) {
            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile|null $imageFile */
                $imageFile = $form->get('images')->getData() ?? null;
                $imageLinks = $form->get('imageLink')->getData() ?? null;

                if ($imageFile) {
                    foreach ($imageFile as $image) {
                        $imageFileName[] = $this->targetDirectory . $fileUploader->upload($image);
                    }
                }

                if ($imageLinks) {
                    $imageFileName = $articleGeneratorService->prepareImageByLinks($imageLinks);
                }

                $task = $form->getData();
                $article = $articleGeneratorService->generateArticle($user, $task, $imageFileName, true);
            }
        }

        return $this->render('dashboard/dashboard_create_article.html.twig', [
            'menuActive' => 'create_article',
            'articleCreateForm' => $form->createView(),
            'disabled' => $userRepository->checkDisabled2Hours(null) ?? null,
            'disabledFree' => $userRepository->checkDisabledFree() ?? null,
            'check2hours' => $generatedArticlesRepository->last2Hours($user) ?? null,
            'subscription' => $userRepository->checkSubscription(null),
            'keyword' => $article['keywords'] ?? null,
            'article' => $article['article'] ?? null,
        ]);
    }
}
