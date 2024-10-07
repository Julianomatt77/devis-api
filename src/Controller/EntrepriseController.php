<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\Entreprise;
use App\Repository\ClientRepository;
use App\Repository\EntrepriseRepository;
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

class EntrepriseController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private EntrepriseRepository $entrepriseRepository;
    private TransformService $transformService;

    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, EntrepriseRepository $entrepriseRepository, TransformService $transformService)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->entrepriseRepository = $entrepriseRepository;
        $this->transformService = $transformService;
    }

    #[Route(
        path: '/entreprises', name: 'app_entreprises_all', defaults: ['_api_resource_class' => Client::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $clients = $this->entrepriseRepository->findBy(['user' => $user]);

        $json = $serializer->serialize($clients, 'json', ['groups' => ['entreprise:read', 'client:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/entreprises', name: 'app_entreprise_new', defaults: ['_api_resource_class' => Entreprise::class,], methods: ['POST'],
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

        $entreprise = $serializer->deserialize(json_encode($content), Entreprise::class, 'json', ['groups' => 'entreprise:write']);
        $entreprise->setUser($user);
        if ($adresse) {
            $entreprise->setAdresse($adresse);
        }

        $em->persist($entreprise);
        $em->flush();

        return new JsonResponse($serializer->serialize($entreprise, 'json', ['groups' => ['entreprise:read', "client:read"]]), 201, [], true);
    }

    #[Route(
        path: '/entreprises/{id}', name: 'app_entreprise_show', defaults: ['_api_resource_class' => Client::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, Entreprise $entreprise): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $entreprise = $this->entrepriseRepository->findOneBy(['id' => $entreprise->getId(), 'user'=> $user]);

        if (!$entreprise) {
            return new JsonResponse(['error' => 'entreprise introuvable'], 404);
        }

        if ($entreprise->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($entreprise, 'json', ['groups' => ['entreprise:read','client:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/entreprises/{id}', name: 'app_entreprise_update', defaults: ['_api_resource_class' => Entreprise::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Entreprise $entreprise): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $entreprise = $this->entrepriseRepository->findOneBy(['id' => $entreprise->getId(), 'user'=> $user]);

        if (!$entreprise) {
            return new JsonResponse(['error' => 'Entreprise introuvable'], 404);
        }

        if ($entreprise->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $adresse = $this->transformService->getAdresse($content);
        if (isset($content['adresse']) && !$adresse) {
            return new JsonResponse(['error' => 'Adresse introuvable'], 404);
        }
        if ($adresse) {
            unset($content['adresse']);
        }

        $entreprise = $serializer->deserialize(json_encode($content), Entreprise::class, 'json', ['groups' => 'entreprise:write', 'object_to_populate' => $entreprise]);

        if ($adresse) {
            $entreprise->setAdresse($adresse);
        }

        $em->persist($entreprise);
        $em->flush();

        return new JsonResponse($serializer->serialize($entreprise, 'json', ['groups' => 'entreprise:read', 'client:read']), 200, [], true);
    }

    #[Route(
        path: '/entreprises/{id}', name: 'app_entreprise_delete', defaults: ['_api_resource_class' => Entreprise::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Entreprise $entreprise): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $entreprise = $this->entrepriseRepository->findOneBy(['id' => $entreprise->getId(), 'user'=> $user]);

        if (!$entreprise) {
            return new JsonResponse(['error' => 'Entreprise introuvable'], 404);
        }

        if ($entreprise->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $devisRepository = $em->getRepository(Devis::class);
        $devis = $devisRepository->findOneBy(['entreprise' => $entreprise, 'user'=> $user]);

        if ($devis) {
            return new JsonResponse(['error' => 'Suppression impossible, entreprise utilisé par un devis'], 403);
        }

        $em->remove($entreprise);
        $em->flush();

        return new JsonResponse(['message' => 'Entreprise supprimée avec succès'], 202);
    }
}
