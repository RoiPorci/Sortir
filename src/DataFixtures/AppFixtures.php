<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create("fr_FR");

        //Création des campus
        $campusNames = [
            "Saint-Herblain",
            "La Roche-sur-Yon",
            "Rennes"
        ];

        foreach ($campusNames as $campusName){
            $newCampus = new Campus();
            $newCampus->setName($campusName);
            $manager->persist($newCampus);
        }

        $manager->flush();

        //Récupération des campus
        $campusRepository = $manager->getRepository(Campus::class);

        $campuses = $campusRepository->findAll();
        $campusStHerblain = $campusRepository->findOneBy(['name' => 'Saint-Herblain']);

        ///////////////////////////////////////////////////////////////////////////////////
        //Création des Etats
        $stateNames = [
          "Créée",
          "Ouverte",
          "Clôturée",
          "Activité en cours",
          "Passée",
          "Annulée"
        ];

        foreach ($stateNames as $stateName){
            $newState = new State();
            $newState->setWording($stateName);
            $manager->persist($newState);
        }

        $manager->flush();

        //Récupération des états
        $stateRepository = $manager->getRepository(State::class);
        $stateOpened = $stateRepository->findOneBy(['wording' => 'Ouverte']);
        $stateFinished = $stateRepository->findOneBy(['wording' => 'Passée']);
        $stateClotured = $stateRepository->findOneBy(['wording' => 'Clôturée']);

        ///////////////////////////////////////////////////////////////////////////////////
        //Création des Villes
        $cityInfos = [
            ["Nantes", "44000"],
            ["La Roche-sur-Yon", "85000"],
            ["Rennes", "35000"],
            ["Coupvray", "77700"],
        ];

        foreach ($cityInfos as $cityInfo){
            $newCity = new City();
            $newCity->setName($cityInfo[0]);
            $newCity->setZipCode($cityInfo[1]);
            $manager->persist($newCity);
        }

        $manager->flush();

        //Récupération des Villes
        $cityRepository = $manager->getRepository(City::class);
        $cities = $cityRepository->findAll();

        ///////////////////////////////////////////////////////////////////////////////////
        //Création des Lieux
        for ($i = 0; $i < 30; $i++){
            $newLocation = new Location();

            $newLocation->setName($faker->sentence());
            $newLocation->setStreet($faker->streetName());
            $newLocation->setLatitude($faker->latitude());
            $newLocation->setLongitude($faker->longitude());
            $newLocation->setCity($faker->randomElement($cities));

            $manager->persist($newLocation);
        }

        $manager->flush();

        //Récupération des Lieux
        $locationRepository = $manager->getRepository(Location::class);
        $locations = $locationRepository->findAll();

        ///////////////////////////////////////////////////////////////////////////////////
        //Création de l'utilisateur de test
        $userTest = new User();
        $userTest->setUsername('Batman');
        $userTest->setEmail('bat@gmail.com');
        $userTest->setPassword(password_hash('bat123', PASSWORD_ARGON2ID));

        $userTest->setRoles(['ROLE_PARTICIPANT']);
        $userTest->setDateCreated(new \DateTime());
        $userTest->setPhone('0612345678');
        $userTest->setActive(true);
        $userTest->setFirstName('Wayne');
        $userTest->setLastName('Bruce');
        $userTest->setCampus($campusStHerblain);

        $manager->persist($userTest);

        //Création des utilisateurs bidons

        for ($i = 1; $i <= 100; $i++){
            $newUser = new User();

            $newUser->setUsername($faker->userName());
            $newUser->setEmail($faker->email());
            $newUser->setPassword(password_hash('azerty', PASSWORD_ARGON2ID));
            $newUser->setRoles(['ROLE_PARTICIPANT']);
            $newUser->setDateCreated(new \DateTime());
            $newUser->setPhone('0612345678');
            $newUser->setActive(true);
            $newUser->setFirstName($faker->firstName());
            $newUser->setLastName($faker->lastName());
            $newUser->setCampus($faker->randomElement($campuses));

            $manager->persist($newUser);
        }

        $manager->flush();

        //Récupération des participants
        $userRepository = $manager->getRepository(User::class);
        $participants = $userRepository->findAll();

        ///////////////////////////////////////////////////////////////////////////////////
        //Création de plusieures sorties pour chaque participant en tant qu'organisateur
        foreach ($participants as $participant){
            $nbOrginasedTrips = $faker->numberBetween(0, 2);

            if ($nbOrginasedTrips > 0) {
                for ($i = 1; $i <= $nbOrginasedTrips; $i++) {
                    $date = $faker->dateTimeBetween('-2 months', '+8 months');
                    $startDate = $date;
                    $endRegisterDate = $date->sub(\DateInterval::createFromDateString('1 week'));
                    $duration = $faker->numberBetween(10, 24 * 60);
                    $now = new \DateTime();

                    //Etablir l'état
                    if ($startDate < $now) {
                        $state = $stateFinished;
                    } elseif ($endRegisterDate < $now) {
                        $state = $stateClotured;
                    } else {
                        $state = $stateOpened;
                    }

                    $organisedTrip = new Trip();

                    $organisedTrip->setName($faker->sentence());
                    $organisedTrip->setDetails($faker->text());
                    $organisedTrip->setState($state);
                    $organisedTrip->setDateTimeStart($startDate);
                    $organisedTrip->setDateLimitForRegistration($endRegisterDate);
                    $organisedTrip->setDuration($duration);
                    $organisedTrip->setOrganiser($participant);
                    $organisedTrip->setOrganiserCampus($participant->getCampus());
                    $organisedTrip->setLocation($faker->randomElement($locations));
                    $organisedTrip->setMaxRegistrationNumber($faker->numberBetween(2, 50));

                    $manager->persist($organisedTrip);
                }
            }
        }

        $manager->flush();

        //Récupération des sorties
        $tripRepository = $manager->getRepository(Trip::class);
        $trips = $tripRepository->findAll();

        ///////////////////////////////////////////////////////////////////////////////////
        //Ajout de participants aux sorties
        foreach ($trips as $trip){
            $nbParticipants = $faker->numberBetween(1, $trip->getMaxRegistrationNumber());
            for ($i = 1; $i <= $nbParticipants; $i++){
                $trip->addParticipant($participants[$faker->numberBetween(0,99)]);
            }
        }

        $manager->flush();
    }
}
