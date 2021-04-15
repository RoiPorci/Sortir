<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileCsv', FileType::class, [
                'required' => false,
                'label' => 'Fichier .csv :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez charger un fichier!'
                    ]),
                    /*new File([
                        //'mimeTypes' => 'text/csv',
                        'mimeTypesMessage' => 'Frère upload que du csv!'
                    ])*/
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
