<?php

namespace App\Service;

use App\Entity\ArticleContent;
use App\Entity\ArticleImages;
use App\Entity\GeneratedArticles;
use App\Entity\Module;
use App\Repository\ApiTokenRepository;
use App\Repository\ArticleContentRepository;
use App\Repository\GeneratedArticlesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleGeneratorService
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly ArticleContentRepository $articleContentRepository,
        private readonly EntityManagerInterface $em,
        private readonly GeneratedArticlesRepository $generatedArticlesRepository,
        private readonly ModuleService $moduleService,
        private readonly FileUploader $fileUploader,
        private readonly SubscriptionService $subscriptionService,
        private readonly ValidatorInterface $validator,
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
    }

    public function generateArticle($user, $requestArray, $imageFileName, $insert)
    {
        if ($requestArray != null) {
            $theme = $requestArray['theme'];
            $title = $requestArray['title'];
            $sizeFrom = $requestArray['sizeFrom'] ?? null;
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
                $allArticles = $this->articleContentRepository->findAll();
            }
            shuffle($allArticles);

            foreach ($allArticles as $paragraph) {
                if (str_contains($article, '{{ paragraph }}') === false && str_contains($article, '{{ paragraphs }}') === false) {
                    break;
                }

                $paragraph = $paragraph->getBody();

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

                preg_match_all('/{{ image }}/', $article, $matches[]);

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

            return [
                'title' => $title,
                'article' => $article,
                'keywords' => explode(',', $keywords),
            ];
        }
    }

    public function addArticle($user, $title, $article, $template, $imageFileName, $keywords)
    {
        $template['images'] = null;
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

    public function imageHandler($imageFile, $imageLinks): array
    {
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

    public function addArticleImage($articleObject, $imageFileName)
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

    public function templateSelect($user, $sizeFrom, $sizeTo, $imageFileName): array
    {
        if ($sizeTo == null) {
            $sizeTo = $sizeFrom;
        }

        /** @var Module|null $userModules */
        if ($user == null || $this->moduleService->getUserTemplates($user->getId()) == null) {
            $userModules = $this->moduleService->defaultTemplates($imageFileName);
            shuffle($userModules);

            for ($i = $sizeFrom; $i <= $sizeTo; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[] = $module;
            }

        } elseif ($imageFileName == null) {
            $userModules = $this->moduleService->getUserTemplates($user->getId());
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
            $userModules = $this->moduleService->getUserTemplates($user->getId());
            shuffle($userModules);

            for ($i = $sizeFrom; $i <= $sizeTo; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[$module['id']] = $module['code'];
            }
        }

        return $template;
    }

    public function getArticleThemes()
    {
        $qb = $this->em->createQueryBuilder();
        $themesArray = $qb
            ->select('ac.code', 'ac.theme')
            ->from('App:ArticleContent', 'ac')
            ->getQuery()
            ->getResult()
        ;

        foreach ($themesArray as $array) {
            $implodedArray[] = (implode(',', $array));
        }

        $keysList = explode(',', implode(',', array_unique($implodedArray)));

        foreach ($keysList as $key => $item) {
            if (!is_float($key/2)) {
                $themes[$item] = null;
                $prevItem = $item;
            } else {
                $themes[$prevItem] = $item;
            }
        }

        return $themes;
    }


    public function prepareImageByLinks($imageLinks)
    {
        $images = [];
        foreach (explode(',', $imageLinks) as $key => $image) {
            $image = trim($image);
            if ($this->checkMimeType($image)) {
                $images[$key] = trim($image);
            }
        }
        return $images ?? null;
    }

    public function checkMimeType($image)
    {
        {
            $mimes = array(
                IMAGETYPE_GIF => "image/gif",
                IMAGETYPE_JPEG => "image/jpg",
                IMAGETYPE_PNG => "image/png",
                IMAGETYPE_WEBP => "image/webp");

            if (($image_type = exif_imagetype($image)) && (array_key_exists($image_type, $mimes))) {
                return $mimes[$image_type];
            } else {
                return false;
            }
        }
    }

    public function validateApiRequest($parameters, $token)
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

}

