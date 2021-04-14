<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\CancelTripType;
use App\Form\TripType;
use App\Repository\StateRepository;
use App\Repository\TripRepository;
use App\Services\Updater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $tripDetail = $tripRepository->findNotArchived($id);

        return $this->render('trip/detail.html.twig', [
            'tripDetail' => $tripDetail,
            'states' => $this->states
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

            //Définition du bouton qui a permis l'envoi du formulaire
            $button = $form->getClickedButton()->getName();
            if ($button == 'create') {
                $state = $this->states['created'];
                $flashMessage = 'Votre sortie a bien été créée';
            } else {
                $state = $this->states['opened'];
                $flashMessage = 'Votre sortie a bien été publiée';
            }

            $user = $this->getUser();

            //Traitement
            $trip->setState($state);
            $trip->setOrganiser($user);
            $trip->setOrganiserCampus($user->getCampus());

            $entityManager->persist($trip);
            $entityManager->flush();

            $this->addFlash('success', $flashMessage);

            //Redirection
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
     * @param int $id
     * @param Request $request
     * @param TripRepository $tripRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function modify(int $id, Request $request, TripRepository $tripRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $trip = $tripRepository->findWithoutParticipants($id, $user);

        //Interdiction de la modification de la sortie si celle-ci n'est pas ouverte
        if ($this->states['created'] !== $trip->getState()){
            $this->addFlash('danger', 'Vous ne pouvez plus modifier cette sortie!');
            return $this->redirectToRoute('main_home');
        }

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flashMessage = 'Votre sortie a bien été modifiée';

            //Définition du bouton qui a permis l'envoi du formulaire
            $button = $form->getClickedButton()->getName();
            if ($button == 'opened') {
                $trip->setState($this->states['opened']);
                $flashMessage = 'Votre sortie a bien été modifiée et publiée';
            }

            $manager->persist($trip);
            $manager->flush();

            $this->addFlash('success', $flashMessage);

            //Redirection
            return $this->redirectToRoute('trip_getDetail', ['id' => $trip->getId()]);
        }

        $location = $trip->getLocation();

        return $this->render('trip/modifyTrip.html.twig', [
            'tripForm' => $form->createView(),
            'location' => $location,
            'states' => $this->states,
            'trip' => $trip,
        ]);
    }

    /**
     * @Route("trip/cancel/{id}", name="trip_cancel", requirements={"id"="\d+"})
     * @param int $id
     * @param Request $request
     * @param TripRepository $tripRepository
     * @param StateRepository $stateRepository
     * @param EntityManagerInterface $manager
     * @return Response
     * @throws NonUniqueResultException
     */
    public function cancelTrip(int $id, Request $request, TripRepository $tripRepository, StateRepository $stateRepository, EntityManagerInterface $manager): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $trip = $tripRepository->findNotArchived($id);

        //Vérification de l'existence de la sortie
        if (!$trip) {
            $this->addFlash('warning', "Cette sortie n'existe pas !");
            return $this->redirectToRoute('main_home' );
        }

        $form = $this->createForm(CancelTripType::class, $trip);
        $tripState = $trip->getState();
        $now = new \DateTime();
        $oldDetailTrip = $trip->getDetails();

        //Vérification de la possibilté d'annulation
        if ($trip->getOrganiser() !== $user
            || $trip->getDateTimeStart() < $now
            || $tripState == $this->states['canceled']
            || $tripState == $this->states['created']) {

            $this->addFlash('warning', 'Vous ne pouvez pas annuler cette sortie !');
            return $this->redirectToRoute('trip_getDetail', ['id' => $trip->getId()]);
        }

        $form->handleRequest($request);

        //Traitement
        if ($form->isSubmitted() && $form->isValid()) {
            $cancelDetailTrip = $form['details']->getData();
            $text_cancel = $oldDetailTrip ." Motif d'annulation : " .$cancelDetailTrip;
            $trip->setDetails($text_cancel);

            $state = $stateRepository->findBy(['wording'=> 'Annulée'])[0];
            $trip->setState($state);

            $manager->persist($trip);
            $manager->flush();

            $this->addFlash('success', 'Votre sortie a été annulée');
            return $this->redirectToRoute('trip_getDetail', ['id' => $trip->getId()]);
        }

        return $this->render('trip/cancelTrip.html.twig', [
            'trip' => $trip,
            'formCancelTrip' => $form->createView()
        ]);
    }
}
