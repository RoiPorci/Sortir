<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CancelTripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('details', TextareaType::class, [
                'label' => 'Motif :',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner un motif d'annulation!"
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => '{{ limit }} caractères minimum requis',
                        // max length allowed by Symfony for security reasons
                        'max' => 200,
                        'maxMessage' => "{{ limit }} caractères maximum"
                    ]),
                ],
            ])
            ->add("confirm", SubmitType::class, [
                'label' => "Confirmer l'annulation"
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
