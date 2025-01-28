<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Repository\DevisRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use App\Service\DataService;
use App\Service\TransformService;
use Doctrine\ORM\EntityManagerInterface;
use Spiritix\Html2Pdf\Converter;
use Spiritix\Html2Pdf\Input\StringInput;
use Spiritix\Html2Pdf\Input\UrlInput;
use Spiritix\Html2Pdf\Output\DownloadOutput;
use Spiritix\Html2Pdf\Output\FileOutput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
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
    private DataService $dataService;

    /**
     * @param UserRepository $userRepository
     * @param AnnuaireService $annuaire
     * @param TransformService $transformService
     * @param DevisRepository $devisRepository
     */
    public function __construct(UserRepository $userRepository, AnnuaireService $annuaire, TransformService $transformService, DevisRepository $devisRepository, DataService $dataService)
    {
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->transformService = $transformService;
        $this->devisRepository = $devisRepository;
        $this->dataService = $dataService;
    }

    #[Route(
        path: '/api/devis', name: 'app_devis_all', defaults: ['_api_resource_class' => Devis::class,], methods: ['GET'],
    )]
    public function index(Request $request, SerializerInterface $serializer): JSONResponse
    {
        $user = $this->annuaire->getUser($request);
        $devis = $this->devisRepository->findBy(['user' => $user], ['id' => 'desc']);

        $json = $serializer->serialize($devis, 'json', ['groups' => 'devis:read']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route(
        path: '/api/devis', name: 'app_devis_new', defaults: ['_api_resource_class' => Devis::class,], methods: ['POST'],
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
        path: '/api/devis/{id}', name: 'app_devis_show', defaults: ['_api_resource_class' => Devis::class,], methods: ['GET'],
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
        path: '/api/devis/{id}', name: 'app_devis_update', defaults: ['_api_resource_class' => Devis::class,], methods: ['PATCH'],
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

        $devis = $this->dataService->updateDevis($devis, $user);

        $em->persist($devis);
        $em->flush();

        return new JsonResponse($serializer->serialize($devis, 'json', ['groups' => 'devis:read']), 200, [], true);
    }

    #[Route(
        path: '/api/devis/{id}', name: 'app_devis_delete', defaults: ['_api_resource_class' => Devis::class,], methods: ['DELETE'],
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

    // Export csv des devis
    #[Route('/api/export/devis', name: 'app_devis_export')]
    public  function export(Request $request,): Response
    {
        $user = $this->annuaire->getUser($request);
        $devis = $this->devisRepository->findBy(['user' => $user]);

        $response = $this->transformService->exportDevis($devis);

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        return $response;
    }

    #[Route(
        path: '/api/devis/{id}/export', name: 'app_devis_export_pdf', defaults: ['_api_resource_class' => Devis::class,], methods: ['GET'],
    )]
    public function export_pdf(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Devis $devis): Response
    {
        $user = $this->annuaire->getUser($request);
        $devis = $this->devisRepository->findOneBy(['id' => $devis->getId(), 'user'=> $user]);

        if (!$devis) {
            return new JsonResponse(['error' => 'Devis introuvable'], 404);
        }

        if ($devis->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], 403);
        }

        $dataTransformed = $this->transformService->transformDevisDataForPdf($devis);

        $html = $this->renderView('devis_template_pdf.html.twig', [
            'devis' => $devis,
            'data' => $dataTransformed
        ]);

        $tempDir = $this->getParameter('kernel.project_dir') . '/var/pdf';
        $filesystem = new Filesystem();

        if (!$filesystem->exists($tempDir)) {
            $filesystem->mkdir($tempDir, 0755);
        }

        $filename = $tempDir . '/devis-' . $devis->getReference() . '.html';
        file_put_contents($filename, $html);

        $this->download_pdf($devis->getReference());

        return new JsonResponse([
            'message' => 'HTML exporté avec succès.' . $devis->getReference() . '/download',
            'link' => $this->getParameter('app.base_url') . '/devis/' . $devis->getReference() . '/download',

        ], 200);
    }

    #[Route(
        path: '/devis/{reference}/download', name: 'app_devis_download_pdf', methods: ['GET'],
    )]
    public function download_pdf(string $reference): Response
    {
        $tempDir = $this->getParameter('kernel.project_dir') . '/var/pdf';
        $htmlFilePath = $tempDir . '/devis-' . $reference . '.html';

        if (!file_exists($htmlFilePath)) {
            return new JsonResponse(['error' => 'Fichier HTML introuvable'], 404);
        }

        $htmlContent = file_get_contents($htmlFilePath);
        if ($htmlContent === false) {
            return new JsonResponse(['error' => 'Impossible de lire le fichier HTML'], 500);
        }

        $input = new StringInput();
        $input->setHtml($htmlContent);

        $output = new DownloadOutput();
        $converter = new Converter($input, $output);

        $converter->setOptions([
            'printBackground' => true,
            'displayHeaderFooter' => false,
            'format' => 'A4',
            'disable-pdf-compression' => true,
            'scale' => 1.2,
            'dpi' => 300,
        ]);

//        $chromePath = $this->getParameter('kernel.project_dir') . '/var/google-chrome-stable';
        $chromePath = '/usr/bin/google-chrome-stable';
        $converter->setLaunchOptions([
            'ignoreHTTPSErrors' => true,
            'headless' => true,
            'executablePath' => $chromePath,
            'args' => [
                '--no-sandbox',
                '--disable-web-security',
            ],
        ]);

        $output = $converter->convert();
        $output->download("devis-" . $reference . ".pdf");
        exit;

//
//        return new Response($pdfContent, 200, [
//            'Content-Type' => 'application/pdf',
//            'Content-Disposition' => 'attachment; filename="devis-' . $id . '.pdf"',
//        ]);
    }
}
