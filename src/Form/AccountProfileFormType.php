<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите имя',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Имя должно содержать минимум {{ limit }} символа',
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'О себе',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Расскажите немного о себе...',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Описание не должно быть длиннее {{ limit }} символов',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
