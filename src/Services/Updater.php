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



    public function __construct(
        TripRepository $tripRepository,
        StateRepository $stateRepository,
        EntityManagerInterface $entityManager)
    {
        $this->tripRepository = $tripRepository;
        $this->stateRepository = $stateRepository;
        $this->entityManager = $entityManager;

        $this->states['past'] = $this->stateRepository->findBy(['wording' => 'Passée'])[0];
        $this->states['completed'] = $this->stateRepository->findBy(['wording' => 'Clôturée'])[0];
        $this->states['ongoing'] = $this->stateRepository->findBy(['wording' => 'Activité en cours'])[0];
        $this->states['opened'] = $this->stateRepository->findBy(['wording' => 'Ouverte'])[0];
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

        if ($trip->getDateTimeStart() < $now) {
            $trip->setState($this->states['past']);
        }
        elseif ($trip->getDateTimeStart()->add(\DateInterval::createFromDateString($trip->getDuration().' minutes')) < $now){
            $trip->setState($this->states['ongoing']);
        }
        elseif ($trip->getDateLimitForRegistration() < $now){
            $trip->setState($this->states['completed']);
        }
        //A décommenter si mauvaises maj sur les Sorties ouvertes
       /* else
        {
            $trip->setState($this->states['opened']);
        }*/

        return $trip;
    }
}