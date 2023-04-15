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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticleGeneratorController extends AbstractController
{
    private ArticleGeneratorService $articleGeneratorService;
    private UserRepository $userRepository;
    private Security $security;
    private GeneratedArticlesRepository $generatedArticlesRepository;

    public function __construct(ArticleGeneratorService $articleGeneratorService,
                                UserRepository $userRepository,
                                Security $security,
                                GeneratedArticlesRepository $generatedArticlesRepository,
                                private $targetDirectory)
    {
        $this->articleGeneratorService = $articleGeneratorService;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->generatedArticlesRepository = $generatedArticlesRepository;
    }

    #[Route('/dashboard/create_article', name: 'app_dashboard_create_article')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function input(Request $request, FileUploader $fileUploader)
    {
        $this->userRepository->checkSubscription(null); // 1
        $user = $this->security->getUser();

        $form = $this->createForm(ArticleCreateType::class);
        $form->handleRequest($request);

        if ($this->userRepository->checkDisabled2Hours(null) === false) {
            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile|null $imageFile */
                $imageFile = $form->get('images')->getData() ?? null;
                $imageLinks = $form->get('imageLink')->getData() ?? null;

                if ($imageFile) {
                    if (count($imageFile) > 1) {
                        foreach ($imageFile as $image) {
                            $imageFileName[] = $this->targetDirectory . $fileUploader->upload($image);
                        }
                    } elseif (count($imageFile) == 1) {
                        $imageFileName = $this->targetDirectory . $fileUploader->upload($imageFile[0]);
                    }
                } elseif ($imageLinks) {
                    $imageFileName = $this->prepareImageByLinks($imageLinks);
                } else {
                    $imageFileName = null;
                }

                $task = $form->getData();
                $article = $this->articleGeneratorService->generateArticle($user, $task, $imageFileName, true);

            }
        }

        return $this->render('dashboard/dashboard_create_article.html.twig', [
            'menuActive' => 'create_article',
            'articleCreateForm' => $form->createView(),
            'disabled' => $this->userRepository->checkDisabled2Hours(null) ?? null,
            'disabledFree' => $this->userRepository->checkDisabledFree() ?? null,
            'check2hours' => $this->generatedArticlesRepository->last2Hours($user) ?? null,
            'subscription' => $this->userRepository->checkSubscription(null),
            'keyword' => $article['keywords'] ?? null,
            'article' => $article['article'] ?? null,
        ]);
    }

    public function prepareImageByLinks($imageLinks)
    {
        $images = [];
        foreach (explode(',', $imageLinks) as $key => $image) {
            $image = trim($image);
            if ($this->checkMimeType($image) != false) {
                $images[$key] = trim($image);
            } else {
                continue;
            }
        }
        return $images ?? null;
    }

    public function checkMimeType($image) {
        {
            $mimes  = array(
                IMAGETYPE_GIF => "image/gif",
                IMAGETYPE_JPEG => "image/jpg",
                IMAGETYPE_PNG => "image/png",
                IMAGETYPE_WEBP => "image/webp");

            if (($image_type = exif_imagetype($image)) && (array_key_exists($image_type ,$mimes)))
            {
                return $mimes[$image_type];
            }
            else
            {
                return false;
            }
        }
    }

}
