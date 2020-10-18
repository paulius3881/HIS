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
        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $visits = [];
        if (!empty($user)) {
            $visits = $user->getVisits();
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }

        $data = [];
        foreach ($visits as $visit) {

            $data[] =
                [
                    'id' => $visit->getId(),
                    'time' => $visit->getTime(),
                    'serviceId' => empty($visit->getService()) ? null : $visit->getService()->getId(),
                    'workerId' => empty($visit->getWorker()) ? null : $visit->getWorker()->getId(),
                    'clientId' => empty($visit->getClient()) ? null : $visit->getClient()->getId(),
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
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (empty($user)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }
        if (empty($visit)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                ]);
            return $response;
        } else {
            if ($visit->getClient()->getId() == $userId) {
                $response->setData(
                    [
                        'id' => $visit->getId(),
                        'time' => $visit->getTime(),
                        'serviceId' => empty($visit->getService()) ? null : $visit->getService()->getId(),
                        'workerId' => empty($visit->getWorker()) ? null : $visit->getWorker()->getId(),
                        'clientId' => empty($visit->getClient()) ? null : $visit->getClient()->getId(),
                    ]
                );
            } else {
                $response->setStatusCode(404);
                $response->setData(
                    [
                        "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                    ]);
            }
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
        $isAllDataSet = true;
        $notSetColumns = [];
        $worker = null;
        $service = null;

        if (empty($user)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }

        if (!isset($data['workerId'])) {
            $notSetColumns += ["workerId" => 'Column not set'];
            $isAllDataSet = false;
        } else {
            if (!is_int($data['workerId'])) {
                $notSetColumns += ["workerId" => 'Must be int'];
                $isAllDataSet = false;
            } else {
                $worker = $this->userRepository->findOneBy(['id' => $data['workerId']]);
                if (empty($worker)) {
                    $isAllDataSet = false;
                    $notSetColumns += ["workerId" => 'Worker with id: ' . $data['workerId'] . ' not found'];
                }
            }
        }
        if (!isset($data['serviceId'])) {
            $notSetColumns += ["serviceId" => 'Column not set'];
            $isAllDataSet = false;
        } else {
            if (!is_int($data['serviceId'])) {
                $notSetColumns += ["serviceId" => 'Must be int'];
                $isAllDataSet = false;
            } else {
                $service = $this->serviceRepository->findOneBy(['id' => $data['serviceId']]);
                if (empty($service)) {
                    $isAllDataSet = false;
                    $notSetColumns += ["serviceId" => 'Service with id: ' . $data['serviceId'] . ' not found'];
                }
            }
        }

        if (!isset($data['time'])) {
            $notSetColumns += ["time" => 'Column not set'];
            $isAllDataSet = false;
        }

        if (!$isAllDataSet) {
            $response->setStatusCode(400);
            $response->setData($notSetColumns);
            return $response;
        } else {

            $visit = new Visit();
            $visit->setClient($user);
            $visit->setWorker($worker);
            $visit->setTime($data['time']);
            $visit->setService($service);
            $this->entityManagerInterface->persist($visit);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(200);
            $response->setData(
                [
                    'id' => $visit->getId(),
                    'time' => $visit->getTime(),
                    'serviceId' => empty($visit->getService()) ? null : $visit->getService()->getId(),
                    'workerId' => empty($visit->getWorker()) ? null : $visit->getWorker()->getId(),
                    'clientId' => empty($visit->getClient()) ? null : $visit->getClient()->getId(),
                ]
            );
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
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (empty($user)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }
        if (empty($visit)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                ]);
            return $response;
        }

        $client = $visit->getClient();
        if ($client->getId() == $userId) {
            $this->entityManagerInterface->remove($visit);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(204);
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                ]);
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
    public function updateVisit(Request $request, int $visitId, int $userId)
    {
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $data = json_decode($request->getContent(), true);
        $notSetColumns = [];
        $isAllDataSet = true;
        $worker = null;
        $service = null;

        if (empty($user)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }
        if (empty($visit)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                ]);
            return $response;
        }
        if (!isset($data['workerId']) && !isset($data['serviceId']) && !isset($data['time'])) {
            $response->setData(
                [
                    "message" => "No data"
                ]);
            $response->setStatusCode(400);
            return $response;
        }


        if ($visit->getClient()->getId() == $userId) {

            if (isset($data['workerId'])) {
                if (is_int($data['workerId'])) {
                    $worker = $this->userRepository->findOneBy(['id' => $data['workerId']]);
                    if (empty($worker)) {
                        $notSetColumns += ["workerId" => 'Worker with id: ' . $data['workerId'] . ' not found'];
                        $isAllDataSet = false;
                    }
                } else {
                    $notSetColumns += ["workerId" => 'Must be int'];
                    $isAllDataSet = false;
                }
            }
            if (isset($data['serviceId'])) {
                if (is_int($data['serviceId'])) {
                    $service = $this->serviceRepository->findOneBy(['id' => $data['serviceId']]);
                    if (empty($service)) {
                        $notSetColumns += ["serviceId" => 'Service with id: ' . $data['serviceId'] . ' not found'];
                        $isAllDataSet = false;
                    }
                } else {
                    $notSetColumns += ["serviceId" => 'Must be int'];
                    $isAllDataSet = false;
                }
            }

            if ($isAllDataSet) {
                if (!empty($worker)) {
                    $visit->setWorker($worker);
                }
                if (!empty($service)) {
                    $visit->setService($service);
                }
                if (isset($data['time'])) {
                    $visit->setTime($data['time']);
                }

                $this->entityManagerInterface->persist($visit);
                $this->entityManagerInterface->flush();

                $response->setStatusCode(200);
                $response->setData(
                    [
                        'id' => $visit->getId(),
                        'time' => $visit->getTime(),
                        'serviceId' => empty($visit->getService()) ? null : $visit->getService()->getId(),
                        'workerId' => empty($visit->getWorker()) ? null : $visit->getWorker()->getId(),
                        'clientId' => empty($visit->getClient()) ? null : $visit->getClient()->getId(),
                    ]
                );


            } else {
                $response->setStatusCode(400);
                $response->setData($notSetColumns);
            }

        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " do not have visit with id: " . $visitId
                ]);
        }
        return $response;
    }
}