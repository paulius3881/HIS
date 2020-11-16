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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        if ($this->getUser()->getRole() == "CUSTOMER") {
            $data = [];
            foreach ($this->userRepository->findBy(['role' => 'CLIENT']) as $user) {
                $data[] =
                    [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'name' => $user->getName(),
                        'surname' => $user->getSurname(),
                        'dateOfBirth' => $user->getDateOfBirth(),
                        'role' => $user->getRole(),
                    ];
            }
            $response = new JsonResponse();
            $response->setStatusCode(200);
            $response->setData($data);
            return $response;
        }else if ($this->getUser()->getRole() == "CLIENT") {
            $data = [];
            foreach ($this->userRepository->findBy(['role' => 'CUSTOMER']) as $user) {
                $data[] =
                    [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'name' => $user->getName(),
                        'surname' => $user->getSurname(),
                        'dateOfBirth' => $user->getDateOfBirth(),
                        'role' => $user->getRole(),
                    ];
            }
            $response = new JsonResponse();
            $response->setStatusCode(200);
            $response->setData($data);
            return $response;
        } else {
            return new UserResponse($this->userRepository->findAll());
        }
    }

    /**
     * @Route("/users/{userId}", name="get-user", methods={"GET"})
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getOneUser(int $userId)
    {
        $user = $this->getUser();
        if ($user->getRole() != "ADMIN") {
            $response = new JsonResponse();
            $response->setStatusCode(403);
            $response->setData(
                [
                    "message" => "Access denied. For user with role " . $user->getRole()
                ]);
            return $response;
        }

        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!empty($user)) {
            $response->setData(
                [
                    'id' => $user->getId(),
                    'email'=>$user->getEmail(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'dateOfBirth' => $user->getDateOfBirth(),
                    'role' => $user->getRole(),
                ]
            );
        } else {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
        }

        return $response;
    }

    /**
     * @Route("/users/register", name="add-user", methods={"POST"})
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function addUser(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        $isAllDataSet = true;
        $notSetColumns = [];

        if (!isset($data['name'])) {
            $notSetColumns += ["name" => 'Column not set'];
            $isAllDataSet = false;
        }
        if (!isset($data['surname'])) {
            $notSetColumns += ["surname" => 'Column not set'];
            $isAllDataSet = false;
        }
        if (!isset($data['dateOfBirth'])) {
            $notSetColumns += ["dateOfBirth" => 'Column not set'];
            $isAllDataSet = false;
        }
        if (!isset($data['email'])) {
            $notSetColumns += ["email" => 'Column not set'];
            $isAllDataSet = false;
        }else{
            if(empty($data['email'])){
                $notSetColumns += ["email" => 'Email can not be empty'];
                $isAllDataSet = false;
            }else{
                $user=$this->userRepository->findOneBy(['email'=>$data['email']]);
                if(!empty($user)){
                    $response->setStatusCode(400);
                    $response->setData(["message"=>"User with email: ". $data['email']." already exist"]);
                    return $response;
                }
            }
        }
        if (!isset($data['password'])) {
            $notSetColumns += ["password" => 'Column not set'];
            $isAllDataSet = false;
        }else{
            if(empty($data['password'])){
                $notSetColumns += ["password" => 'Password can not be empty'];
                $isAllDataSet = false;
            }
        }

        if ($isAllDataSet) {
            $user = new User($data['email']);
            $user->setPassword($encoder->encodePassword($user, $data['password']));
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setSurname($data['surname']);
            $user->setDateOfBirth($data['dateOfBirth']);
            $user->setRole("CLIENT");
            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(201);
            $response->setData(
                [
                    'id' => $user->getId(),
                    'email'=>$user->getEmail(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'dateOfBirth' => $user->getDateOfBirth(),
                    'role' => $user->getRole(),
                ]);
        } else {
            $response->setStatusCode(400);
            $response->setData($notSetColumns);
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
        $user = $this->getUser();
        if ($user->getRole() != "ADMIN") {
            $response = new JsonResponse();
            $response->setStatusCode(403);
            $response->setData(
                [
                    "message" => "Access denied. For user with role " . $user->getRole()
                ]);
            return $response;
        }

        $response = new JsonResponse();
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!empty($user)) {
            $this->entityManagerInterface->remove($user);
            $this->entityManagerInterface->flush();
            $response->setStatusCode(204);
        } else {
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            $response->setStatusCode(404);
        }
        return $response;
    }

    /**
     * @Route("/users/{userId}", name="update-user", methods={"PUT"})
     *
     * @param Request $request
     * @param int $userId
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function updateUser(Request $request, int $userId,UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        if ($user->getRole() != "ADMIN") {
            $response = new JsonResponse();
            $response->setStatusCode(403);
            $response->setData(
                [
                    "message" => "Access denied. For user with role " . $user->getRole()
                ]);
            return $response;
        }

        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $notSetColumns = [];
        $isAllDataSet = true;
        $setPassword = true;

        if (empty($user)) {
            $response->setStatusCode(404);
            $response->setData(
                [
                    "message" => "User with id: " . $userId . " not found"
                ]);
            return $response;
        }

        if (isset($data['name']) || isset($data['surname']) || isset($data['dateOfBirth']) || isset($data['role'])) {
            $response->setStatusCode(200);
        } else {
            $response->setData(
                [
                    "message" => "No data"
                ]);
            $response->setStatusCode(400);

            return $response;
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['surname'])) {
            $user->setSurname($data['surname']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }
        if (isset($data['dateOfBirth'])) {
            $user->setDateOfBirth($data['dateOfBirth']);
        }
        if (isset($data['oldPassword'])) {
            if($encoder->isPasswordValid($user, $data['oldPassword'])){
                if (!isset($data['newPassword'])) {
                    $notSetColumns += ["newPassword" => 'Column not set'];
                    $isAllDataSet = false;
                    $setPassword = false;
                }else{
                    if(empty($data['newPassword'])){
                        $notSetColumns += ["newPassword" => 'New password can not be empty'];
                        $isAllDataSet = false;
                        $setPassword = false;
                    }
                }
            }else{
                $notSetColumns += ["newPassword" => 'Password not match with old'];
                $isAllDataSet = false;
                $setPassword = false;
            }
            if($setPassword){
                $user->setPassword($encoder->encodePassword($user, $data['newPassword']));
            }
        }



        if (!$isAllDataSet) {
            $response->setData($notSetColumns);
            $response->setStatusCode(400);

            return $response;
        }

        $this->entityManagerInterface->persist($user);
        $this->entityManagerInterface->flush();
        $response->setStatusCode(200);
        $response->setData(
            [
                'id' => $user->getId(),
                'email'=>$user->getEmail(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'dateOfBirth' => $user->getDateOfBirth(),
                'role' => $user->getRole(),
            ]);

        return $response;
    }

    /**
     * @Route("/activeUser", name="get-active-user", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getActiveUser()
    {
        $response = new JsonResponse();
        if($this->getUser()==null){
            $response->setData(['message'=>'Unauthorized']);
            $response->setStatusCode(401);
            return $response;
        }
        $response->setData(
            [
                'id' => $this->getUser()->getId(),
                'email' => $this->getUser()->getEmail(),
                'name' => $this->getUser()->getName(),
                'surname' => $this->getUser()->getSurname(),
                'dateOfBirth' => $this->getUser()->getDateOfBirth(),
                'role' => $this->getUser()->getRole(),
            ]
        );
        $response->setStatusCode(200);

        return $response;
    }
}