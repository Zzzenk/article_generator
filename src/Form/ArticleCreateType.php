<?php

namespace App\Form;

use App\Repository\ArticleContentRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ArticleCreateType extends AbstractType
{

    private ArticleContentRepository $articleContentRepository;
    private UserRepository $userRepository;

    public function __construct(ArticleContentRepository $articleContentRepository, UserRepository $userRepository)
    {
        $this->articleContentRepository = $articleContentRepository;
        $this->userRepository = $userRepository;;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $this->userRepository->checkDisabled2Hours(null);
        $disabledFree = $this->userRepository->checkDisabledFree();

        $builder
            ->add('theme', ChoiceType::class, [
                'placeholder' => 'Выберите тематику',
                'label' => false,
                'choices' => array_flip($this->articleContentRepository->themes()),
                'disabled' => $disabled ?? false,
            ])
            ->add('title', null, [
                'label' => 'Заголовок',
                'disabled' => $disabled ?? false,
            ])
            ->add('keyword0', null, [
                'required' => false,
                'label' => 'Ключевое слово',
                'disabled' => $disabled ?? false,
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
            ->add('sizeFromField', IntegerType::class, [
                'required' => false,
                'label' => 'Размер статьи от',
                'disabled' => $disabled ?? false,
            ])
            ->add('sizeToField', IntegerType::class, [
                'required' => false,
                'label' => 'до',
                'disabled' => $disabled ?? false,
            ])
            ->add('promotedWord1', null, [
                'required' => false,
                'label' => 'Продвигаемое слово 1',
                'disabled' => $disabled ?? false,
            ])
            ->add('promotedWord1Count', IntegerType::class, [
                'required' => false,
                'label' => 'кол-во',
                'disabled' => $disabled ?? false,
            ])
            ->add('promotedWord2', null, [
                'required' => $disabled ?? false,
                'label' => 'Продвигаемое слово 2',
                'disabled' => $disabled || $disabledFree,
            ])
            ->add('promotedWord2Count', IntegerType::class, [
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
