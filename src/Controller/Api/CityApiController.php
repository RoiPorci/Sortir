<?php

namespace App\Controller\Api;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CityApiController extends AbstractController
{
    /**
     * @Route("/api/get-locations-from-{id}", name="api_get_locations_from_city", requirements={"id": "\d+"})
     * @param int $id
     * @param Request $request
     * @param CityRepository $cityRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCityWithLocations(int $id, Request $request, CityRepository $cityRepository, SerializerInterface $serializer): Response
    {
        $city = $cityRepository->findCityWithLocations($id);

        $json = $serializer->serialize($city, 'json', ['groups' => 'city_read']);

        return new JsonResponse($json);
    }
}
