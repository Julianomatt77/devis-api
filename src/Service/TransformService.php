<?php
namespace App\Service;

use App\Entity\Adresse;
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
}