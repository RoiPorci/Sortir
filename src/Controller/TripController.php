<?php

namespace App\Controller;

use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripController extends AbstractController
{
    /**
     * @Route("/trip/{id}", name="trip_getDetail")
     */
    public function getDetail(Integer $id, TripRepository $tripRepository): Response
    {
        $tripDetail = $tripRepository->find($id);

        return $this->render('trip/detail.html.twig', [
            'tripDetail' => $tripDetail,
        ]);
    }
}
