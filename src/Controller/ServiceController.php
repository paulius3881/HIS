<?php

namespace App\Controller;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ServiceController extends AbstractController
{
    /** @var ServiceRepository */
    private $serviceRepository;

    public function __construct(
        ServiceRepository $serviceRepository
    )
    {
        $this->serviceRepository = $serviceRepository;
    }


    /**
     * @Route("/services", name="get-services-list", methods={"GET"})
     */
    public function getServicesList()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/services/{serviceId}", name="get-service", methods={"GET"})
     */
    public function getOneService()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/services", name="add-service", methods={"POST"})
     */
    public function addService()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/services/{serviceId}", name="delete-service", methods={"DELETE"})
     */
    public function deleteService()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/services/{serviceId}", name="update-service", methods={"PUT"})
     */
    public function updateService()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }
}
