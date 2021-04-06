<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create("fr_FR");

        //Création des Campus
        $campusNames = [
            "Saint-Herblain",
            "Rennes",
            "Quimper",
            "Niort",
            "La Roche-sur-Yon",
            "Angers",
            "Laval",
            "Mans"
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

        //Création de l'utilisateur de test
        $userTest = new User();
        $userTest->setUsername('Batman');
        $userTest->setEmail('bat@gmail.com');
        $userTest->setPassword(password_hash('bat123', PASSWORD_ARGON2ID));

        $userTest->setRoles(['ROLE_USER']);
        $userTest->setDateCreated(new \DateTime());
        $userTest->setPhone('0612345678');
        $userTest->setActive(false);
        $userTest->setFirstName('Wayne');
        $userTest->setLastName('Bruce');
        $userTest->setCampus($campusStHerblain);

        $manager->persist($userTest);

        //Création des utilisateurs bidons

        for ($i = 1; $i <= 100; $i++){
            $newUser = new User();

            $newUser->setUsername($faker->userName);
            $newUser->setEmail($faker->email);
            $newUser->setPassword(password_hash('azerty', PASSWORD_ARGON2ID));
            $newUser->setRoles(['ROLE_USER']);
            $newUser->setDateCreated(new \DateTime());
            $newUser->setPhone('0612345678');
            $newUser->setActive(false);
            $newUser->setFirstName($faker->firstName);
            $newUser->setLastName($faker->lastName);
            $newUser->setCampus($faker->randomElement($campuses));

            $manager->persist($newUser);
        }


        $manager->flush();
    }
}
