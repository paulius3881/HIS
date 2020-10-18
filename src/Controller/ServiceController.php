<?php

namespace App\Controller;

use App\Entity\Service;
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
class ServiceController extends AbstractController
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
     * @Route("/users/{userId}/visits/{visitId}/services", name="get-services-list", methods={"GET"})
     *
     * @param int $userId
     * @param int $visitId
     * @return JsonResponse
     */
    public function getServicesList(int $userId, int $visitId)
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
        $service = $visit->getService();
        if (!empty($service)) {
            $response->setStatusCode(200);
            $response->setData(
                [
                    'id' => $service->getId(),
                    'price' => $service->getPrice(),
                    'title' => $service->getTitle(),
                ]
            );
        } else {
            $response->setData([]);
            $response->setStatusCode(200);
        }

        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}/services/{serviceId}", name="get-service", methods={"GET"})
     *
     * @param int $userId
     * @param int $visitId
     * @param int $serviceId
     * @return JsonResponse
     */
    public function getOneService(int $userId, int $visitId, int $serviceId)
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
        $service = $visit->getService();
        if (!empty($service)) {
            if ($service->getId() == $serviceId) {

                $response->setData(
                    [
                        'id' => $service->getId(),
                        'price' => $service->getPrice(),
                        'title' => $service->getTitle(),
                    ]
                );
            } else {
                $response->setStatusCode(404);
                $response->setData(
                    [
                        "message" => "Service with id: " . $serviceId . " not found"
                    ]);
            }
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "Service with id: " . $serviceId . " not found"
                ]);
        }

        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}/services", name="add-service", methods={"POST"})
     *
     * @param Request $request
     * @param int $userId
     * @param int $visitId
     * @return JsonResponse
     */
    public function addService(Request $request, int $userId, int $visitId)
    {
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $data = json_decode($request->getContent(), true);
        $notSetColumns = [];
        $service = null;
        $isAllDataSet = true;

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

        if ($isAllDataSet) {
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
        } else {
            $response->setStatusCode(400);
            $response->setData($notSetColumns);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}/services/{serviceId}", name="delete-service", methods={"DELETE"})
     *
     * @param int $userId
     * @param int $visitId
     * @param int $serviceId
     * @return JsonResponse
     */
    public function deleteService(int $userId, int $visitId, int $serviceId)
    {
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
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

        $service = $visit->getService();
        if (!empty($service)) {
            if ($service->getId() == $serviceId) {

                $visit->setService(null);
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
                return $response;

            } else {
                $response->setStatusCode(404);
                $response->setData(
                    [
                        "message" => "Service with id: " . $serviceId . " not found"
                    ]);
            }
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "Service with id: " . $serviceId . " not found"
                ]);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}/services/{serviceId}", name="update-service", methods={"PUT"})
     *
     * @param Request $request
     * @param int $userId
     * @param int $visitId
     * @param int $serviceId
     * @return JsonResponse
     */
    public function updateService(Request $request, int $userId, int $visitId, int $serviceId)
    {
        $response = new JsonResponse();
        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $data = json_decode($request->getContent(), true);
        $notSetColumns = [];
        $service = null;
        $isAllDataSet = true;

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

        $service = $visit->getService();
        if (!empty($service)) {
            if ($service->getId() == $serviceId) {


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

                if ($isAllDataSet) {
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
                } else {
                    $response->setStatusCode(400);
                    $response->setData($notSetColumns);
                }
                return $response;

            } else {
                $response->setStatusCode(404);
                $response->setData(
                    [
                        "message" => "Service with id: " . $serviceId . " not found"
                    ]);
            }
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "Service with id: " . $serviceId . " not found"
                ]);
        }
        return $response;
    }
}
