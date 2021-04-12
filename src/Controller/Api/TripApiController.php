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
}
