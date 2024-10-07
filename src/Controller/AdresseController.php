<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Entreprise;
use App\Repository\AdresseRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AdresseController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private AdresseRepository $adresseRepository;

    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, AdresseRepository $adresseRepository)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->adresseRepository = $adresseRepository;
    }

    #[Route(
        path: '/adresses', name: 'app_adresses_all', defaults: ['_api_resource_class' => Adresse::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $adresses = $this->adresseRepository->findBy(['user' => $user]);

        $json = $serializer->serialize($adresses, 'json', ['groups' => 'adresse:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
    path: '/adresses', name: 'app_adresse_new', defaults: ['_api_resource_class' => Adresse::class,], methods: ['POST'],
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = $request->getContent();

        $adresse = $serializer->deserialize($content, Adresse::class, 'json', ['groups' => 'adresse:write']);
        $adresse->setUser($user);

        $em->persist($adresse);
        $em->flush();

        return new JsonResponse($serializer->serialize($adresse, 'json', ['groups' => 'adresse:read']), 201, [], true);
    }

    #[Route(
        path: '/adresses/{id}', name: 'app_adresse_show', defaults: ['_api_resource_class' => Adresse::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Adresse $adresse): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $adresse = $this->adresseRepository->findOneBy(['id' => $adresse->getId(), 'user'=> $user]);

        if (!$adresse) {
            return new JsonResponse('Cette adresse n\éxiste ppas !', 404);
        }

        if ($adresse->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($adresse, 'json', ['groups' => 'adresse:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/adresses/{id}', name: 'app_adresse_update', defaults: ['_api_resource_class' => Adresse::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Adresse $adresse): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = $request->getContent();

        $adresse = $this->adresseRepository->findOneBy(['id' => $adresse->getId(), 'user'=> $user]);

        if (!$adresse) {
            return new JsonResponse(['error' => 'adresse introuvable'], 404);
        }

        if ($adresse->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $adresse = $serializer->deserialize($content, Adresse::class, 'json', ['groups' => 'adresse:write', 'object_to_populate' => $adresse]);

        $em->persist($adresse);
        $em->flush();

        return new JsonResponse($serializer->serialize($adresse, 'json', ['groups' => 'adresse:read']), 200, [], true);
    }

    #[Route(
        path: '/adresses/{id}', name: 'app_adresse_delete', defaults: ['_api_resource_class' => Adresse::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Adresse $adresse, Entreprise $entreprise = null, Client $client = null): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $adresse = $this->adresseRepository->findOneBy(['id' => $adresse->getId(), 'user'=> $user]);

        if (!$adresse) {
            return new JsonResponse(['error' => 'adresse introuvable'], 404);
        }

        if ($adresse->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $entrepriseRepository = $em->getRepository(Entreprise::class);
        $clientRepository = $em->getRepository(Client::class);
        $entreprise = $entrepriseRepository->findOneBy(['adresse' => $adresse, 'user'=> $user]);
        $client = $clientRepository->findOneBy(['adresse' => $adresse, 'user'=> $user]);

        if ($client ){
            return new JsonResponse(['error' => 'Suppression impossible, adresse utilisée par un client'], 403);
        }

        if ($entreprise) {
            return new JsonResponse(['error' => 'Suppression impossible, adresse utilisée par une entreprise'], 403);
        }

        $em->remove($adresse);
        $em->flush();

        return new JsonResponse('Adresse supprimée !', 202);
    }

}
