<?php

namespace App\DataFixtures;

use App\Entity\Module;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ModuleFixtures extends Fixture implements OrderedFixtureInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getOrder(): int
    {
        return 10;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i <= 1; $i++) {
            $module = new Module();
            $module
                ->setTitle('Название с текстом')
                ->setCode('<h1>{{ title }}</h1>
<p>{{ paragraph }}</p>')
                ->setUser($this->userRepository->findAll()[$i])
            ;
            $manager->persist($module);

            $module = new Module();
            $module
                ->setTitle('Текст справа')
                ->setCode('<p class="text-right">{{ paragraph }}</p>')
                ->setUser($this->userRepository->findAll()[$i])
            ;
            $manager->persist($module);

            $module = new Module();
            $module
                ->setTitle('Текст в две колонки')
                ->setCode('<div class="row">
  <div class="col-sm-6">
    {{ paragraphs }}
  </div>
  <div class="col-sm-6">
    {{ paragraphs }}
  </div>
</div>')
                ->setUser($this->userRepository->findAll()[$i])
            ;
            $manager->persist($module);

            $module = new Module();
            $module
                ->setTitle('Две колонки в три ряда с изображенияи')
                ->setCode('<h1 class="mt-4">{{ title }}</h1>
<div class="media">
    <img class="mr-3" src="{{ image }}" width="446" height="250" alt="">
    <div class="media-body">
        <p>{{ paragraph }}</p>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <p>{{ paragraph }}</p>
    </div>
    <div class="col-sm-6">
        <p>{{ paragraph }}</p>
    </div>
</div>

<div class="media">
    <div class="media-body">
        <p>{{ paragraph }}</p>
    </div>
    <img class="ml-3" src="{{ image }}" width="446" height="250" alt="">
</div>')
                ->setUser($this->userRepository->findAll()[$i])
            ;
            $manager->persist($module);
        }

        $manager->flush();

    }
}
