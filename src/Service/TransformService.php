<?php
namespace App\Service;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\Element;
use App\Entity\Entreprise;
use App\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TransformService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAdresse(array $data): Adresse | null
    {
        $adresseRepository = $this->em->getRepository(Adresse::class);

        if (isset($data['adresse']) && isset($data['adresse']['id'])) {
            $adresse = $adresseRepository->findOneBy(['id' => $data['adresse']['id']]);

            if ($adresse) {
                return $adresse;
            }
        }

        return null;
    }

    public function getDevis(array $data): Devis | null
    {
        $devisRepository = $this->em->getRepository(Devis::class);
        if (isset($data['devis']) && isset($data['devis']['id'])) {
            $devis = $devisRepository->findOneBy(['id' => $data['devis']['id']]);

            if ($devis) {
                return $devis;
            }
        }

        return null;
    }

    public function getElement(array $data): Element | null
    {
        $elementRepository = $this->em->getRepository(Element::class);

        if (isset($data['element']) && isset($data['element']['id'])) {
            $element = $elementRepository->findOneBy(['id' => $data['element']['id']]);

            if ($element) {
                return $element;
            }
        }

        return null;
    }

    public function getEntreprise(array $data): Entreprise | null
    {
        $entrepriseRepository = $this->em->getRepository(Entreprise::class);
        if (isset($data['entreprise']) && isset($data['entreprise']['id'])) {
            $entreprise = $entrepriseRepository->findOneBy(['id' => $data['entreprise']['id']]);

            if ($entreprise) {
                return $entreprise;
            }
        }

        return null;
    }

    public function getClient(array $data): Client | null
    {
        $clientRepository = $this->em->getRepository(Client::class);
        if (isset($data['client']) && isset($data['client']['id'])) {
            $client = $clientRepository->findOneBy(['id' => $data['client']['id']]);

            if ($client) {
                return $client;
            }
        }
        return null;
    }

    public function calculTvaAndTotal(Prestation $prestation): Prestation
    {
        $tvaUnitaire = $prestation->getPrixHT() * $prestation->getTvaPercentage() / 100;
        $totalHT = $prestation->getPrixHT() * $prestation->getQty();
        $tvaTotal = $tvaUnitaire * $prestation->getQty();
        $totalTTC = $totalHT + $tvaTotal;

        $prestation->setTva($tvaTotal);
        $prestation->setTotalHT($totalHT);
        $prestation->setTotalTTC($totalTTC);

        return $prestation;
    }

    public function divideByHundred(Prestation $prestation): Prestation
    {
        if ($prestation->getPrixHT()){
            $prestation->setPrixHT($prestation->getPrixHT() / 100);
        }

        if ($prestation->getTotalTTC()){
            $prestation->setTotalTTC($prestation->getTotalTTC() / 100);
        }

        if ($prestation->getTva()){
            $prestation->setTva($prestation->getTva() / 100);
        }

        if ($prestation->getTotalHT()){
            $prestation->setTotalHT($prestation->getTotalHT() / 100);
        }

        return $prestation;
    }

    public function divideByHundredForDevis(Devis $devis): Devis
    {
        if ($devis->getTotalTTC()){
            $devis->setTotalTTC($devis->getTotalTTC() / 100);
        }

        if ($devis->getTva()){
            $devis->setTva($devis->getTva() / 100);
        }

        if ($devis->getTotalHT()){
            $devis->setTotalHT($devis->getTotalHT() / 100);
        }

        return $devis;
    }

    public function createCsv(array $list, string $fileName): Response
    {
        $fp = fopen('php://temp', 'w');
        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        rewind($fp);
        $response = new Response(stream_get_contents($fp));
        fclose($fp);

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName . '.csv');

        return $response;
    }

    // Export csv des devis
    public function exportDevis(array $devis)
    {
        // Entêtes
        $list = [[
            'id',
            'reference',
            'crée le',
            'mise à jour le',
            'supprimé le',
            'date de validité',
            'payé le',
            'date de début de prestation',
            'client id',
            'total HT',
            'TVA',
            'total TTC',
            'termes & conditions'
        ]];

        // Contenu
        foreach ($devis as $d) {
            $list[] = [
                $d->getId(),
                $d->getReference(),
                $d->getCreatedAt()->format('d/m/Y'),
                $d->getUpdatedAt()? $d->getUpdatedAt()->format('d/m/Y') : null,
                $d->getDeletedAt() ? $d->getDeletedAt()->format('d/m/Y') : null,
                $d->getDateValidite() ? $d->getDateValidite()->format('d/m/Y') : null,
                $d->getPaidAt() ? $d->getPaidAt()->format('d/m/Y') : null,
                $d->getDateDebutPrestation() ? $d->getDateDebutPrestation()->format('d/m/Y') : null,
                $d->getClient()->getId(),
                $d->getTotalHT() ? ($d->getTotalHT() / 100) : null,
                $d->getTva() ? ($d->getTva() / 100) : null,
                $d->getTotalTTC() ? ($d->getTotalTTC() / 100) : null,
                $d->getTc()
            ];
        }

        return $this->createCsv($list, 'Devis_export');
    }

    // Export csv des clients
    public function exportClients(array $clients)
    {
        // Entêtes
        $list = [[
            'id',
            'nom',
            'prenom',
            'email',
            'telephone',
            'numéro',
            'rue',
            'adresse complémentaire',
            'code postal',
            'ville',
            'pays'
        ]];

        // Contenu
        foreach ($clients as $c) {
            $list[] = [
                $c->getId(),
                $c->getNom(),
                $c->getPrenom() ? $c->getPrenom() : null,
                $c->getEmail() ? $c->getEmail() : null,
                $c->getTelephone() ? $c->getTelephone() : null,
                $c->getAdresse()->getNumero() ? $c->getAdresse()->getNumero() : null,
                $c->getAdresse()->getRue() ? $c->getAdresse()->getRue() : null,
                $c->getAdresse()->getComplementaire() ? $c->getAdresse()->getComplementaire() : null,
                $c->getAdresse()->getCp()? $c->getAdresse()->getCp() : null,
                $c->getAdresse()->getVille() ? $c->getAdresse()->getVille() : null,
                $c->getAdresse()->getPays() ? $c->getAdresse()->getPays() : null
            ];
        }

        return $this->createCsv($list, 'Clients_export');
    }

    public function transformDevisDataForPdf(Devis $devis) {
        if (!$devis) {
            return [
                'entrepriseNom' => '',
                'entrepriseAdresseRue' => '',
                'entrepriseAdresseVille' => '',
                'clientNom' => '',
                'clientPrenom' => '',
                'clientAdresseRue' => '',
                'clientAdresseVille' => '',
                'contactClient' => 'Contact non disponible',
                'createdAtDate' => '',
                'updatedAtDate' => '',
                'paidAtDate' => null,
                'debutAtDate' => 'À définir',
                'validite' => '',
                'prixHtCalcule' => 0,
                'tvaCalcule' => 0,
                'totalTTCCalcule' => 0,
                'prestations' => '',
                'tc' => '',
                'paid' => ''
            ];
        }

        $entreprise = $devis->getEntreprise() ?? null;
        $client = $devis->getClient() ?? null;

        $entrepriseContact = $entreprise && $entreprise->getContact() ? $entreprise->getContact() : '';
        $entrepriseAdresseRue = $entreprise && $entreprise->getAdresse() ? AdresseFormatter::stringAdresseRue($entreprise->getAdresse()) : '';
        $entrepriseAdresseVille = $entreprise && $entreprise->getAdresse() ? AdresseFormatter::stringAdresseVille($entreprise->getAdresse()) : '';
        $clientAdresseRue = $client && $client->getAdresse() ? AdresseFormatter::stringAdresseRue($client->getAdresse()) : '';
        $clientAdresseVille = $client && $client->getAdresse() ? AdresseFormatter::stringAdresseVille($client->getAdresse()) : '';
        $contactClient = $client->getEmail() ?? $client->getTelephone() ?? 'Contact non disponible';
        $paid = $devis->getPaidAt() ? 'checked' : '';
        $createdAt = $devis->getCreatedAt()->format('d/m/Y');
        $updatedAt = $devis->getUpdatedAt() ? $devis->getUpdatedAt()->format('d/m/Y') : $createdAt;
        $paidAt = $devis->getPaidAt() ? $devis->getPaidAt()->format('d/m/Y') : null;
        $debutAt = $devis->getDateDebutPrestation() ? $devis->getDateDebutPrestation()->format('d/m/Y') : 'À définir';
        $validite = $devis->getDateValidite() ? $devis->getDateValidite()->format('d/m/Y') : '';
        $prestations = $this->getPrestations($devis->getPrestations());

        return [
            'entrepriseContact' => $entrepriseContact,
            'entrepriseNom' => $entreprise->getNom() ?? '',
            'entrepriseAdresseRue' => $entrepriseAdresseRue,
            'entrepriseAdresseVille' => $entrepriseAdresseVille,
            'clientNom' => $client->getNom() ?? '',
            'clientPrenom' => $client->getPrenom() ?? '',
            'clientAdresseRue' => $clientAdresseRue,
            'clientAdresseVille' => $clientAdresseVille,
            'contactClient' => $contactClient,
            'createdAtDate' => $createdAt ?? '',
            'updatedAtDate' => $updatedAt,
            'paidAtDate' => $paidAt,
            'debutAtDate' => $debutAt,
            'validite' => $validite,
            'prixHtCalcule' => $this->transformPriceToEuro($devis->getTotalHT() ?? 0),
            'tvaCalcule' => $this->transformPriceToEuro($devis->getTva() ?? 0),
            'totalTTCCalcule' => $this->transformPriceToEuro($devis->getTotalTTC() ?? 0),
            'prestations' => $prestations,
            'tc' => $devis->getTc() ?? '',
            'paid' => $paid
        ];

    }

