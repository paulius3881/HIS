<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Response\UserResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_user_")
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
     * @Route("/user-list", name="list", methods={"GET"})
     */
    public function getUsersList()
    {
        return new UserResponse($this->userRepository->findAll());
    }
}