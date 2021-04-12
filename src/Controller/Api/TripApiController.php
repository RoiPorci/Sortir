<?php

namespace App\Controller\Api;

use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripApiController extends AbstractController
{
    const OUVERTE = 'Ouverte';


    /**
     * @Route("/api/trip/publish/{id}", name="api_trip_api", requirements={"id": "\d+"})
     * @param int $id
     * @param TripRepository $tripRepository
     * @param StateRepository $stateRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function publishTrip(int $id, TripRepository $tripRepository, StateRepository $stateRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $trip = $tripRepository->findBy(['organiser' => $user, 'id' => $id])[0];
        $isPublished = false;

        if ($trip){
            $created = $stateRepository->findBy(['wording' => 'Créée'])[0];

            if ($trip->getState() == $created){
                $opened = $stateRepository->findBy(['wording' => 'Ouverte'])[0];

                $trip->setState($opened);

                $manager->persist($trip);
                $manager->flush();

                $isPublished = true;
            }
        }

        return new JsonResponse(['isPublished' => $isPublished]);
    }

    /**
     * @Route("/api/trip/register-user/{id}", name="api_trip_registerForATrip", requirements={"id": "\d+"})
     */
    public function registerForATrip($id,
                                     TripRepository $tripRepository,
                                     EntityManagerInterface $entityManager,
                                     StateRepository $stateRepository): Response
    {
        $user = $this->getUser();
        $tripForRegistration = $tripRepository->findATripForRegister($id);
        $isRegistered = false;

        $tripState = $tripForRegistration->getState()->getWording();
        $tripMaxRegistrationNumber = $tripForRegistration->getMaxRegistrationNumber();
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();

        if ( !in_array($user, $tripParticipants)
            AND $tripState === self::OUVERTE
            AND count($tripParticipants) < $tripMaxRegistrationNumber)
        {
            $tripForRegistration->addParticipant($user);
            $entityManager->persist($tripForRegistration);
            $entityManager->flush();

            $isRegistered = true;
        }

        $tripForRegistration = $tripRepository->findATripForRegister($id);
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();
        $tripParticipantsNumber = count($tripParticipants);
        if ($tripParticipantsNumber == $tripMaxRegistrationNumber)
        {
            $completed = $stateRepository->findBy(['wording' => 'Clôturée'])[0];

            $tripForRegistration->setState($completed);

            $entityManager->persist($tripForRegistration);
            $entityManager->flush();
        }

        return new JsonResponse(['isRegistered' => $isRegistered, 'tripParticipantsNumber' => $tripParticipantsNumber]);

    }
    
}
