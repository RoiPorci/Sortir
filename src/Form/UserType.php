<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label'=> 'Pseudo :',
                'required' => false
            ])
            ->add('last_name', TextType::class, [
                'label'=> 'Prénom :',
                'required' => false
            ])
            ->add('first_name', TextType::class, [
                'label'=> 'Nom :',
                'required' => false
            ])
            ->add('email', TextType::class, [
                'label'=> 'E-mail :',
                'required' => false
            ])
            ->add('phone', TextType::class, [
                'label'=> 'Téléphone :',
                'required' => false
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit correspondre!',
                'first_options' => ['label' => 'Mot de passe :',],
                'second_options' => ['label' => 'Confirmation :',],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => '{{ limit }} caractères minimum requis',
                        // max length allowed by Symfony for security reasons
                        'max' => 30,
                        'maxMessage' => "{{ limit }} caractères maximum"
                    ]),
                ],
                'required' => false
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'class' => Campus::class,
                'choice_label' => 'name',
                'attr' => [
                    "class" => "form-select"
                ]
            ])
            ->add('picture', FileType::class, [
                'mapped' => false,
                'label' => 'Ma Photo:',
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '8M',
                        'maxSizeMessage' => '8 megas max svp',
                    ])
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
