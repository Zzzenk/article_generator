<?php

namespace App\Form;

use App\Repository\GeneratedArticlesRepository;
use App\Service\SubscriptionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleCreateType extends AbstractType
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly GeneratedArticlesRepository $generatedArticlesRepository,
        private readonly Security $security,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $this->subscriptionService->checkDisabled2Hours($this->security->getUser());
        $disabledFree = $this->subscriptionService->checkDisabledFree();

        $builder
            ->add('theme', ChoiceType::class, [
                'placeholder' => 'Выберите тематику',
                'label' => false,
                'choices' => array_flip($this->generatedArticlesRepository->getArticleThemes()),
                'disabled' => $disabled,
            ])
            ->add('title', null, [
                'label' => 'Заголовок',
                'disabled' => $disabled,
            ])
            ->add('keyword0', null, [
                'required' => false,
                'label' => 'Ключевое слово',
                'disabled' => $disabled,
            ])
            ->add('keyword1', null, [
                'required' => false,
                'label' => 'Родительный падеж',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('keyword2', null, [
                'required' => false,
                'label' => 'Дательный падеж',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('keyword3', null, [
                'required' => false,
                'label' => 'Винительный падеж',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('keyword4', null, [
                'required' => false,
                'label' => 'Творительный падеж',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('keyword5', null, [
                'required' => false,
                'label' => 'Предложный падеж',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('keyword6', null, [
                'required' => false,
                'label' => 'Множественное число',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('sizeFrom', IntegerType::class, [
                'required' => false,
                'label' => 'Размер статьи от',
                'disabled' => $disabled,
            ])
            ->add('sizeTo', IntegerType::class, [
                'required' => false,
                'label' => 'до',
                'disabled' => $disabled,
            ])
            ->add('word1', null, [
                'required' => false,
                'label' => 'Продвигаемое слово 1',
                'disabled' => $disabled,
            ])
            ->add('word1Count', IntegerType::class, [
                'required' => false,
                'label' => 'кол-во',
                'disabled' => $disabled,
            ])
            ->add('word2', null, [
                'required' => $disabled,
                'label' => 'Продвигаемое слово 2',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('word2Count', IntegerType::class, [
                'required' => false,
                'label' => 'кол-во',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('images', FileType::class, [
                'required' => false,
                'label' => 'Изображения (до 5 файлов)',
                'disabled' => $disabled || $disabledFree,
                'mapped' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple',
                    'data_class' => null,
                ],
            ])
            ->add('imageLink', TextareaType::class, [
                'required' => false,
                'label' => ' ',
                'disabled' => $disabled || $disabledFree,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => null,
        ]);
    }
}
