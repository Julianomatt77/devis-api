<?php

namespace App\Controller;

use App\Entity\Element;
use App\Entity\Prestation;
use App\Repository\ElementRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ElementController extends AbstractController
{
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private ElementRepository $elementRepository;

    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, ElementRepository $elementRepository)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->elementRepository = $elementRepository;
    }

    #[Route(
        path: '/api/elements', name: 'app_elements_all', defaults: ['_api_resource_class' => Element::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $adresses = $this->elementRepository->findBy(['user' => $user]);

        $json = $serializer->serialize($adresses, 'json', ['groups' => 'element:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/api/elements', name: 'app_element_new', defaults: ['_api_resource_class' => Element::class,], methods: ['POST'],
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = $request->getContent();

        $element = $serializer->deserialize($content, Element::class, 'json', ['groups' => 'element:write']);
        $element->setUser($user);

        $em->persist($element);
        $em->flush();

        return new JsonResponse($serializer->serialize($element, 'json', ['groups' => 'element:read']), 201, [], true);
    }

    #[Route(
        path: '/api/elements/{id}', name: 'app_element_show', defaults: ['_api_resource_class' => Element::class,], methods: ['GET'],
    )]
    public function show(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Element $element): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $element = $this->elementRepository->findOneBy(['id' => $element->getId(), 'user'=> $user]);

        if (!$element) {
            return new JsonResponse(['error' => 'adresse introuvable'], 404);
        }

        if ($element->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $json = $serializer->serialize($element, 'json', ['groups' => 'element:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/api/elements/{id}', name: 'app_element_update', defaults: ['_api_resource_class' => Element::class,], methods: ['PATCH'],
    )]
    public function edit(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Element $element): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $content = $request->getContent();

        $element = $this->elementRepository->findOneBy(['id' => $element->getId(), 'user'=> $user]);

        if (!$element) {
            return new JsonResponse(['error' => 'adresse introuvable'], 404);
        }

        if ($element->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $element = $serializer->deserialize($content, Element::class, 'json', ['groups' => 'element:write', 'object_to_populate' => $element]);

        $em->persist($element);
        $em->flush();

        return new JsonResponse($serializer->serialize($element, 'json', ['groups' => 'element:read']), 200, [], true);
    }

    #[Route(
        path: '/api/elements/{id}', name: 'app_element_delete', defaults: ['_api_resource_class' => Element::class,], methods: ['DELETE'],
    )]
    public function delete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Element $element): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $element = $this->elementRepository->findOneBy(['id' => $element->getId(), 'user'=> $user]);

        if (!$element) {
            return new JsonResponse(['error' => 'adresse introuvable'], 404);
        }

        if ($element->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $prestationRepository = $em->getRepository(Prestation::class);
        $prestation = $prestationRepository->findOneBy(['element' => $element, 'user'=> $user]);

        if ($prestation){
            return new JsonResponse(['error' => 'Suppression impossible, element utilisé par une prestation'], 403);
        }

        $em->remove($element);
        $em->flush();

        return new JsonResponse('Element supprimé', 202);
    }

}
