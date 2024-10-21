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

}