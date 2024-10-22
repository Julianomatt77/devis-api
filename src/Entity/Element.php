<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\ElementController;
use App\Repository\ElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ElementRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/elements', controller: ElementController::class, name: 'app_elements_all'),
        new Post(uriTemplate: '/api/elements', controller: ElementController::class, denormalizationContext: ['groups' => ['element:write']], name: 'app_element_new'),
        new Get(uriTemplate: '/api/elements/{id}', controller: ElementController::class, denormalizationContext: ['groups' => ['element:write']], name: 'app_element_show'),
        new Delete(uriTemplate: '/api/elements/{id}', controller: ElementController::class, denormalizationContext: ['groups' => ['element:write']],name: 'app_element_delete'),
        new Patch(uriTemplate: '/api/elements/{id}', controller: ElementController::class, denormalizationContext: ['groups' => ['element:write']], name: 'app_element_update'),
    ],
    formats: ["json"],
)]
class Element
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'client:read', 'element:read', 'prestation:read', 'devis:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'client:read', 'element:read', 'element:write', 'prestation:read', 'devis:read'])]
    private ?string $nom = null;

    /**
     * @var Collection<int, Prestation>
     */
    #[ORM\OneToMany(targetEntity: Prestation::class, mappedBy: 'element')]
    #[Groups(['element:read'])]
    private Collection $prestations;

    #[ORM\ManyToOne(inversedBy: 'elements')]
    #[Groups(['element:read'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->prestations = new ArrayCollection();
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
            $prestation->setElement($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): static
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getElement() === $this) {
                $prestation->setElement(null);
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
