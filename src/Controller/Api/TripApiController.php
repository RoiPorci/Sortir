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
        $tripLimitDate = $tripForRegistration->getDateLimitForRegistration();

        $now = new \DateTime();

        if ( !in_array($user, $tripParticipants)
            && $tripState === self::OUVERTE
            && count($tripParticipants) < $tripMaxRegistrationNumber
            && $tripLimitDate < $now )
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

            $completed = $stateRepository->findBy(['wording' => 'Clôturée'])[0];

            $tripForRegistration->setState($completed);

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
     */
    public function cancelForATrip($id,
                                     TripRepository $tripRepository,
                                     EntityManagerInterface $entityManager,
                                     StateRepository $stateRepository): Response
    {
        $user = $this->getUser();
        $tripForCancel = $tripRepository->findATripForRegister($id);
        $isCanceled = false;

        $tripState = $tripForCancel->getState()->getWording();
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

            $opened = $stateRepository->findBy(['wording' => 'Ouverte'])[0];

            $tripForCancel->setState($opened);

            $entityManager->persist($tripForCancel);
            $entityManager->flush();

            $isOpened = true;
        }

        return new JsonResponse(['isCanceled' => $isCanceled,
            'tripParticipantsNumber' => $tripParticipantsNumber,
            'isOpened' => $isOpened]);

    }
    
}
