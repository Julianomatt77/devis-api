<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\EntrepriseController;
use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/entreprises', controller: EntrepriseController::class, name: 'app_entreprises_all'),
        new Post(uriTemplate: '/entreprises', controller: EntrepriseController::class, denormalizationContext: ['groups' => ['entreprise:write']], name: 'app_entreprise_new'),
        new Get(uriTemplate: '/entreprises/{id}', controller: EntrepriseController::class, denormalizationContext: ['groups' => ['entreprise:write']], name: 'app_entreprise_show'),
        new Delete(uriTemplate: '/entreprises/{id}', controller: EntrepriseController::class, denormalizationContext: ['groups' => ['entreprise:write']],name: 'app_entreprise_delete'),
        new Patch(uriTemplate: '/entreprises/{id}', controller: EntrepriseController::class, denormalizationContext: ['groups' => ['entreprise:write']], name: 'app_entreprise_update'),
    ],
    formats: ["json"],
)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $codeApe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read','entreprise:read', 'entreprise:write'])]
    private ?string $tvaIntracom = null;

    #[ORM\ManyToOne(inversedBy: 'entreprises')]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?Adresse $adresse = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['adresse:read', 'user:read','entreprise:read', 'entreprise:write'])]
    private ?string $telephone1 = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $telephone2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $web = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'entreprise:read', 'entreprise:write'])]
    private ?string $contact = null;

    /**
     * @var Collection<int, Devis>
     */
    #[ORM\OneToMany(targetEntity: Devis::class, mappedBy: 'entreprise')]
    #[Groups(['entreprise:read'])]
    private Collection $devis;

    #[ORM\ManyToOne(inversedBy: 'entreprises')]
    #[Groups(['entreprise:read'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->devis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCodeApe(): ?string
    {
        return $this->codeApe;
    }

    public function setCodeApe(?string $codeApe): static
    {
        $this->codeApe = $codeApe;

        return $this;
    }

    public function getTvaIntracom(): ?string
    {
        return $this->tvaIntracom;
    }

    public function setTvaIntracom(?string $tvaIntracom): static
    {
        $this->tvaIntracom = $tvaIntracom;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone1(): ?string
    {
        return $this->telephone1;
    }

    public function setTelephone1(?string $telephone1): static
    {
        $this->telephone1 = $telephone1;

        return $this;
    }

    public function getTelephone2(): ?string
    {
        return $this->telephone2;
    }

    public function setTelephone2(?string $telephone2): static
    {
        $this->telephone2 = $telephone2;

        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(?string $web): static
    {
        $this->web = $web;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Collection<int, Devis>
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): static
    {
        if (!$this->devis->contains($devi)) {
            $this->devis->add($devi);
            $devi->setEntreprise($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): static
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getEntreprise() === $this) {
                $devi->setEntreprise(null);
            }
        }

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
