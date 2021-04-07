<?php

namespace App\Form;

use Symfony\Component\Security\Core\Security;
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
    private $security;

    public function __construct(Security $security){
        $this->security = $security;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User */
        $user = $this->security->getUser();

        //dd($user);

        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
                'required' => false,
                'placeholder' => false,
                'data' => $user->getCampus(),
                "attr" => [
                    "class" => "form-select"
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Entre',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'et',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('isOrganiser', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required' => false,
            ])
            ->add('isParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false,
            ])
            ->add('isNotParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false,
            ])
            ->add('past', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
