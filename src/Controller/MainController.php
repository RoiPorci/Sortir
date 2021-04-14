<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ListTripType;

use App\Repository\TripRepository;
use App\Services\Updater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /** @var Updater */
    private Updater $updater;
    private array $states;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
        $this->states = $this->updater->states;
    }

    /**
     * @Route("/{page}", name="main_home", requirements={"page": "\d+"})
     * @param TripRepository $tripRepository
     * @param Request $request
     * @return Response
     */
    public function home(int $page = 1, TripRepository $tripRepository, Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();
        $maxResults = 10;

        //On met à jour les états des Sorties
        $this->updater->updateTripsState();

        $filter = $this->createForm(ListTripType::class, null, [
            'action' => $this->generateUrl('main_home'),
            'method' => 'GET'
        ]);

        $filter->handleRequest($request);

        $routeParams = $request->query->all();

        //On récupère les données du filtre
        if (array_key_exists('list_trip', $routeParams)){
            $filterParams = $routeParams['list_trip'];
        }
        else
        {
            $filterParams = null;
        }

        //Appel à la bdd
        $results = $tripRepository->findTripsFiltered($filterParams, $user, $page, $maxResults);

        //Liste des Sorties
        $trips = $results['trips'];

        //Pagination
        $totalTrips = $results['totalTrips'];
        $totalPages = ceil($totalTrips/$maxResults);

        return $this->render('main/home.html.twig', [
            'filterForm' => $filter->createView(),
            'trips' => $trips,
            'states' => $this->states,
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
