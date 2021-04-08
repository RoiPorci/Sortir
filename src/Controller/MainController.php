<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ListTripType;

use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/{page}", name="main_home", requirements={"page": "\d+"})
     * @param TripRepository $tripRepository
     * @param Request $request
     * @return Response
     */
    public function home(int $page = 1 , TripRepository $tripRepository, Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();

        $maxTrips = 20;

        $filter = $this->createForm(ListTripType::class, null, ['attr' => ['id' => 'filter_form']]);

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

        }
        else {
            $filterParameters = null;
        }

        $results = $tripRepository->findTripsFiltered($filterParameters, $user, $page, $maxTrips);

        $trips = $results['trips'];
        $totalTrips = $results['nbTrips'];
        $totalPages = ceil($totalTrips/$maxTrips);

        return $this->render('main/home.html.twig', [
            'filterForm' => $filter->createView(),
            'trips' => $trips,
            'currentPage' => $page,
            'totalTrips' => $totalTrips,
            'totalPages' => $totalPages,
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
