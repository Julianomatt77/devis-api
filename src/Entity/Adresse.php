<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\AdresseController;
use App\Repository\AdresseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/adresses', controller: AdresseController::class, name: 'app_adresses_all'),
        new Post(uriTemplate: '/api/adresses', controller: AdresseController::class, denormalizationContext: ['groups' => ['adresse:write']], name: 'app_adresse_new'),
        new Get(uriTemplate: '/api/adresses/{id}', controller: AdresseController::class, denormalizationContext: ['groups' => ['adresse:write']], name: 'app_adresse_show'),
        new Delete(uriTemplate: '/api/adresses/{id}', controller: AdresseController::class, denormalizationContext: ['groups' => ['adresse:write']],name: 'app_adresse_delete'),
        new Patch(uriTemplate: '/api/adresses/{id}', controller: AdresseController::class, denormalizationContext: ['groups' => ['adresse:write']], name: 'app_adresse_update'),
    ],
    formats: ["json"],
)]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read', 'client:write', ])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?int $numero = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?string $rue = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?string $complementaire = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?string $cp = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adresse:read', 'adresse:write', 'user:read', 'client:read'])]
    private ?string $pays = null;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'adresse')]
    #[Groups(['adresse:read'])]
    private Collection $clients;

    /**
     * @var Collection<int, Entreprise>
     */
    #[ORM\OneToMany(targetEntity: Entreprise::class, mappedBy: 'adresse')]
    #[Groups(['adresse:read'])]
    private Collection $entreprises;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[Groups(['adresse:read'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->entreprises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getComplementaire(): ?string
    {
        return $this->complementaire;
    }

    public function setComplementaire(?string $complementaire): static
    {
        $this->complementaire = $complementaire;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setAdresse($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getAdresse() === $this) {
                $client->setAdresse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Entreprise>
     */
    public function getEntreprises(): Collection
    {
        return $this->entreprises;
    }

    public function addEntreprise(Entreprise $entreprise): static
    {
        if (!$this->entreprises->contains($entreprise)) {
            $this->entreprises->add($entreprise);
            $entreprise->setAdresse($this);
        }

        return $this;
    }

    public function removeEntreprise(Entreprise $entreprise): static
    {
        if ($this->entreprises->removeElement($entreprise)) {
            // set the owning side to null (unless already changed)
            if ($entreprise->getAdresse() === $this) {
                $entreprise->setAdresse(null);
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
