<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Devis;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use App\Service\TransformService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private ClientRepository $clientRepository;
    private TransformService $transformService;

    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, ClientRepository $clientRepository, TransformService $transformService)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->clientRepository = $clientRepository;
        $this->transformService = $transformService;
    }

    #[Route(
        path: '/clients', name: 'app_clients_all', defaults: ['_api_resource_class' => Client::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $clients = $this->clientRepository->findBy(['user' => $user]);

        $json = $serializer->serialize($clients, 'json', ['groups' => 'client:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/clients', name: 'app_client_new', defaults: ['_api_resource_class' => Client::class,], methods: ['POST'],
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $adresse = $this->transformService->getAdresse($content);
        if (isset($content['adresse']) && !$adresse) {
            return new JsonResponse(['error' => 'Adresse introuvable'], 404);
        }
        if ($adresse) {
            unset($content['adresse']);
        }

        $client = $serializer->deserialize(json_encode($content), Client::class, 'json', ['groups' => 'client:write']);
        $client->setUser($user);

        if ($adresse) {
            $client->setAdresse($adresse);
        }

        $em->persist($client);
        $em->flush();

        return new JsonResponse($serializer->serialize($client, 'json', ['groups' => 'client:read']), 201, [], true);
    }

    #[Route(
        path: '/clients/{id}', name: 'app_client_show', defaults: ['_api_resource_class' => Client::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Client $client): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $client = $this->clientRepository->findOneBy(['id' => $client->getId(), 'user'=> $user]);

        if (!$client) {
            return new JsonResponse(['error' => 'client introuvable'], 404);
        }

        if ($client->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($client, 'json', ['groups' => 'client:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/clients/{id}', name: 'app_client_update', defaults: ['_api_resource_class' => Client::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Client $client): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $client = $this->clientRepository->findOneBy(['id' => $client->getId(), 'user'=> $user]);

        if (!$client) {
            return new JsonResponse(['error' => 'client introuvable'], 404);
        }

        if ($client->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $adresse = $this->transformService->getAdresse($content);
        if (isset($content['adresse']) && !$adresse) {
            return new JsonResponse(['error' => 'Adresse introuvable'], 404);
        }
        if ($adresse) {
            unset($content['adresse']);
        }

        $client = $serializer->deserialize(json_encode($content), Client::class, 'json', ['groups' => 'client:write', 'object_to_populate' => $client]);

        if ($adresse) {
            $client->setAdresse($adresse);
        }

        $em->persist($client);
        $em->flush();

        return new JsonResponse($serializer->serialize($client, 'json', ['groups' => 'client:read']), 200, [], true);
    }

    #[Route(
        path: '/clients/{id}', name: 'app_client_delete', defaults: ['_api_resource_class' => Client::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Client $client, Devis $devis = null): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $client = $this->clientRepository->findOneBy(['id' => $client->getId(), 'user'=> $user]);

        if (!$client) {
            return new JsonResponse(['error' => 'client introuvable'], 404);
        }

        $devisRepository = $em->getRepository(Devis::class);
        $devis = $devisRepository->findOneBy(['client' => $client, 'user'=> $user]);

        if ($devis) {
            return new JsonResponse(['error' => 'Suppression impossible, client utilisé par un devis'], 403);
        }

        if ($client->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $em->remove($client);
        $em->flush();

        return new JsonResponse('Client supprimée !', 202);
    }

}
