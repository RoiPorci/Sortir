<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                    'label'=> 'Pseudo :'
            ])
            ->add('password', TextType::class, [
                'label'=> 'Mot de passe :'
            ])
            ->add('last_name', TextType::class, [
                'label'=> 'Prénom :'
            ])
            ->add('first_name', TextType::class, [
                'label'=> 'Nom :'
            ])
            ->add('email', TextType::class, [
                'label'=> 'E-mail :'
            ])
            ->add('phone', TextType::class, [
                'label'=> 'Téléphone :'
            ])
            ->add('envoyer', SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
