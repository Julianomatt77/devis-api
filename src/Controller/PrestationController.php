<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use App\Service\DataService;
use App\Service\TransformService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PrestationController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private TransformService $transformService;
    private PrestationRepository $prestationRepository;
    private DataService $dataService;

    /**
     * @param UserRepository $userRepository
     * @param AnnuaireService $annuaire
     * @param TransformService $transformService
     * @param PrestationRepository $prestationRepository
     * @param DataService $dataService
     */
    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, TransformService $transformService, PrestationRepository $prestationRepository, DataService $dataService)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->transformService = $transformService;
        $this->prestationRepository = $prestationRepository;
        $this->dataService = $dataService;
    }

    #[Route(
    path: '/api/prestations', name: 'app_prestations_all', defaults: ['_api_resource_class' => Prestation::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $prestations = $this->prestationRepository->findBy(['user' => $user], ['id' => 'desc']);

        $json = $serializer->serialize($prestations, 'json', ['groups' => 'prestation:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
    path: '/api/prestations', name: 'app_prestation_new', defaults: ['_api_resource_class' => Prestation::class,], methods: ['POST'],
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $devis = $this->transformService->getDevis($content);
        if (isset($content['devis']) && !$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }
        if ($devis) {
            unset($content['devis']);
        }

        $element = $this->transformService->getElement($content);
        if (isset($content['element']) && !$element) {
            return new JsonResponse(['error' => 'Element introuvable'], 404);
        }
        if ($element) {
            unset($content['element']);
        }

       $prestation = $serializer->deserialize(json_encode($content), Prestation::class, 'json', ['groups' => 'prestation:write']);
        $prestation->setUser($user);
        $prestation->setDevis($devis);
        $prestation->setElement($element);

        $prestation = $this->transformService->calculTvaAndTotal($prestation);

        $em->persist($prestation);
        $em->flush();

        if ($prestation->getDevis()) {
            $devis = $this->dataService->updateDevis($prestation->getDevis(), $user);
            $em->persist($devis);
            $em->flush();
        }

        return new JsonResponse($serializer->serialize($prestation, 'json', ['groups' => 'prestation:read']), 201, [], true);
    }

    #[Route(
    path: '/api/prestations/{id}', name: 'app_prestation_show', defaults: ['_api_resource_class' => Prestation::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Prestation $prestation): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $prestation = $this->prestationRepository->findOneBy(['id' => $prestation->getId(), 'user'=> $user]);

        if (!$prestation) {
            return new JsonResponse(['error' => 'prestation introuvable'], 404);
        }

        if ($prestation->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($prestation, 'json', ['groups' => 'prestation:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
    path: '/api/prestations/{id}', name: 'app_prestation_update', defaults: ['_api_resource_class' => Prestation::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Prestation $prestation, RouterInterface $router)
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);
        $prestation = $this->prestationRepository->findOneBy(['id' => $prestation->getId(), 'user' => $user]);

        if (!$prestation){
            return new JsonResponse(['error' => 'Prestation introuvable'], 404);
        }

        if ($prestation->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $devis = $this->transformService->getDevis($content);
        if (isset($content['devis']) && !$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }
        if ($devis) {
            unset($content['devis']);
        }

        $element = $this->transformService->getElement($content);
        if (isset($content['element']) && !$element) {
            return new JsonResponse(['error' => 'Element introuvable'], 404);
        }
        if ($element) {
            unset($content['element']);
        }

        $prestation = $serializer->deserialize(json_encode($content), Prestation::class, 'json', ['groups' => 'prestation:write', 'object_to_populate' => $prestation]);

        if ($devis) {
            $prestation->setDevis($devis);
        }

        if ($element) {
            $prestation->setElement($element);
        }

        $prestation = $this->transformService->calculTvaAndTotal($prestation);

        $em->persist($prestation);
        $em->flush();

        if ($prestation->getDevis()) {
            $devis = $this->dataService->updateDevis($prestation->getDevis(), $user);
            $em->persist($devis);
            $em->flush();
        }

        return new JsonResponse($serializer->serialize($prestation, 'json', ['groups' => 'prestation:read']), 200, [], true);
    }

    #[Route(
    path: '/api/prestations/{id}', name: 'app_prestation_delete', defaults: ['_api_resource_class' => Prestation::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Prestation $prestation): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $prestation = $this->prestationRepository->findOneBy(['id' => $prestation->getId(), 'user' => $user]);
        $devis = $prestation->getDevis();

        if (!$prestation) {
            return new JsonResponse(['error' => 'Prestation introuvable'], 404);
        }

        if ($prestation->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $em->remove($prestation);
        $em->flush();

        if ($devis) {
            $devis = $this->dataService->updateDevis($prestation->getDevis(), $user);
            $em->persist($devis);
            $em->flush();
        }

        return new JsonResponse('Prestation supprimée !', 202);
    }
}
