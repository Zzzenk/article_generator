<?php

namespace App\Service;

use App\Entity\ArticleContent;
use App\Entity\ArticleImages;
use App\Entity\GeneratedArticles;
use App\Entity\Module;
use App\Repository\ApiTokenRepository;
use App\Repository\ArticleContentRepository;
use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleGeneratorService
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly ArticleContentRepository $articleContentRepository,
        private readonly EntityManagerInterface $em,
        private readonly GeneratedArticlesRepository $generatedArticlesRepository,
        private readonly ModuleService $moduleService,
        private readonly ModuleRepository $moduleRepository,
        private readonly FileUploader $fileUploader,
        private readonly SubscriptionService $subscriptionService,
        private readonly ValidatorInterface $validator,
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
    }

    /**
     * @param UserInterface|null $user
     * @param array $requestArray
     * @param array|null $imageFileName
     * @param bool $insert
     * @return GeneratedArticles
     */
    public function generateArticle(UserInterface|null $user, array $requestArray, array|null $imageFileName, bool $insert): GeneratedArticles
    {
        $theme = $requestArray['theme'];
        $title = $requestArray['title'];
        $sizeFrom = $requestArray['sizeFrom'] ?? 1;
        $sizeTo = $requestArray['sizeTo'] ?? null;
        $word1 = $requestArray['word1'] ?? null;
        $word1Count = $requestArray['word1Count'] ?? 1;
        $word2 = $requestArray['word2'] ?? null;
        $word2Count = $requestArray['word2Count'] ?? null;

        $article = implode(PHP_EOL . PHP_EOL, $this->templateSelect($user, $sizeFrom, $sizeTo, $imageFileName));

        $keywords = [
            $requestArray['keyword0'] ?? null,
            $requestArray['keyword1'] ?? null,
            $requestArray['keyword2'] ?? null,
            $requestArray['keyword3'] ?? null,
            $requestArray['keyword4'] ?? null,
            $requestArray['keyword5'] ?? null,
            $requestArray['keyword6'] ?? null,
        ];

        $keywords = implode(',', $keywords);

        /** @var ArticleContent|null $allArticles */
        if ($theme != null) {
            $allArticles = $this->articleContentRepository->findBy(['code' => $theme]);
        } else {
            $allArticles = $this->articleContentRepository->findContentForDemoGeneration('300');
        }
        shuffle($allArticles);

        foreach ($allArticles as $paragraph) {
            if (str_contains($article, '{{ paragraph }}') === false && str_contains($article, '{{ paragraphs }}') === false) {
                break;
            }

            if (is_array($paragraph)) {
                $paragraph = $paragraph['body'];
            } else {
                $paragraph = $paragraph->getBody();
            }

            if ($word1) {
                $paragraph = explode(' ', $paragraph);
                $word1 = "<b>" . $word1 . "</b>";
                for ($i = 1; $i <= $word1Count; $i++) {
                    array_splice($paragraph, rand(1, count($paragraph)-1), 0, $word1);
                }
                $paragraph = implode(' ', $paragraph);
            }
            if ($word2) {
                $paragraph = explode(' ', $paragraph);
                $word2 = "<b>" . $word2 . "</b>";
                for ($i = 1; $i <= $word2Count; $i++) {
                    array_splice($paragraph, rand(1, count($paragraph)-1), 0, $word2);
                }
                $paragraph = implode(' ', $paragraph);
            }

            $article = preg_replace('{{{ paragraph }}}', $paragraph, $article, 1);
            $article = preg_replace('{{{ paragraphs }}}', $paragraph, $article, 1);
            $article = preg_replace('{{{ title }}}', $title, $article, 1);
        }

        if ($imageFileName) {
            shuffle($imageFileName);
            foreach ($imageFileName as $image) {
                if (str_contains($article, '{{ image }}') === false) {
                    break;
                }
                $article = preg_replace('{{{ image }}}', $image, $article, 1);
            }

            preg_match_all('{{{ image }}}', $article, $matches[]);

            if (str_contains($article, '{{ image }}') === true) {
                for ($i = count($matches); ; $i--) {
                    if ($i == 0) {
                        break;
                    }
                    $article = preg_replace('{{{ image }}}', $imageFileName[rand(0, count($imageFileName) -1)], $article);
                }
            }
        }

        if ($insert === true) {
            $this->addArticle($user, $title, $article, $requestArray, $imageFileName, $keywords);
        }

        $articleObject = new GeneratedArticles();
        return $articleObject
            ->setTitle($title)
            ->setArticle($article)
            ->setKeywords($keywords)
            ;
    }

    /**
     * @param $user
     * @param string $title
     * @param string $article
     * @param array $template
     * @param array $imageFileName
     * @param string $keywords
     * @return void
     */
    public function addArticle($user, string $title, string $article, array $template, array $imageFileName, string $keywords): void
    {
        foreach ($template as $key => $value) {
            $templateArray[] = $key . '_' . $value;
        }
        $template = implode(',', $templateArray);

        $newArticle = new GeneratedArticles();
        $newArticle
            ->setUser($user)
            ->setArticle($article)
            ->setTitle($title)
            ->setTemplate($template)
            ->setCreatedAt(new DateTime('now'))
            ->setKeywords(serialize($keywords))
        ;
        $this->em->persist($newArticle);
        $this->em->flush();

        $articleObject = $this->generatedArticlesRepository->findOneBy(['id' => $newArticle->getId()]);

        if ($imageFileName != null) {
            $this->addArticleImage($articleObject, $imageFileName);
        }
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    public function generateImagesPaths(FormInterface $form): array
    {
        $imageFile = $form->get('images')->getData() ?? null;
        $imageLinks = $form->get('imageLink')->getData() ?? null;

        $imageFileName = [];
        if ($imageFile) {
            foreach ($imageFile as $image) {
                $imageFileName[] = $this->targetDirectory . $this->fileUploader->upload($image);
            }
        }

        if ($imageLinks) {
            $imageFileName = $this->prepareImageByLinks($imageLinks);
        }

        return $imageFileName;
    }

    /**
     * @param GeneratedArticles $articleObject
     * @param array $imageFileName
     * @return void
     */
    public function addArticleImage(GeneratedArticles $articleObject, array $imageFileName): void
    {
        /** @var ArticleImages|null $newArticleImage */
        /** @var GeneratedArticles|null $articleObject */

        $newArticleImage = new ArticleImages;
        $newArticleImage
            ->setArticle($articleObject);

        if (is_array($imageFileName)) {
            foreach ($imageFileName as $image) {
                $newArticleImage
                    ->setImageLink($image);
                $this->em->persist($newArticleImage);
            }
        } else {
            $newArticleImage
                ->setImageLink($imageFileName);
            $this->em->persist($newArticleImage);
        }
        $this->em->flush();
    }

    /**
     * @param UserInterface|null $user
     * @param int $sizeFrom
     * @param int|null $sizeTo
     * @param array $imageFileName
     * @return array
     */
    public function templateSelect(UserInterface|null $user, int $sizeFrom, int|null $sizeTo, array $imageFileName): array
    {
        if ($sizeTo == null) {
            $sizeTo = $sizeFrom;
        }

        /** @var Module|null $userModules */
        if ($user == null || $this->moduleRepository->getUserTemplates($user->getId()) == null) {
            $userModules = $this->moduleService->getDefaultTemplates($imageFileName);
            shuffle($userModules);

            for ($i = $sizeFrom; $i <= $sizeTo; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[] = $module;
            }

        } elseif ($imageFileName == null) {
            $userModules = $this->moduleRepository->getUserTemplates($user->getId());
            shuffle($userModules);

            foreach ($userModules as $module) {
                if (preg_match('{{{ image }}}', $module['code'])) {
                    continue;
                } else {
                    for ($i = $sizeFrom; $i <= $sizeTo; $i++) {
                        $template[$module['id']] = $module['code'];
                    }
                }
            }

        } else {
            $userModules = $this->moduleRepository->getUserTemplates($user->getId());
            shuffle($userModules);

            for ($i = $sizeFrom; $i <= $sizeTo; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[$module['id']] = $module['code'];
            }
        }

        return $template;
    }

    /**
     * @param string $imageLinks
     * @return array
     */
    public function prepareImageByLinks(string $imageLinks): array
    {
        $images = [];
        foreach (explode(',', $imageLinks) as $key => $image) {
            $image = trim($image);
            if ($this->checkMimeType($image)) {
                $images[$key] = trim($image);
            }
        }
        return $images;
    }

    /**
     * @param string $image
     * @return bool
     */
    public function checkMimeType(string $image)
    {
        {
            $mimes = array(
                IMAGETYPE_GIF => "image/gif",
                IMAGETYPE_JPEG => "image/jpg",
                IMAGETYPE_PNG => "image/png",
                IMAGETYPE_WEBP => "image/webp");

            if (($image_type = exif_imagetype($image)) && (array_key_exists($image_type, $mimes))) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param array $parameters
     * @param string $token
     * @return GeneratedArticles|string[]
     */
    public function validateApiRequest(array $parameters, string $token)
    {
        $user = $this->apiTokenRepository->findOneBy(['token' => $token])->getUser();
        $theme = $this->articleContentRepository->findOneBy(['code' => $parameters['theme']]);

        $errors = $this->validator->validate($parameters);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return [
                'error' => $errorsString,
            ];
        } elseif ($theme === null) {
            return [
                'error' => 'Такой тематики не существует',
            ];
        } elseif ($this->subscriptionService->checkDisabled2Hours($user)) {
            return [
                'error' => 'Вы уже сгенерировали 2 статьи за последние 2 часа. Оформите подписку PRO, чтобы снять ограничения',
            ];
        } elseif ($parameters['theme'] == null || $parameters['title'] == null) {
            return [
                'error' => 'Отсутствуют обязательные параметры: theme, title',
            ];
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

            if ($this->subscriptionService->checkSubscription($user) == 'FREE') {
                $requestArray['keyword1'] = null;
                $requestArray['keyword2'] = null;
                $requestArray['keyword3'] = null;
                $requestArray['keyword4'] = null;
                $requestArray['keyword5'] = null;
                $requestArray['keyword6'] = null;
                $requestArray['word2'] = null;
                $requestArray['word2Count'] = null;
                $requestArray['images'] = null;
            }

            return $this->generateArticle($user, $requestArray, $requestArray['images'], true);
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public function getArticleParams(int $id): string
    {
        $repeatParams = explode(',', implode(' ', $this->generatedArticlesRepository->getArticleTemplate($id)[0]));
        return str_replace('_', '=',implode('&', $repeatParams));
    }
}
