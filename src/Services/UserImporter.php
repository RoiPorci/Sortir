<?php


namespace App\Services;


use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserImporter
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $encoder;
    /**
     * @var CampusRepository
     */
    private CampusRepository $campusRepository;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $encoder, CampusRepository $campusRepository)
    {
        $this->userRepository = $userRepository;
        $this->campusRepository = $campusRepository;
        $this->encoder = $encoder;
    }

    public function getAllCampuses(){

    }

    public function createUser(array $infoUser)
    {
        $newUser = new User();

        //Ajout des infos 'simples'
        $newUser
            ->setUsername($infoUser[0])
            ->setLastName($infoUser[1])
            ->setFirstName($infoUser[2])
            ->setPhone($infoUser[3])
            ->setEmail($infoUser[4])
            ->setPassword($this->encoder->encodePassword($newUser, $infoUser[5]))
            ->setActive($infoUser[7])
        ;

        //Ajout du rôle
        if (0 == $infoUser[6])
        {
            $newUser->setRoles(['ROLE_PARTICIPANT']);
        }
        else {
            $newUser->setRoles(['ROLE_ADMIN']);
        }

        //Ajout campus



        //Ajout des attributs nécessaires
        $newUser->setDateCreated(new \DateTime());

    }

}