<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListTripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
                'required' => false,
                "attr" => [
                    "class" => "form-select"
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'mapped' => false,
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Entre',
                'required' => false,
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'et',
                'required' => false,
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('isOrganiser', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required' => false,
                'mapped' => false,
            ])
            ->add('isParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false,
                'mapped' => false,
            ])
            ->add('isNotParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
                'required' => false,
                'mapped' => false,
            ])
            ->add('past', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
