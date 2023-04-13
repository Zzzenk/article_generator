<?php

namespace App\Service;

use App\Entity\ArticleContent;
use App\Entity\Module;
use App\Repository\ArticleContentRepository;
use App\Repository\GeneratedArticlesRepository;
use App\Repository\ModuleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
class ArticleGeneratorService
{
    private static $article = 'Вот уже больше пятнадцати лет у фанатов сериала Half-Life душа болит из-за так и не вышедшей Half-Life 2: Episode 3. Многие умельцы пообещали сделать заключительный эпизод боевика самостоятельно на базе сценария Марка Лэйдлоу — и совсем скоро один такой проект увидит свет дня.

Моддер valina35 анонсировал дату релиза Episode 3: The Return, своей вариации на тему затерянного в офисах Valve третьего эпизода Half-Life 2. Итак, премьера короткометражного экшена назначена на 10 апреля.

Напомним, что сейчас Valve помаленьку возвращается в строй. В 2020-м году она выпустила VR-приключение Half-Life 2: Alyx, а летом она перенесёт Counter-Strike: Global Offensive на рельсы движка Source 2.';
    private ArticleContentRepository $articleContentRepository;
    private ModuleRepository $moduleRepository;
    private GeneratedArticlesRepository $generatedArticlesRepository;

    public function __construct(ArticleContentRepository $articleContentRepository,
                                ModuleRepository $moduleRepository,
                                GeneratedArticlesRepository $generatedArticlesRepository)
    {
        $this->articleContentRepository = $articleContentRepository;
        $this->moduleRepository = $moduleRepository;
        $this->generatedArticlesRepository = $generatedArticlesRepository;
    }

    public function generateArticle($user, $requestArray, $imageFileName, $insert)
    {
        if ($requestArray != null) {
            $theme = $requestArray['theme'];
            $title = $requestArray['title'];
            $sizeFromField = $requestArray['sizeFromField'] ?? null;
            $sizeToField = $requestArray['sizeToField'] ?? null;
            $promotedWord1 = $requestArray['promotedWord1'] ?? null;
            $promotedWord1Count = $requestArray['promotedWord1Count'] ?? 1;
            $promotedWord2 = $requestArray['promotedWord2'] ?? null;
            $promotedWord2Count = $requestArray['promotedWord2Count'] ?? null;

            $template = implode(PHP_EOL . PHP_EOL, $this->templateSelect($user, $sizeFromField, $sizeToField, $imageFileName));

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
                $allArticles = $this->articleContentRepository->findBy(['code' => $this->articleContentRepository->findOneBy(['theme' => $theme])->getCode()]);
            } else {
                $allArticles = $this->articleContentRepository->findAll();
            }
            shuffle($allArticles);

            foreach ($allArticles as $parapraph) {
                if (str_contains($template, '{{ paragraph }}') === false && str_contains($template, '{{ paragraphs }}') === false) {
                    break;
                }

                $parapraph = $parapraph->getBody();

                if ($promotedWord1) {
                    $parapraph = explode(' ', $parapraph);
                    $word1 = "<b>" . $promotedWord1 . "</b>";
                    for ($i = 1; $i <= $promotedWord1Count; $i++) {
                        array_splice($parapraph, rand(1, count($parapraph)-1), 0, $word1);
                    }
                    $parapraph = implode(' ', $parapraph);
                }
                if ($promotedWord2) {
                    $parapraph = explode(' ', $parapraph);
                    $word2 = "<b>" . $promotedWord2 . "</b>";
                    for ($i = 1; $i <= $promotedWord2Count; $i++) {
                        array_splice($parapraph, rand(1, count($parapraph)-1), 0, $word2);
                    }
                    $parapraph = implode(' ', $parapraph);
                }

                $template = preg_replace('{{{ paragraph }}}', $parapraph, $template, 1);
                $template = preg_replace('{{{ paragraphs }}}', $parapraph, $template, 1);
                $template = preg_replace('{{{ title }}}', $title, $template, 1);
            }

            if ($imageFileName) {
                shuffle($imageFileName);
                foreach ($imageFileName as $image) {
                    if (str_contains($template, '{{ image }}') === false) {
                        break;
                    }
                    $template = preg_replace('{{{ image }}}', $image, $template, 1);
                }
            }

            if ($insert === true) {
                $this->generatedArticlesRepository->addArticle($user, $title, $template, $template, $imageFileName, $keywords);
            }

            return [
                'title' => $title,
                'article' => $template,
                'keywords' => explode(',', $keywords),
            ];
        }
    }

    public function templateSelect($user, $sizeFromField, $sizeToField, $imageFileName)
    {
        /** @var Module|null $userModules */

        if ($user == null || $this->moduleRepository->getUserTemplates($user->getId()) == null) {
            $userModules = $this->moduleRepository->defaultTemplates($imageFileName);
            for ($i = $sizeFromField; $i <= $sizeToField; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[] = $module;
            }

        } else {
            $userModules = $this->moduleRepository->getUserTemplates($user->getId());

            for ($i = $sizeFromField; $i <= $sizeToField; $i++) {
                $module = $userModules[rand(0, count($userModules) -1)];
                $template[$module['id']] = $module['code'];
            }
        }

        return $template;
    }


    public function prepareArticleForApi($requestArray)
    {
        $title = array('{{ title }}');
        $paragraphs = array('{{ paragraph|raw }}', '{{ paragraphs|raw }}');
        $requestArray['article'] = str_replace($title, $requestArray['title'], $requestArray['article']);
        $requestArray['article'] = str_replace($paragraphs, $requestArray['article'], $requestArray['article']);

        return $requestArray['article'];
    }

}
