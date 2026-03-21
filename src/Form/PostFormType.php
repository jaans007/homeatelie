<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $authorName = $options['cover_author_name'] ?? 'Автор';

        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'attr' => [
                    'class' => 'form-control',
                    'required' => false,
                    'placeholder' => 'Введите заголовок статьи',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
                'placeholder' => 'Выберите категорию',
                'required' => true,
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
            ->add('imageAttribution', ChoiceType::class, [
                'label' => 'Автор обложки',
                'required' => true,
                'placeholder' => 'Выберите автора обложки',
                'choices' => [
                    'Создано автором — ' . $authorName => 'author',
                    'ChatGPT' => 'chatgpt',
                    'Midjourney' => 'midjourney',
                    'DALL·E' => 'dalle',
                    'Flux' => 'flux',
                    'Stable Diffusion' => 'stable_diffusion',
                    'Unsplash' => 'unsplash',
                    'Pexels' => 'pexels',
                    'Pixabay' => 'pixabay',
                    'Другое / без уточнения' => 'other',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Выберите автора обложки.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'cover_author_name' => 'Автор',
        ]);
    }
}
