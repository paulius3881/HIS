<?php

namespace App\Controller;

use App\Repository\VisitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class VisitController extends AbstractController
{
    /** @var VisitRepository */
    private $visitRepository;

    public function __construct(
        VisitRepository $visitRepository
    )
    {
        $this->visitRepository = $visitRepository;
    }

    /**
     * @Route("/visits", name="get-visits-list", methods={"GET"})
     */
    public function getVisitsList()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/visits/{visitId}", name="get-visit", methods={"GET"})
     */
    public function getOneVisit()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/visits", name="add-visit", methods={"POST"})
     */
    public function addVisit()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/visits/{visitId}", name="delete-visit", methods={"DELETE"})
     */
    public function deleteVisit()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/visits/{visitId}", name="update-visit", methods={"PUT"})
     */
    public function updateVisit()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }
}