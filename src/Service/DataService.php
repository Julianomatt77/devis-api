<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\User;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class DataService
{
    private AnnuaireService $annuaire;
    private EntityManagerInterface $em;
    private DevisRepository $devisRepository;
    private SerializerInterface $serializer;

    /**
     * @param AnnuaireService $annuaire
     * @param EntityManagerInterface $em
     * @param DevisRepository $devisRepository
     */
    public function __construct(AnnuaireService $annuaire, EntityManagerInterface $em, DevisRepository $devisRepository, SerializerInterface $serializer)
    {
        $this->annuaire = $annuaire;
        $this->em = $em;
        $this->devisRepository = $devisRepository;
        $this->serializer = $serializer;
    }


    public function updateDevis(Devis $devis, User $user){
        $devis = $this->devisRepository->findOneBy(['id' => $devis->getId(), 'user' => $user]);

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

        return $devis;
    }

}