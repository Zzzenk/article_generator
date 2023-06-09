<?php

namespace App\DataFixtures;

use App\Entity\ArticleContent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleContentFixtures extends Fixture implements OrderedFixtureInterface
{

    public function getOrder(): int
    {
        return 15;
    }
    private static $articles = [
        ['theme' => 'Игры',
        'code' => 'games',
        'body' => 'Вот уже больше пятнадцати лет у фанатов сериала Half-Life душа болит из-за так и не вышедшей Half-Life 2: Episode 3. Многие умельцы пообещали сделать заключительный эпизод боевика самостоятельно на базе сценария Марка Лэйдлоу — и совсем скоро один такой проект увидит свет дня.',
        ],
        ['theme' => 'Игры',
        'code' => 'games',
        'body' => 'Моддер valina35 анонсировал дату релиза Episode 3: The Return, своей вариации на тему затерянного в офисах Valve третьего эпизода Half-Life 2. Итак, премьера короткометражного экшена назначена на 10 апреля.',
        ],
        ['theme' => 'Игры',
        'code' => 'games',
        'body' => 'Напомним, что сейчас Valve помаленьку возвращается в строй. В 2020-м году она выпустила VR-приключение Half-Life 2: Alyx, а летом она перенесёт Counter-Strike: Global Offensive на рельсы движка Source 2.',
        ],
        ['theme' => 'Игры',
        'code' => 'games',
        'body' => 'Расширение Half-Life: Alyx NoVR теперь позволяет пройти игру целиком без шлема виртуальной реальности. Авторы отмечают, что проект находится на стадии «раннего доступа», так что пока не лишён шероховатостей.',
        ],
        ['theme' => 'Игры',
        'code' => 'games',
        'body' => 'Разработчикам удалось перенести управление всеми возможностями шутера для мышки и клавиатуры — включая гравиперчатки, улучшения оружия и всё прочее. В течение этого года команда планирует довести релиз до версии 1.0, доработав интерфейс, анимации, взаимодействие с окружением и прочие детали.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Исследователи из Корнельского университета разработали интерфейс EchoSpeech для распознавания тихой речи, который использует акустическое восприятие и искусственный интеллект для непрерывного распознавания до 31 невокализованной команды на основе движений губ и рта. Обработка информации осуществляется локально на смартфоне, что обеспечивает конфиденциальность данных.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Очки EchoSpeech оснащены парой микрофонов и динамиков, размер которых меньше ластика на конце карандаша, и не используют камеру. Устройство отправляет и улавливает акустические волны, которые показывают изменения динамики лицевых мышц и рта. Алгоритм глубокого обучения анализирует эти эхо-профили в режиме реального времени с точностью около 95%.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Полученные данные передаются через Bluetooth в режиме реального времени на смартфон, обрабатываются и хранятся локально на устройстве. Разработчики сообщают, что EchoSpeech достаточно нескольких минут для обучения для конкретного пользователя.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Большинство технологий распознавания немой речи ограничены избранным набором предопределенных команд и требуют, чтобы пользователь и его собеседник смотрели в камеру или носили ее. Это существенно затрудняет возможности применения таких устройств. Кроме того, большой поток данных требует обработки в облаке, что нарушает конфиденциальность пользователей.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'В своем нынешнем виде EchoSpeech можно использовать для общения с другими через смартфон в местах, где речь неудобна или неуместна, например, в шумном ресторане или тихой библиотеке. Бесшумный речевой интерфейс также можно использовать в паре со стилусом и программным обеспечением для проектирования, таким как САПР, практически исключая необходимость в клавиатуре и мыши, добавляют разработчики.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Фирма по кибербезопасности Home Security Heroes опубликовала исследование, в котором показала работу инструмента на основе искусственного интеллекта PassGAN для проверки более 15 680 000 паролей. Они смогли подобрать 51% распространённых паролей менее чем за минуту.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'PassGAN потребовалось менее часа, чтобы с помощью брутфорса взломать 65% паролей, а 71% был подобран менее чем за день и 81% — менее чем за месяц.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Исследователи составили таблицу, которая показывает, какие пароли являются самыми сложными. Если человек использует 12-значный пароль, состоящий из прописных и строчных букв, инструменту может потребоваться 289 лет, чтобы взломать его. При добавлении цифр этот срок увеличивается до 2000 лет, а символов — до 30 000 лет.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'В Home Security Heroes рекомендуют использовать пароли, состоящие не менее чем из 12 символов, и включающие не только цифры. Они встроили в свой сайт инструмент, где можно написать случайный пароль, и ресурс сообщит, сколько времени займёт его взлом.',
        ],
        ['theme' => 'Технологии',
        'code' => 'tech',
        'body' => 'Ранее исследователи из Университета Глазго разработали систему ThermoSecure, которая способна мгновенно угадывать пароль, анализируя тепловой отпечаток кончиков пальцев на клавиатуре или экране смартфона. Для этого они обучили искусственный интеллект эффективно читать изображения и делать обоснованные предположения о паролях из подсказок тепловых сигнатур с использованием вероятностной модели. Оказалось, что ThermoSecure способна раскрыть 86% паролей, если тепловизионный снимок был сделан в течение 20 секунд, 76%, если прошло 30 секунд и 62% спустя 60 секунд после ввода.',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::$articles as $article) {
            $articleContent = new ArticleContent();
            $articleContent
                ->setBody($article['body'])
                ->setCode($article['code'])
                ->setTheme($article['theme'])
            ;
            $manager->persist($articleContent);
        }

        $manager->flush();
    }
}
