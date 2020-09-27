<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Response\UserResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class UserController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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
     */
    public function addUser()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/users/{userId}", name="delete-user", methods={"DELETE"})
     */
    public function deleteUser()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }

    /**
     * @Route("/users/{userId}", name="update-user", methods={"PUT"})
     */
    public function updateUser()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
    }
}