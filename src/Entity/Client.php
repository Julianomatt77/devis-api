<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\ClientController;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/clients', controller: ClientController::class, name: 'app_clients_all'),
        new Post(uriTemplate: '/api/clients', controller: ClientController::class, denormalizationContext: ['groups' => ['client:write']], name: 'app_client_new'),
        new Get(uriTemplate: '/api/clients/{id}', controller: ClientController::class, denormalizationContext: ['groups' => ['client:write']], name: 'app_client_show'),
        new Delete(uriTemplate: '/api/clients/{id}', controller: ClientController::class, denormalizationContext: ['groups' => ['client:write']],name: 'app_client_delete'),
        new Patch(uriTemplate: '/api/clients/{id}', controller: ClientController::class, denormalizationContext: ['groups' => ['client:write']], name: 'app_client_update'),
    ],
    formats: ["json"],
)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adresse:read', 'user:read', 'client:read', 'prestation:read', 'devis:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'user:read', 'client:read', 'client:write', 'devis:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'client:read', 'client:write', 'devis:read'])]
    private ?string $prenom = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[Groups(['client:read', 'client:write', 'devis:read'])]
    private ?Adresse $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'client:read', 'client:write', 'devis:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['adresse:read', 'user:read', 'client:read', 'client:write', 'devis:read'])]
    private ?string $telephone = null;

    /**
     * @var Collection<int, Devis>
     */
    #[ORM\OneToMany(targetEntity: Devis::class, mappedBy: 'client')]
    #[Groups(['client:read'])]
    private Collection $devis;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    #[Groups(['client:read'])]
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

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

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
            $devi->setClient($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): static
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getClient() === $this) {
                $devi->setClient(null);
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
