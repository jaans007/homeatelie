<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Комментарий',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Напишите ваш комментарий...',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите текст комментария',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Комментарий должен содержать минимум {{ limit }} символа',
                        'max' => 3000,
                        'maxMessage' => 'Комментарий не должен быть длиннее {{ limit }} символов',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
