<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A manufacturer
 *
 * @ORM\Entity
 */
#[ApiResource(

    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Put()
    ],
    paginationItemsPerPage: 1,
),
ApiFilter(
    SearchFilter::class,
    properties: [
        'name' => SearchFilter::STRATEGY_PARTIAL,
        'description' => SearchFilter::STRATEGY_PARTIAL,
        'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT,
    ],
),
    ApiFilter(
        OrderFilter::class,
        properties: ['issueDate']
    )
]
class Manufacturer
{
    /**
     * The id of the manufacturer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * The name of the manufacturer
     *
     * @ORM\Column
     */
    #[
        Assert\NotBlank,
        Groups(['product.read'])
    ]
    private string $name = '';

    /**
     * The description of the manufacturer
     *
     * @ORM\Column(type="text")
     */
    #[Assert\NotBlank]
    private string $description = '';

    /**
     * The country code of the manufacturer
     *
     *  @ORM\Column(length=3)
     */
    #[Assert\NotBlank]
    private string $countryCode = '';

    /**
     * The date that the manufacturer was listed
     *
     * @ORM\Column(type="datetime")
     */
    #[Assert\NotNull]
    private ?\DateTimeInterface $listDate = null;

    /**
     * @var Product[] Available products from this manufacturer
     *
     * @ORM\OneToMany(
     *     targetEntity="Product",
     *     mappedBy="manufacturer",
     *     cascade={"persist", "remove"}
     * )
     */
    #[Link(toProperty: 'products')]
    private iterable $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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


    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getListDate(): ?\DateTimeInterface
    {
        return $this->listDate;
    }

    public function setListDate(?\DateTimeInterface $listDate): void
    {
        $this->listDate = $listDate;
    }

    public function getProducts(): iterable|ArrayCollection
    {
        return $this->products;
    }
}