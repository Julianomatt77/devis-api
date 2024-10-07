<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PrestationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
#[ApiResource]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Element $element = null;

    #[ORM\Column]
    private ?int $qty = null;

    #[ORM\Column]
    private ?int $prixHT = null;

    #[ORM\Column]
    private ?int $tvaPercentage = null;

    #[ORM\Column]
    private ?int $tva = null;

    #[ORM\Column]
    private ?int $totalTTC = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    private ?Devis $devis = null;

    #[ORM\Column]
    private ?int $totalHT = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElement(): ?Element
    {
        return $this->element;
    }

    public function setElement(?Element $element): static
    {
        $this->element = $element;

        return $this;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(int $qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function getPrixHT(): ?int
    {
        return $this->prixHT;
    }

    public function setPrixHT(int $prixHT): static
    {
        $this->prixHT = $prixHT;

        return $this;
    }

    public function getTvaPercentage(): ?int
    {
        return $this->tvaPercentage;
    }

    public function setTvaPercentage(int $tvaPercentage): static
    {
        $this->tvaPercentage = $tvaPercentage;

        return $this;
    }

    public function getTva(): ?int
    {
        return $this->tva;
    }

    public function setTva(int $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    public function getTotalTTC(): ?int
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(int $totalTTC): static
    {
        $this->totalTTC = $totalTTC;

        return $this;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

        return $this;
    }

    public function getTotalHT(): ?int
    {
        return $this->totalHT;
    }

    public function setTotalHT(int $totalHT): static
    {
        $this->totalHT = $totalHT;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
