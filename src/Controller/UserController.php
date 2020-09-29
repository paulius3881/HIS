<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;
use App\Response\UserResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class UserController extends AbstractController
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
     * @Route("/users", name="get-users-list", methods={"GET"})
     */
    public function getUsersList()
    {
        return new UserResponse($this->userRepository->findAll());
    }

    /**
     * @Route("/users/{userId}", name="get-user", methods={"GET"})
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getOneUser(int $userId)
    {
        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!empty($user)) {
            $response->setData(
                [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'dateOfBirth' => $user->getDateOfBirth(),
                    'role' => $user->getRole(),
                ]
            );
        } else {
            $response->setStatusCode(404);
        }

        return $response;
    }

    /**
     * @Route("/users", name="add-user", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addUser(Request $request)
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);

        if (isset($data['name']) && isset($data['surname']) && isset($data['dateOfBirth']) && isset($data['role'])) {
            $user = new User();
            $user->setName($data['name']);
            $user->setSurname($data['surname']);
            $user->setDateOfBirth($data['dateOfBirth']);
            $user->setRole($data['role']);
            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(201);
        } else {
            $response->setStatusCode(400);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}", name="delete-user", methods={"DELETE"})
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function deleteUser(int $userId)
    {
        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!empty($user)) {
            $this->entityManagerInterface->remove($user);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(200);
        } else {
            $response->setStatusCode(404);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}", name="update-user", methods={"PUT"})
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function updateUser(Request $request, int $userId)
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (isset($data['name']) || isset($data['surname']) || isset($data['dateOfBirth']) || isset($data['role'])) {
            $response->setStatusCode(200);
        }else{
            $response->setStatusCode(400);

            return $response;
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['surname'])) {
            $user->setSurname($data['surname']);
        }
        if (isset($data['dateOfBirth'])) {
            $user->setDateOfBirth($data['dateOfBirth']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        $this->entityManagerInterface->persist($user);
        $this->entityManagerInterface->flush();
        $response->setStatusCode(200);

        return $response;
    }
}