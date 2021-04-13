<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\TripType;
use App\Repository\CampusRepository;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TripController extends AbstractController
{
    /**
     * @var Updater
     */
    private Updater $updater;

    private array $states;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
        $this->states = $this->updater->states;
    }

    /**
     * @Route("/trip/{id}", name="trip_getDetail")
     * @param $id
     * @param TripRepository $tripRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function getDetail(int $id, TripRepository $tripRepository): Response
    {
        $tripDetail = $tripRepository->findATrip($id);

        return $this->render('trip/detail.html.twig', [
            'tripDetail' => $tripDetail
        ]);
    }

    /**
     * @Route("/create", name="trip_create")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $button = $form->getClickedButton()->getName();
            if ($button == 'create') {
                $state = $this->states['created'];
            } else {
                $state = $this->states['opened'];
            }

            $user = $this->getUser();

            $trip->setState($state);
            $trip->setOrganiser($user);
            $trip->setOrganiserCampus($user->getCampus());

            $entityManager->persist($trip);
            $entityManager->flush();

            return $this->redirectToRoute('trip_getDetail', ['id' => $trip->getId()]);
        }

        $location = $trip->getLocation();

        return $this->render('trip/createTrip.html.twig', [
            'tripForm' => $form->createView(),
            'location' => $location
        ]);
    }

    /**
     * @Route("/modify-{id}", name="trip_modify")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function modify(int $id, Request $request, TripRepository $tripRepository, EntityManagerInterface $entityManager): Response
    {

        $trip = $tripRepository->findTripWithoutParticipants($id);
        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        $location = $trip->getLocation();

        return $this->render('trip/modifyTrip.html.twig', [
            'tripForm' => $form->createView(),
            'location' => $location,
        ]);
    }
}
