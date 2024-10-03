<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private $em;
    private $userRepository;
    private $jwtManager;
    private $tokenStorageInterface;
    private $annuaire;


    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, EntityManagerInterface $manager, UserRepository $userRepository, AnnuaireService $annuaire, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em   = $manager;
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->annuaire = $annuaire;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    //Création d’un utilisateur
    #[\Symfony\Component\Routing\Annotation\Route(
        path: '/register', name: 'api_register', defaults: ['_api_resource_class' => User::class,], methods: ['POST']
    )]
    public function register(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $email    = $data["email"];
        $username    = $data["username"];
        $password = $data["password"];

        //Vérification de l’email
        $checkEmail = $userRepository->findOneBy(['email' => $email]);
        if ($checkEmail) {
            return new JsonResponse([
                "status"  => false,
                "message" => "Cet email existe déjà, vous devez enchoisir un autre !"
            ]);
        }

        $checkUsername = $userRepository->findOneBy(['username' => $username]);
        if ($checkUsername) {
            return new JsonResponse([
                "status"  => false,
                "message" => "Ce nom d'utilisateur existe déjà, vous devez en choisir un autre !"
            ]);
        }

        $user = new User();
        $user->setEmail($email)
            ->setUsername($username)
            ->setRegisteredAt(new \DateTimeImmutable())
            ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
            ->setRoles(["ROLE_USER"]);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse([
            "status"  => true,
            "message" => "L’utilisateur a été créé avec succès !"
        ]);

    }

    #[Route(
        path: '/users-infos', name: 'app_user_show', defaults: ['_api_resource_class' => User::class,], methods: ['GET'],
    )]
    public function show( Request $request, SerializerInterface $serializer): Response
    {
        $connectedUser = $this->annuaire->getUser($request);
        $json = $serializer->serialize($connectedUser, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, 200, [], true);
    }
}
