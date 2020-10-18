<?php

namespace App\Controller;

use App\Entity\Visit;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class VisitController extends AbstractController
{
    /** @var ServiceRepository */
    private $serviceRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var VisitRepository */
    private $visitRepository;

    /** @var EntityManagerInterface */
    private $entityManagerInterface;

    public function __construct(
        ServiceRepository $serviceRepository,
        UserRepository $userRepository,
        VisitRepository $visitRepository,
        EntityManagerInterface $entityManagerInterface
    )
    {
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
        $this->visitRepository = $visitRepository;
        $this->entityManagerInterface = $entityManagerInterface;
    }

    /**
     * @Route("/users/{userId}/visits", name="get-visits-list", methods={"GET"})
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getVisitsList(int $userId)
    {
        //returnint tuscia masyva
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
                    'serviceId' => 0,//$visit->getService()->getId(),
                    'workerId' => 0,//$visit->getWorker()->getId(),
                    'clientId' => 0,//$visit->getClient()->getId(),
                ];
        }

        if (empty($data)) {
            $response->setStatusCode(200);
            $response->setData();
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
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function addVisit(Request $request, int $userId)
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);

        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (empty($data['workerId']) || empty($data['time'])) {
            return $response->setStatusCode(400);
        }

        if (!is_int($data['workerId'])) {
            return $response->setStatusCode(400);
        }

        $worker = $this->userRepository->findOneBy(['id' => $data['workerId']]);
        if (empty($worker)) {
            return $response->setStatusCode(400);
        }

        if (!empty($user)) {
            $visit = new Visit();
            $visit->setClient($user);
            $visit->setWorker($worker);
            $visit->setTime($data['time']);
            if (isset($data['serviceId'])) {
                $visit->setService($this->serviceRepository->findOneBy(['id' => $data['serviceId']]));
            }

            $this->entityManagerInterface->persist($visit);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(200);
        } else {
            $response->setStatusCode(404);
        }

        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}", name="delete-visit", methods={"DELETE"})
     *
     * @param int $visitId
     * @param int $userId
     * @return JsonResponse
     */
    public function deleteVisit(int $visitId, int $userId)
    {
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $found = false;
        if (!empty($visit)) {
            $client = $visit->getClient();
            if ($client->getId() == $userId) {
                $this->entityManagerInterface->remove($visit);
                $this->entityManagerInterface->flush();
                $found = true;
            }
        }
        if ($found) {
            $response->setStatusCode(200);
        } else {
            $response->setStatusCode(404);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}", name="update-visit", methods={"PUT"})
     *
     * @param Request $request
     * @param int $visitId
     * @param int $userId
     * @return JsonResponse
     */
    public function updateVisit(Request $request,int $visitId, int $userId)
    {
        $response = new JsonResponse();

        if (!isset($data['workerId']) && !isset($data['serviceId']) &&!isset($data['time'])) {
            return $response->setStatusCode(400);
        }

        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $found = false;
        if (!empty($visit)) {
            $client = $visit->getClient();
            if ($client->getId() == $userId) {

                $data = json_decode($request->getContent(), true);

                if(!empty($data['workerId'])){
                    $worker = $this->userRepository->findOneBy(['id' => $data['workerId']]);
                    if (empty($worker)) {
                        return $response->setStatusCode(400);
                    }
                    $visit->setWorker($worker);
                }

                if (isset($data['serviceId'])) {
                    $visit->setService($this->serviceRepository->findOneBy(['id' => $data['serviceId']]));
                }
                if (isset($data['time'])) {
                    $visit->setTime($data['time']);
                }
                $this->entityManagerInterface->persist($visit);
                $this->entityManagerInterface->flush();
                $found = true;
            }
        }
        if ($found) {
            $response->setStatusCode(200);
        } else {
            $response->setStatusCode(404);
        }
        return $response;
    }
}