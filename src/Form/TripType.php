<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
use App\Entity\Trip;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    private EntityManagerInterface $manager;
    /**
     * @var Updater
     */
    private Updater $updater;

    private array $states;

    public function __construct(EntityManagerInterface $manager, Updater $updater)
    {
        $this->manager = $manager;
        $this->updater = $updater;
        $this->states = $this->updater->states;
    }

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
            ->add('create', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'])
        ;
    }

    public function addElements(FormInterface $form, City $city = null)
    {
        $form->add('city', EntityType::class, [
            'class' => City::class,
            'choice_label' => 'name',
            'label' => 'Ville :',
            'required' => false,
            'mapped' => false,
            'data' => $city,
            "attr" => [
                "class" => "form-select"],
        ]);

        $locations = [];

        if ($city) {
            $locationRepository = $this->manager->getRepository('App:Location');
            $locations = $locationRepository->findBy(['city' => $city]);
        }
        $form->add('location', EntityType::class, [
            'class' => Location::class,
            'choices' => $locations,
            'placeholder' => false,
            'choice_label' => 'name',
            'label' => 'Lieu :',
            'required' => false,
            "attr" => [
                "class" => "form-select"],
        ]);
    }

    public function addModifyElements(FormInterface $form, Trip $trip = null){
        if ($trip){
            if ($trip->getId()){
                $form->add('organiserCampus', EntityType::class, [
                    'class' => Campus::class,
                    'placeholder' => false,
                    'choice_label' => 'name',
                    'label' => 'Campus :',
                    'required' => false,
                    "attr" => [
                        "class" => "form-select"],
                ]);

                if ($trip->getState() == $this->states['created']){
                    $form->add('publish', SubmitType::class, [
                        'label' => 'Publier'
                    ]);
                }
            }
            else {
                $form->add('publish', SubmitType::class, [
                    'label' => 'Publier'
                ]);
            }
        }
        else {
            $form->add('publish', SubmitType::class, [
                'label' => 'Publier'
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event){
        $form = $event->getForm();
        $data = $event->getData();

        $city = $this->manager->getRepository('App:City')->find($data['city']);

        $this->addElements($form, $city);
    }

    public function onPreSetData(FormEvent $event){
        $trip = $event->getData();
        $form = $event->getForm();

        /*dd($trip);*/

        if($trip->getLocation()){
            $city = $trip->getLocation()->getCity();
        }
        else {
            $city = null;
        }

        $this->addElements($form, $city);
        $this->addModifyElements($form, $trip);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
