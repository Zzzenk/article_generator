<?php

namespace App\Service;

use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;

class ModuleService
{
    // templates with images
    const TEMPLATE_COLUMNS_ROWS_TITLE_PARAGRAPH_IMAGE = 'templates/default_templates/columns_rows_title_paragraph_image.html.twig';
    const TEMPLATE_IMAGE_PARAGRAPHS = 'templates/default_templates/image_paragraphs.html.twig';

    // templates without images
    const TEMPLATE_PARAGRAPH_ALIGN_RIGHT = 'templates/default_templates/paragraph_align_right.html.twig';
    const TEMPLATE_TITLE_PARAGRAPHS = 'templates/default_templates/title_paragraphs.html.twig';
    const TEMPLATE_TWO_COLUMNS_PARAGRAPH = 'templates/default_templates/two_columns_paragraph.html.twig';

    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function addTemplate($user, $title, $body)
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

    public function deleteTemplate($moduleId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->delete()
            ->from(Module::class, 'm')
            ->where('m.id = :id')
            ->setParameter('id', $moduleId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getUserTemplates($userId)
    {
        $qb = $this->em->createQueryBuilder();
        return $qb
            ->select('m.id', 'm.code')
            ->from(Module::class, 'm')
            ->where('m.user = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function defaultTemplates($imageFileName) : array
    {
        if ($imageFileName) {
            return [
                self::TEMPLATE_COLUMNS_ROWS_TITLE_PARAGRAPH_IMAGE,
                self::TEMPLATE_IMAGE_PARAGRAPHS,
                self::TEMPLATE_PARAGRAPH_ALIGN_RIGHT,
                self::TEMPLATE_TWO_COLUMNS_PARAGRAPH,
                self::TEMPLATE_TITLE_PARAGRAPHS
            ];
        } else {
            return [
                self::TEMPLATE_PARAGRAPH_ALIGN_RIGHT,
                self::TEMPLATE_TWO_COLUMNS_PARAGRAPH,
                self::TEMPLATE_TITLE_PARAGRAPHS
            ];
        }
    }
}