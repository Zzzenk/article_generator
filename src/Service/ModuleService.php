<?php

namespace App\Service;

use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;

class ModuleService
{
    // templates with images
    const TEMPLATE_COLUMNS_ROWS_TITLE_PARAGRAPH_IMAGE = '/templates/default_templates/columns_rows_title_paragraph_image.html.twig';
    const TEMPLATE_IMAGE_PARAGRAPHS = '/templates/default_templates/image_paragraphs.html.twig';

    // templates without images
    const TEMPLATE_PARAGRAPH_ALIGN_RIGHT = '/templates/default_templates/paragraph_align_right.html.twig';
    const TEMPLATE_TITLE_PARAGRAPHS = '/templates/default_templates/title_paragraphs.html.twig';
    const TEMPLATE_TWO_COLUMNS_PARAGRAPH = '/templates/default_templates/two_columns_paragraph.html.twig';

    public function __construct(
        private $rootPath,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param $user
     * @param string $title
     * @param string $body
     * @return void
     */
    public function addTemplate($user, string $title, string $body): void
    {
        $newTemplate = new Module();
        $newTemplate
            ->setUser($user)
            ->setCode($body)
            ->setTitle($title)
        ;
        $this->em->persist($newTemplate);
        $this->em->flush();
    }

    /**
     * @param array $imageFileName
     * @return array
     */
    public function getDefaultTemplates(array $imageFileName): array
    {
        if ($imageFileName) {
            return [
                file_get_contents($this->rootPath . self::TEMPLATE_COLUMNS_ROWS_TITLE_PARAGRAPH_IMAGE),
                file_get_contents($this->rootPath . self::TEMPLATE_IMAGE_PARAGRAPHS),
                file_get_contents($this->rootPath . self::TEMPLATE_PARAGRAPH_ALIGN_RIGHT),
                file_get_contents($this->rootPath . self::TEMPLATE_TWO_COLUMNS_PARAGRAPH),
                file_get_contents($this->rootPath . self::TEMPLATE_TITLE_PARAGRAPHS)
            ];
        } else {
            return [
                file_get_contents($this->rootPath . self::TEMPLATE_PARAGRAPH_ALIGN_RIGHT),
                file_get_contents($this->rootPath . self::TEMPLATE_TWO_COLUMNS_PARAGRAPH),
                file_get_contents($this->rootPath . self::TEMPLATE_TITLE_PARAGRAPHS)
            ];
        }
    }
}
