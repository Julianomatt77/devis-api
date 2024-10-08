<?php
namespace App\Service;

use App\Entity\Adresse;
use App\Entity\Devis;
use App\Entity\Element;
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

    public function calculTvaAndTotal(Prestation $prestation)
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

}