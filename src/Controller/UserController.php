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
     */
    public function getOneUser()
    {
        return new JsonResponse(
            [
                'status' => 'OK'
            ]
        );
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