//    public static function formatDate(string $dateString): string
//    {
//        $date = new \DateTime($dateString);
//        return $date->format('d/m/Y');
//    }
//
//    public static function transformDateTimeToDate(string $dateTime): string
//    {
//        $date = new \DateTime($dateTime);
//        return $date->format('Y-m-d');
//    }

    public static function transformPriceToEuro(int $price): string
    {
        return number_format($price / 100, 2, ',', ' ') . ' €';
    }

    public function getPrestations($prestations): array
    {
        $prestationsList = [];
        foreach ($prestations as $prestation) {
            $elementRepository = $this->em->getRepository(Element::class);
            $element = $elementRepository->findOneBy(['id' => $prestation->getElement()->getId()])->getNom();

            $prestationsList[] = [
                'id' => $prestation->getId(),
                'element' => $element,
                'prixHT' => $this->transformPriceToEuro($prestation->getPrixHT()),
                'qty' => $prestation->getQty(),
                'totalHT' => $this->transformPriceToEuro($prestation->getTotalHT()),
                'totalTTC' => $this->transformPriceToEuro($prestation->getTotalTTC()),
                'tvaPercentage' => $prestation->getTvaPercentage(),
                'tva' => $this->transformPriceToEuro($prestation->getTva())
                ];
        }

        return $prestationsList;
    }

}