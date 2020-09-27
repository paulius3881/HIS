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
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $visits = [];
        if (!empty($user)) {
            $visits = $user->getVisits();
        }

        foreach ($visits as $visit) {

            if ($visit->getId() == $visitId) {
                $service = $visit->getService();
                break;
            }
        }

        $response = new JsonResponse();

        if (!empty($service)) {

            $response->setData(
                [
                    'id' => $service->getId(),
                    'price' => $service->getPrice(),
                    'title' => $service->getTitle(),
                ]
            );
        } else {
            $response->setStatusCode(404);
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
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $visits = [];
        if (!empty($user)) {
            $visits = $user->getVisits();
        }

        foreach ($visits as $visit) {

            if ($visit->getId() == $visitId) {
                $service = $visit->getService();
                if (!empty($service)) {
                    if ($service->getId() != $serviceId) {
                        $service = null;
                    }
                }
                break;
            }
        }

        $response = new JsonResponse();

        if (!empty($service)) {

            $response->setData(
                [
                    'id' => $service->getId(),
                    'price' => $service->getPrice(),
                    'title' => $service->getTitle(),
                ]
            );
        } else {
            $response->setStatusCode(404);
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
        $data = json_decode($request->getContent(), true);

        $visit = $this->visitRepository->findOneBy(['id' => $visitId]);

        if (!empty($visit)) {
            if ($visit->getClient()->getId() == $userId) {

                if (!empty($data['price']) && !empty($data['title'])) {
                    if (!is_int($data['price'])) {
                        $response->setData(['errorMessage' => 'Bad parameter']);
                        $response->setStatusCode(400);
                        return $response;
                    } else {
                        $service = new Service();
                        $service->setTitle($data['title']);
                        $service->setPrice($data['price']);
                        $this->entityManagerInterface->persist($service);
                        $this->entityManagerInterface->flush();
                        $visit->setService($service);
                        $this->entityManagerInterface->persist($visit);
                        $this->entityManagerInterface->flush();
                    }
                } else {
                    return $response->setData(['errorMessage' => 'Bad parameter']);
                }
            } else {
                $response->setStatusCode(404);
            }
        } else {
            $response->setStatusCode(404);
        }

        return $response;
    }

    /**
     * @Route("/users/{userId}/visits/{visitId}/services/{serviceId}", name="delete-service", methods={"DELETE"})
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
     * @Route("/users/{userId}/visits/{visitId}/services/{serviceId}", name="update-service", methods={"PUT"})
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
