<?php

namespace App\Service;

use App\Entity\ArticleContent;
use App\Entity\Module;
use App\Repository\ArticleContentRepository;
use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ArticleGeneratorService
{
    public function __construct(
        private readonly ArticleContentRepository $articleContentRepository,
        private readonly ModuleRepository $moduleRepository,
        private readonly GeneratedArticlesRepository $generatedArticlesRepository
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
                $this->generatedArticlesRepository->addArticle($user, $title, $article, $requestArray, $imageFileName, $keywords);
            }

            return [
                'title' => $title,
                'article' => $article,
                'keywords' => explode(',', $keywords),
            ];
        }
    }

    public function templateSelect($user, $sizeFrom, $sizeTo, $imageFileName): array
    {
        if ($sizeTo == null) {
            $sizeTo = $sizeFrom;
        }

        /** @var Module|null $userModules */
        if ($user == null || $this->moduleRepository->getUserTemplates($user->getId()) == null) {
            $userModules = $this->moduleRepository->defaultTemplates($imageFileName);
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

}

