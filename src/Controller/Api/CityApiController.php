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
     * @Route("/create/api/city", name="api_city")
     * @param Request $request
     * @param CityRepository $cityRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getLocations(Request $request, CityRepository $cityRepository, SerializerInterface $serializer): Response
    {
        $data = json_decode($request->getContent());

        $cityId = $data->cityId;

        $city = $cityRepository->find($cityId);

        $json = $serializer->serialize($city, 'json', ['groups' => 'city_read']);

        return new JsonResponse($json);
    }
}
