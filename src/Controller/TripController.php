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
    public function registerForATrip($id, TripRepository $tripRepository): Response
    {
        $user = $this->getUser();

        $tripForRegistration = $tripRepository->findATripForRegister($id);

        $tripState = $tripForRegistration->getState()->getWording();
        $tripDateLimitForRegistration = $tripForRegistration->getDateLimitForRegistration();
        $tripMaxRegistrationNumber = $tripForRegistration->getMaxRegistrationNumber();
        $tripParticipants = $tripForRegistration->getParticipants();
        $now = new \DateTime();
        dd($tripParticipants);

        if ($tripState === self::OUVERTE
            AND $tripDateLimitForRegistration >= $now
            AND count($tripParticipants) < $tripMaxRegistrationNumber
            AND in_array($user, $tripParticipants))
        {
            dd(count($tripParticipants));
        }
        dd($user);

        return $this->redirectToRoute('main_home');
    }
}
