<?php

namespace App\Controller\Api;

use App\Repository\TripRepository;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripApiController extends AbstractController
{
    /** @var Updater */
    private Updater $updater;

    private array $states;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
        $this->states = $this->updater->states;
    }

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

        if ($trip)
        {
            if ($trip->getState() == $this->states['created'])
            {
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
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function registerForATrip(int $id, TripRepository $tripRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $trip = $tripRepository->findWithStateAndParticipants($id);
        $isRegistered = false;

        //Infos de la sortie
        $tripState = $trip->getState();
        $tripMaxRegistrationNumber = $trip->getMaxRegistrationNumber();
        $tripParticipants = $trip->getParticipants()->toArray();
        $tripLimitDate = $trip->getDateLimitForRegistration();

        $now = new \DateTime();

        //Traitement
        if ( !in_array($user, $tripParticipants)
            && $tripState === $this->states['opened']
            && count($tripParticipants) < $tripMaxRegistrationNumber
            && $tripLimitDate > $now)
        {
            $trip->addParticipant($user);
            $manager->persist($trip);
            $manager->flush();

            $isRegistered = true;
        }

        //Revérification du nombre de participants après ajout
        $trip = $tripRepository->findWithStateAndParticipants($id);
        $tripParticipants = $trip->getParticipants()->toArray();
        $tripParticipantsNumber = count($tripParticipants);

        $isCompleted = false;

        if ($tripParticipantsNumber == $tripMaxRegistrationNumber)
        {
            $trip->setState($this->states['completed']);

            $manager->persist($trip);
            $manager->flush();

            $isCompleted = true;
        }

        return new JsonResponse(['isRegistered' => $isRegistered,
                                'tripParticipantsNumber' => $tripParticipantsNumber,
                                'isCompleted' => $isCompleted]
        );
    }

    /**
     * @Route("/api/trip/cancel-user/{id}", name="api_trip_cancelForATrip", requirements={"id": "\d+"})
     * @param int $id
     * @param TripRepository $tripRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function cancelForATrip(int $id, TripRepository $tripRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $trip = $tripRepository->findWithStateAndParticipants($id);
        $isCanceled = false;

        //Infos de la sortie
        $tripMaxRegistrationNumber = $trip->getMaxRegistrationNumber();
        $tripParticipants = $trip->getParticipants()->toArray();
        $now = new \DateTime();

        //Traitement
        if ( in_array($user, $tripParticipants)
            && $trip->getDateLimitForRegistration() > $now )
        {
            $trip->removeParticipant($user);
            $manager->persist($trip);
            $manager->flush();

            $isCanceled = true;
        }

        //Revérification du nombre de participants après suppression
        $trip = $tripRepository->findWithStateAndParticipants($id);
        $tripParticipants = $trip->getParticipants()->toArray();
        $tripParticipantsNumber = count($tripParticipants);

        $isOpened = false;

        if ($tripParticipantsNumber < $tripMaxRegistrationNumber
            && $trip->getDateLimitForRegistration() > $now)
        {
            $trip->setState($this->states['opened']);

            $manager->persist($trip);
            $manager->flush();

            $isOpened = true;
        }

        return new JsonResponse(['isCanceled' => $isCanceled,
            'tripParticipantsNumber' => $tripParticipantsNumber,
            'isOpened' => $isOpened]
        );
    }
}
