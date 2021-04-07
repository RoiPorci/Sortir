<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=> 'Nom de la sortie :',
                'required' => false
            ])
            ->add('dateTimeStart', TextType::class, [
                'label'=> 'Date et heure de la sortie :',
                'required' => false
            ])
            ->add('dateLimitForRegistration', TextType::class, [
                'label'=> "Date limite d'inscription :",
                'required' => false
            ])
            ->add('duration', TextType::class, [
                'label'=> "Durée :",
                'required' => false
            ])
            ->add('maxRegistrationNumber', TextType::class, [
                'label'=> "Nombre de places :",
                'required' => false
            ])
            ->add('details', TextType::class, [
                'label'=> "Description et infos :",
                'required' => false
            ])
            ->add('organiserCampus', TextType::class, [
                'label'=> "Campus :",
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
