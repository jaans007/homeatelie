<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите заголовок статьи',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
                'placeholder' => 'Выберите категорию',
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Текст статьи',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 14,
                    'placeholder' => 'Напишите текст статьи',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Изображение статьи',
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
