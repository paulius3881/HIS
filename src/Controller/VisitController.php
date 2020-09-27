<?php

namespace App\Controller;

use App\Repository\UserRepository;
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

    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        VisitRepository $visitRepository,
        UserRepository $userRepository
    )
    {
        $this->visitRepository = $visitRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/users/{userId}/visits", name="get-visits-list", methods={"GET"})
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getVisitsList(int $userId)
    {
        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $visits = [];
        if (!empty($user)) {
            $visits = $user->getVisits();
        } else {
            $response->setStatusCode(404);
        }
        $data = [];
        foreach ($visits as $visit) {

            $data[] =
                [
                    'id' => $visit->getId(),
                    'time' => $visit->getTime(),
                    'serviceId' => $visit->getService()->getId(),
                    'workerId' => $visit->getWorker()->getId(),
                    'clientId' => $visit->getClient()->getId(),
                ];
        }
        if (empty($data)) {
            $response->setStatusCode(404);
        }

        return $response->setData($data);
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}", name="get-visit", methods={"GET"})
     *
     * @param int $visitId
     * @param int $userId
     * @return JsonResponse
     */
    public function getOneVisit(int $visitId, int $userId)
    {
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $response = new JsonResponse();
        if (!empty($visit)) {
            if ($visit->getClient()->getId() == $userId) {
                $response->setData(
                    [
                        'id' => $visit->getId(),
                        'time' => $visit->getTime(),
                        'serviceId' => $visit->getService()->getId(),
                        'workerId' => $visit->getWorker()->getId(),
                        'clientId' => $visit->getClient()->getId(),
                    ]
                );
            } else {
                $response->setStatusCode(404);
            }
        } else {
            $response->setStatusCode(404);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}/visits", name="add-visit", methods={"POST"})
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
     * @Route("/users/{userId}/visits/{visitId}", name="delete-visit", methods={"DELETE"})
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
     * @Route("/users/{userId}/visits/{visitId}", name="update-visit", methods={"PUT"})
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