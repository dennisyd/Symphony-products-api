<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\PseudoTypes\PositiveInteger;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Product
 * @ORM\Entity
 */
#[
    ApiResource(
        normalizationContext: ['groups' => ['product.read']],
        denormalizationContext: ['groups' => ['product.write']],
        operations: [
            new Get(),
            new Post(
                security: 'is_granted("ROLE_ADMIN") and object.getOwner() === user',
                securityMessage: 'A product can only be updated by the owner'
            ),
        ],
    )
]
#[
    ApiResource(
        uriTemplate: '/manufacturer/{id}/products',
        operations: [new GetCollection()],
        uriVariables: [
            'id' => new Link(
                fromProperty: 'products',
                fromClass: Manufacturer::class
            )
        ]
    )
]
class Product
{
    /**
     * The id of the product.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * The MPN (manufacturer part number) of the product)
     *
     * @ORM\Column
     */
    #[
        Assert\NotNull,
        Groups(['product.read', 'product.write'])
    ]
    private ?string $mpn = null;

    /**
     * The name of the product.
     *
     * @ORM\Column
     */
    #[
        Assert\NotBlank,
        Groups(['product.read', 'product.write'])
    ]
    private string $name = '';

    /**
     * The description of the product.
     *
     * @ORM\Column(type="text")
     */
    #[
        Assert\NotBlank,
        Groups(['product.read', 'product.write'])
    ]
    private string $description = '';

    /**
     * The date of issue of the product.
     *
     * @ORM\Column(type="datetime")
     */
    #[
        Assert\NotNull,
        Groups(['product.read', 'product.write'])
    ]
    private ?\DateTimeInterface $issueDate = null;

    /**
     * The manufacturer of the product.
     *
     * @ORM\ManyToOne(
     *     targetEntity="Manufacturer",
     *     inversedBy="products"
     * )
     */
    #[
        Groups(['product.read, product.write'])
    ]
    private ?Manufacturer $manufacturer = null;


    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    #[
        Groups(['product.read, product.write'])
    ]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): void
    {
        $this->mpn = $mpn;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(?\DateTimeInterface $issueDate): void
    {
        $this->issueDate = $issueDate;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }



}