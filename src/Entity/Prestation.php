<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Operation;
use App\Controller\PrestationController;
use App\Repository\PrestationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/api/prestations', controller: PrestationController::class, name: 'app_prestations_all'),
        new Post(
            uriTemplate: '/api/prestations',
            denormalizationContext: ['groups' => ['prestation:write']],
            name: 'app_prestation_new',
            controller: PrestationController::class,
            openapi: new Operation(
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'element' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]],
                                    'qty' => ['type' => 'integer'],
                                    'prixHT' => ['type' => 'integer'],
                                    'tvaPercentage' => ['type' => 'integer'],
                                    'devis' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]]
                                ],
                                'required' => ['element', 'qty', 'prixHT', 'tvaPercentage', 'devis']
                            ]
                        ]
                    ])
                )
            )
        ),
        new Get(uriTemplate: '/api/prestations/{id}', controller: PrestationController::class, denormalizationContext: ['groups' => ['prestation:write']], name: 'app_prestation_show'),
        new Delete(uriTemplate: '/api/prestations/{id}', controller: PrestationController::class, denormalizationContext: ['groups' => ['prestation:write']],name: 'app_prestation_delete'),
        new Patch(
            uriTemplate: '/api/prestations/{id}',
            denormalizationContext: ['groups' => ['prestation:write']],
            name: 'app_prestation_update',
            controller: PrestationController::class,
            openapi: new Operation(
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'element' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]],
                                    'qty' => ['type' => 'integer'],
                                    'prixHT' => ['type' => 'integer'],
                                    'tvaPercentage' => ['type' => 'integer'],
                                    'devis' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]]
                                ]
                            ]
                        ]
                    ])
                )
            )
        ),
    ],
    formats: ["json"],
)]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'user:read', 'prestation:read', 'devis:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['client:read', 'user:read', 'prestation:read', 'prestation:write', 'devis:read'])]
    private ?Element $element = null;

    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'prestation:read', 'prestation:write', 'devis:read'])]
    private ?int $qty = null;

    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'prestation:read', 'prestation:write', 'devis:read'])]
    private ?int $prixHT = null;

    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'prestation:read', 'prestation:write', 'devis:read'])]
    private ?int $tvaPercentage = null;

    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'prestation:read', 'devis:read'])]
    private ?int $tva = null;

    #[ORM\Column]
    #[Groups(['client:read','element:read', 'prestation:read', 'devis:read'])]
    private ?int $totalTTC = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    #[Groups(['prestation:read', 'prestation:write'])]
    private ?Devis $devis = null;

    #[ORM\Column]
    #[Groups(['client:read', 'element:read', 'prestation:read', 'devis:read'])]
    private ?int $totalHT = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    #[Groups(['prestation:read'])]
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
