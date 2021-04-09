<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Form\TripType;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripController extends AbstractController
{
    const OUVERTE = 'Ouverte';

    /**
     * @Route("/trip/{id}", name="trip_getDetail")
     */
    public function getDetail($id, TripRepository $tripRepository): Response
    {
        $tripDetail = $tripRepository->findATrip($id);

        return $this->render('trip/detail.html.twig', [
            'tripDetail' => $tripDetail
        ]);
    }

    /**
     * @Route("/create", name="trip_create")
     */
    public function create(): Response {

        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);

        return $this->render('trip/createTrip.html.twig', [
            'tripForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/trip/{id}", name="trip_registerForATrip")
     */
    public function registerForATrip($id, TripRepository $tripRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $tripForRegistration = $tripRepository->findATripForRegister($id);

        $tripState = $tripForRegistration->getState()->getWording();
        $tripDateLimitForRegistration = $tripForRegistration->getDateLimitForRegistration();
        $tripMaxRegistrationNumber = $tripForRegistration->getMaxRegistrationNumber();
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();
        $now = new \DateTime();

        if (in_array($user, $tripParticipants))
        {
            $this->addFlash('danger', 'Vous êtes déjà inscrit!');
            $userIsRegisteredForTrip = true;
        } else {
            $userIsRegisteredForTrip = false;
        }

        if (!$tripState === self::OUVERTE)
        {
            $this->addFlash('danger', 'Désolé, l\'inscription n\'est pas encore ouverte');
            $tripIsOpened = false;
        } else {
            $tripIsOpened = true;
        }

        if ($tripDateLimitForRegistration <= $now){
            $this->addFlash('danger', 'Désolé, la date limite d\'inscription est dépassée');
            $registrationDateIsValid = false;
        } else {
            $registrationDateIsValid = true;
        }

        if (count($tripParticipants) >= $tripMaxRegistrationNumber){
            $this->addFlash('danger', 'Désolé, la sortie est déjà complète');
            $tripIsFull = true;
        } else {
            $tripIsFull = false;
        }

        if ( !$userIsRegisteredForTrip AND $tripIsOpened AND $registrationDateIsValid AND !$tripIsFull)
        {
            $tripForRegistration->addParticipant($user);
            $entityManager->persist($tripForRegistration);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes inscrit! ');
        }

        return $this->redirectToRoute('main_home');
    }
}
