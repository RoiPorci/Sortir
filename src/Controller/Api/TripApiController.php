<?php

namespace App\Controller\Api;

use App\Repository\StateRepository;
use App\Repository\TripRepository;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripApiController extends AbstractController
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

    const OUVERTE = 'Ouverte';


    /**
     * @Route("/api/trip/publish/{id}", name="api_trip_api", requirements={"id": "\d+"})
     * @param int $id
     * @param TripRepository $tripRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function publishTrip(int $id, TripRepository $tripRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        $trip = $tripRepository->findBy(['organiser' => $user, 'id' => $id])[0];
        $isPublished = false;

        if ($trip){

            if ($trip->getState() == $this->states['created']){

                $trip->setState($this->states['opened']);

                $manager->persist($trip);
                $manager->flush();

                $isPublished = true;
            }
        }

        return new JsonResponse(['isPublished' => $isPublished]);
    }

    /**
     * @Route("/api/trip/register-user/{id}", name="api_trip_registerForATrip", requirements={"id": "\d+"})
     * @param int $id
     * @param TripRepository $tripRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function registerForATrip(
        int $id,
        TripRepository $tripRepository,
        EntityManagerInterface $entityManager
        ): Response
    {
        $user = $this->getUser();
        $tripForRegistration = $tripRepository->findATripForRegister($id);
        $isRegistered = false;

        $tripState = $tripForRegistration->getState();
        $tripMaxRegistrationNumber = $tripForRegistration->getMaxRegistrationNumber();
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();
        $tripLimitDate = $tripForRegistration->getDateLimitForRegistration();

        $now = new \DateTime();

        if ( !in_array($user, $tripParticipants)
            && $tripState === $this->states['opened']
            && count($tripParticipants) < $tripMaxRegistrationNumber
            && $tripLimitDate > $now)
        {
            $tripForRegistration->addParticipant($user);
            $entityManager->persist($tripForRegistration);
            $entityManager->flush();

            $isRegistered = true;
        }

        $tripForRegistration = $tripRepository->findATripForRegister($id);
        $tripParticipants = $tripForRegistration->getParticipants()->toArray();
        $tripParticipantsNumber = count($tripParticipants);

        $isCompleted = false;

        if ($tripParticipantsNumber == $tripMaxRegistrationNumber)
        {
            $tripForRegistration->setState($this->states['completed']);

            $entityManager->persist($tripForRegistration);
            $entityManager->flush();

            $isCompleted = true;
        }

        return new JsonResponse(['isRegistered' => $isRegistered,
                                'tripParticipantsNumber' => $tripParticipantsNumber,
                                'isCompleted' => $isCompleted]);

    }

    /**
     * @Route("/api/trip/cancel-user/{id}", name="api_trip_cancelForATrip", requirements={"id": "\d+"})
     * @param int $id
     * @param TripRepository $tripRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function cancelForATrip(
        int $id,
        TripRepository $tripRepository,
        EntityManagerInterface $entityManager
        ): Response
    {
        $user = $this->getUser();
        $tripForCancel = $tripRepository->findATripForRegister($id);
        $isCanceled = false;

        $tripMaxRegistrationNumber = $tripForCancel->getMaxRegistrationNumber();
        $tripParticipants = $tripForCancel->getParticipants()->toArray();

        if ( in_array($user, $tripParticipants) )
        {
            $tripForCancel->removeParticipant($user);
            $entityManager->persist($tripForCancel);
            $entityManager->flush();

            $isCanceled = true;
        }

        $tripForCancel = $tripRepository->findATripForRegister($id);
        $tripParticipants = $tripForCancel->getParticipants()->toArray();
        $tripParticipantsNumber = count($tripParticipants);

        $isOpened = false;

        if ($tripParticipantsNumber < $tripMaxRegistrationNumber)
        {
            $tripForCancel->setState($this->states['opened']);

            $entityManager->persist($tripForCancel);
            $entityManager->flush();

            $isOpened = true;
        }

        return new JsonResponse(['isCanceled' => $isCanceled,
            'tripParticipantsNumber' => $tripParticipantsNumber,
            'isOpened' => $isOpened]);

    }
    
}
