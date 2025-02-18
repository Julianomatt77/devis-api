<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Controller\DevisController;
use App\Repository\DevisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DevisRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/devis', name: 'app_devis_all'),
        new Post(uriTemplate: '/api/devis', denormalizationContext: ['groups' => ['devis:write']], name: 'app_devis_new'),
        new Get(uriTemplate: '/api/devis/{id}', denormalizationContext: ['groups' => ['devis:write']], name: 'app_devis_show'),
        new Delete(uriTemplate: '/api/devis/{id}',denormalizationContext: ['groups' => ['devis:write']],name: 'app_devis_delete'),
        new Patch(uriTemplate: '/api/devis/{id}', denormalizationContext: ['groups' => ['devis:write']], name: 'app_devis_update'),
        new Get(
            uriTemplate: '/api/devis/{id}/export',
            openapi: new Operation(
                summary: 'Export a quotation in pdf',
                description: 'This operation export the quotation in PDF format.',),
            denormalizationContext: ['groups' => ['devis:read']],
            name: 'app_devis_export_pdf'),
    ],
    formats: ["json"],
    controller: DevisController::class
)]
class Devis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'prestation:read', 'devis:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'prestation:read', 'devis:read', 'devis:write'])]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['prestation:read', 'devis:read', 'devis:write'])]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'devis')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['prestation:read', 'devis:read', 'devis:write'])]
    private ?Client $client = null;

    /**
     * @var Collection<int, Prestation>
     */
    #[ORM\OneToMany(targetEntity: Prestation::class, mappedBy: 'devis')]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private Collection $prestations;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read', 'devis:write'])]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['client:read','entreprise:read', 'user:read', 'devis:read', 'devis:write'])]
    private ?\DateTimeInterface $dateDebutPrestation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['client:read', 'entreprise:read','user:read', 'devis:read'])]
    private ?\DateTimeInterface $dateValidite = null;

    #[ORM\Column]
    #[Groups(['client:read','entreprise:read', 'user:read', 'devis:read'])]
    private ?int $totalHT = null;

    #[ORM\Column]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private ?int $tva = null;

    #[ORM\Column]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read'])]
    private ?int $totalTTC = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['client:read', 'entreprise:read', 'user:read', 'devis:read', 'devis:write'])]
    private ?string $tc = null;

    public function __construct()
    {
        $this->prestations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Prestation>
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): static
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations->add($prestation);
            $prestation->setDevis($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): static
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getDevis() === $this) {
                $prestation->setDevis(null);
            }
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getDateDebutPrestation(): ?\DateTimeInterface
    {
        return $this->dateDebutPrestation;
    }

    public function setDateDebutPrestation(?\DateTimeInterface $dateDebutPrestation): static
    {
        $this->dateDebutPrestation = $dateDebutPrestation;

        return $this;
    }

    public function getDateValidite(): ?\DateTimeInterface
    {
        return $this->dateValidite;
    }

    public function setDateValidite(?\DateTimeInterface $dateValidite): static
    {
        $this->dateValidite = $dateValidite;

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

    public function getTc(): ?string
    {
        return $this->tc;
    }

    public function setTc(?string $tc): static
    {
        $this->tc = $tc;

        return $this;
    }
}
