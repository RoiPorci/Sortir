<?php


namespace App\Services;


use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private array $campuses;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager,UserRepository $userRepository, UserPasswordEncoderInterface $encoder, CampusRepository $campusRepository)
    {
        $this->userRepository = $userRepository;
        $this->campusRepository = $campusRepository;
        $this->encoder = $encoder;
        $this->campuses = $this->getAllCampuses();
        $this->manager = $manager;
    }

    public function insertUsers(array $infoUsers) {
       array_splice($infoUsers, 0, 1);

       foreach ($infoUsers as $infoUser) {
           $newUser = $this->createUser($infoUser);
           $this->manager->persist($newUser);
       }

       $this->manager->flush();

    }

    public function getAllCampuses(){
        $campusesDb = $this->campusRepository->findAll();
        $CampusesOrganized = null;

        foreach ($campusesDb as $campusDb){
            switch ($campusDb->getName()){
                case 'Saint-Herblain':
                    $index = 'SH';
                    break;
                case 'La Roche-sur-Yon':
                    $index = 'LRSY';
                    break;
                case 'Rennes':
                    $index = 'R';
                    break;
                default:
                    $index = '';
            }
            $campusesOrganized[$index] = $campusDb;
        }

        return $campusesOrganized;

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
        switch ($infoUser[8]){
            case '1':
                $campus = $this->campuses['SH'];
                break;
            case '2':
                $campus = $this->campuses['LRSY'];
                break;
            case '3':
                $campus = $this->campuses['R'];
                break;
            default:
                $campus = $this->campuses['SH'];
        }
        $newUser->setCampus($campus);

        //Ajout des attributs nécessaires
        $newUser->setDateCreated(new \DateTime());

        return $newUser;
    }

}