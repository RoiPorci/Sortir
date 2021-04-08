<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\ListTripType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main_home")
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function home(EntityManagerInterface $manager, Request $request): Response
    {
        $user = $this->getUser();
        $trips = [];

        $filter = $this->createForm(ListTripType::class, $user);

        $filter->handleRequest($request);

        if($filter->isSubmitted() && $filter->isValid()){

            $filterParameters = [
            'campus' => $filter->get('campus')->getData(),
            'name' => $filter->get('name')->getData(),
            'dateStart' => $filter->get('dateStart')->getData(),
            'dateEnd' => $filter->get('dateEnd')->getData(),
            'isOrganiser' => $filter->get('isOrganiser')->getData(),
            'isParticipant' => $filter->get('isParticipant')->getData(),
            'isNotParticipant' => $filter->get('isNotParticipant')->getData(),
            'past' => $filter->get('past')->getData()
            ];

            $tripRepository = $manager->getRepository(Trip::class);
            $trips = $tripRepository->findTripsFiltered($filterParameters, $user);
        }
        else {

        }

        return $this->render('main/home.html.twig', [
            'filterForm' => $filter->createView(),
            'trips' => $trips
        ]);
    }

    /**
     * @Route("/about-us", name="main_about_us")
     */
    public function aboutUs(): Response
    {
        return $this->render('main/about-us.html.twig', [

        ]);
    }

    /**
     * @Route("/cgu", name="main_cgu")
     */
    public function cgu(): Response
    {
        return $this->render('main/cgu.html.twig', [

        ]);
    }

    /**
     * @Route("/legal-notice", name="main_legal_notice")
     */
    public function legalNotice(): Response
    {
        return $this->render('main/legal-notice.html.twig', [

        ]);
    }

}
