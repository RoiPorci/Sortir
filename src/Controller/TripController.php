<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\TripType;
use App\Repository\CampusRepository;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TripController extends AbstractController
{
    /**
     * @var Updater
     */
    private Updater $updater;

    private array $states;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
        $this->states = $this->updater->states;
    }

    /**
     * @Route("/trip/{id}", name="trip_getDetail")
     * @param $id
     * @param TripRepository $tripRepository
     * @return Response
     * @throws NonUniqueResultException
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
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $button = $form->getClickedButton()->getName();
            if ($button == 'create'){
                $state = $this->states['created'];
            }
            else
            {
                $state = $this->states['opened'];
            }

            $user = $this->getUser();

            $trip->setState($state);
            $trip->setOrganiser($user);
            $trip->setOrganiserCampus($user->getCampus());

            $entityManager->persist($trip);
            $entityManager->flush();
        }

        $location = $trip->getLocation();

        return $this->render('trip/createTrip.html.twig', [
            'tripForm' => $form->createView(),
            'location' => $location
        ]);
    }

    /**
     * @Route("/register/trip/{id}", name="trip_registerForATrip")
     */
    public function registerForATrip($id,
                                     TripRepository $tripRepository,
                                     EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $tripForRegistration = $tripRepository->findATripForRegister($id);

        $tripState = $tripForRegistration->getState();
        $tripMaxRegistrationNumber = $tripForRegistration->getMaxRegistrationNumber();
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();

        if (in_array($user, $tripParticipants))
        {
            $this->addFlash('danger', 'Vous êtes déjà inscrit!');
            $userIsRegisteredForTrip = true;
        } else {
            $userIsRegisteredForTrip = false;
        }

        if (!$tripState === $this->states['opened'])
        {
            $this->addFlash('danger', 'Désolé, l\'inscription n\'est pas encore ouverte');
            $tripIsOpened = false;
        } else {
            $tripIsOpened = true;
        }

        if (count($tripParticipants) >= $tripMaxRegistrationNumber)
        {
            $this->addFlash('danger', 'Désolé, la sortie est déjà complète');
            $tripIsFull = true;
        } else {
            $tripIsFull = false;
        }

        if ( !$userIsRegisteredForTrip AND $tripIsOpened AND !$tripIsFull)
        {
            $tripForRegistration->addParticipant($user);
            $entityManager->persist($tripForRegistration);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes inscrit! ');
        }

        $tripForRegistration = $tripRepository->findATripForRegister($id);
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();
        if (count($tripParticipants) == $tripMaxRegistrationNumber)
        {
            dd(count($tripParticipants));
        }
        //TODO clôturer la sortie si nb max atteint

        return $this->redirectToRoute('main_home');
    }
}
