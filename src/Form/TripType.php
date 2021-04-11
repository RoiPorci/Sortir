<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Trip;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('dateTimeStart', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie :',
                'required' => false
            ])
            ->add('dateLimitForRegistration', DateType::class, [
                'widget' => 'single_text',
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
            ->add('details', TextareaType::class, [
                'label'=> "Description et infos :",
                'required' => false
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville :',
                'mapped' => false,
                'choice_label' => 'name',
                'class' => City::class,
                'attr' => [
                    "class" => "form-select"
                ]

            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'label' => 'Ville :',
                'required' => false,
                'mapped' => false,
                "attr" => [
                    "class" => "form-select"],
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'label' => 'Lieu :',
                'required' => false,
                "attr" => [
                    "class" => "form-select"],
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
