<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Repository\DevisRepository;
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

class DevisController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private TransformService $transformService;
    private DevisRepository $devisRepository;

    /**
     * @param UserRepository $userRepository
     * @param AnnuaireService $annuaire
     * @param TransformService $transformService
     * @param DevisRepository $devisRepository
     */
    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, TransformService $transformService, DevisRepository $devisRepository)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->transformService = $transformService;
        $this->devisRepository = $devisRepository;
    }

    #[Route(
        path: '/devis', name: 'app_devis_all', defaults: ['_api_resource_class' => Devis::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $clients = $this->devisRepository->findBy(['user' => $user]);

        $json = $serializer->serialize($clients, 'json', ['groups' => 'devis:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/devis', name: 'app_devis_new', defaults: ['_api_resource_class' => Devis::class,], methods: ['POST'],
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $entreprise = $this->transformService->getEntreprise($content);
        if (isset($content['entreprise']) && !$entreprise) {
            return new JsonResponse(['error' => 'Entreprise introuvable'], 404);
        }
        if ($entreprise) {
            unset($content['entreprise']);
        }

        $client = $this->transformService->getClient($content);
        if (isset($content['client']) && !$client) {
            return new JsonResponse(['error' => 'Client introuvable'], 404);
        }
        if ($client) {
            unset($content['client']);
        }

        $devis = $serializer->deserialize(json_encode($content), Devis::class, 'json', ['groups' => 'devis:write']);
        $devis->setUser($user);
        $devis->setEntreprise($entreprise);
        $devis->setClient($client);
        $devis->setTva(0);
        $devis->setTotalHT(0);
        $devis->setTotalTTC(0);
        $devis->setCreatedAt(new \DateTimeImmutable());
        $debut = new \DateTime();
        $debut->modify('+1 month');
        $devis->setDateValidite(new \DateTime($debut->format('Y-m-d')));

        $em->persist($devis);
        $em->flush();

        return new JsonResponse($serializer->serialize($devis, 'json', ['groups' => 'devis:read']), 201, [], true);
    }

    #[Route(
        path: '/devis/{id}', name: 'app_devis_show', defaults: ['_api_resource_class' => Devis::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Devis $devis): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $devis = $this->devisRepository->findOneBy(['id' => $devis->getId(), 'user'=> $user]);

        if (!$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }

        if ($devis->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($devis, 'json', ['groups' => 'devis:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/devis/{id}', name: 'app_devis_update', defaults: ['_api_resource_class' => Devis::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Devis $devis): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);
        $devis = $this->devisRepository->findOneBy(['id' => $devis->getId(), 'user' => $user]);

        if (!$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }

        if ($devis->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        if ($content) {
            $entreprise = $this->transformService->getEntreprise($content);
            if (isset($content['entreprise']) && !$entreprise) {
                return new JsonResponse(['error' => 'Entreprise introuvable'], 404);
            }
            if ($entreprise) {
                unset($content['entreprise']);
            }

            $client = $this->transformService->getClient($content);
            if (isset($content['client']) && !$client) {
                return new JsonResponse(['error' => 'Client introuvable'], 404);
            }
            if ($client) {
                unset($content['client']);
            }

            $devis = $serializer->deserialize(json_encode($content), Devis::class, 'json', ['groups' => 'devis:write', 'object_to_populate' => $devis]);

            if ($entreprise){
                $devis->setEntreprise($entreprise);
            }

            if ($client){
                $devis->setClient($client);
            }
        }

        $devis->setUpdatedAt(new \DateTimeImmutable());
        $debut = new \DateTime();
        $debut->modify('+1 month');
        $devis->setDateValidite(new \DateTime($debut->format('Y-m-d')));

        $prestations = $devis->getPrestations();
        $tva = 0;
        $totalHT = 0;
        $totalTTC = 0;
        foreach ($prestations as $prestation) {
            $tva += $prestation->getTva();
            $totalHT += $prestation->getTotalHT();
            $totalTTC += $prestation->getTotalTTC();
        }

        $devis->setTva($tva);
        $devis->setTotalHT($totalHT);
        $devis->setTotalTTC($totalTTC);

        $em->persist($devis);
        $em->flush();

        return new JsonResponse($serializer->serialize($devis, 'json', ['groups' => 'devis:read']), 200, [], true);
    }

    #[Route(
        path: '/devis/{id}', name: 'app_devis_delete', defaults: ['_api_resource_class' => Devis::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,Devis $devis): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $devis = $this->devisRepository->findOneBy(['id' => $devis->getId(), 'user' => $user]);

        if (!$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }

        if ($devis->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $devis->setDeletedAt(new \DateTimeImmutable());

        $em->persist($devis);
        $em->flush();

        return new JsonResponse('Devis supprimée !', 202);
    }
}
