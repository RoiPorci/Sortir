<?php

namespace App\Services;

use App\Entity\State;
use App\Entity\Trip;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;

class Updater
{
    /** @var TripRepository */
    private TripRepository $tripRepository;

    /** @var StateRepository */
    private StateRepository $stateRepository;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    public array $states;

    public function __construct(
        TripRepository $tripRepository,
        StateRepository $stateRepository,
        EntityManagerInterface $entityManager)
    {
        $this->tripRepository = $tripRepository;
        $this->stateRepository = $stateRepository;
        $this->entityManager = $entityManager;

        $this->states = $this->getAllStates();
    }

    public function getAllStates(): array
    {
        $statesDb = $this->stateRepository->findAll();
        $statesOrganized = null;

        foreach ($statesDb as $stateDb){
            switch ($stateDb->getWording()){
                case 'Créée':
                    $index = 'created';
                    break;
                case 'Ouverte':
                    $index = 'opened';
                    break;
                case 'Clôturée':
                    $index = 'completed';
                    break;
                case 'Activité en cours':
                    $index = 'ongoing';
                    break;
                case 'Passée':
                    $index = 'past';
                    break;
                case 'Annulée':
                    $index = 'canceled';
                    break;
                default:
                    $index = '';
            }
            $statesOrganized[$index] = $stateDb;
        }

        return $statesOrganized;
    }

    public function updateTripsState()
    {
        $trips = $this->tripRepository->findAllNotArchived();

        foreach ($trips as $trip){
            $this->defineState($trip);
            $this->entityManager->persist($trip);
        }

        $this->entityManager->flush();

        return $trips;
    }

    /**
     * redefine the state of a trip based on its dateTimeStart, duration and dateLimitForRegistration
     * @param Trip $trip
     * @return Trip
     */
    private function defineState(Trip $trip): Trip
    {
        $now = new \DateTime();

        if ($trip->getDateLimitForRegistration() < $now){
        $trip->setState($this->states['completed']);
        }
        if ($trip->getDateTimeStart() < $now) {
            $trip->setState($this->states['ongoing']);
        }
        if ($trip->getDateTimeStart()->modify('+'.$trip->getDuration().' minutes') < $now){
            $trip->setState($this->states['past']);
        }


        return $trip;
    }
}