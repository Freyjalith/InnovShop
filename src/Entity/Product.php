<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Correspond à la colonne 'nom' dans ta base
    #[ORM\Column(type: 'string', length: 255, name: 'nom')]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'description')]
    private ?string $description = null;

    // Correspond à la colonne 'prix' dans ta base
    #[ORM\Column(type: 'float', name: 'prix')]
    private ?float $price = null;

    // Correspond à la colonne 'stock' dans ta base
    #[ORM\Column(type: 'integer', name: 'stock')]
    private ?int $stock = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $featured = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $specifications = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isNew = false;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $originalPrice = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $discountPercentage = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'product')]
    private Collection $reviews;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    private ?Category $category = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

    public function getSpecifications(): ?string
    {
        return $this->specifications;
    }

    public function setSpecifications(?string $specifications): self
    {
        $this->specifications = $specifications;
        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function getOriginalPrice(): ?float
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?float $originalPrice): self
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    public function getDiscountPercentage(): ?int
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?int $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function hasPromotion(): bool
    {
        return $this->originalPrice !== null && $this->discountPercentage !== null;
    }

    public function getPromotionPrice(): ?float
    {
        if (!$this->hasPromotion()) {
            return null;
        }
        return round($this->originalPrice * (1 - $this->discountPercentage / 100), 2);
    }

    public function getCurrentPrice(): float
    {
        return $this->hasPromotion() ? $this->getPromotionPrice() : round($this->price, 2);
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }
        return $this;
    }

    public function removeImage(ProductImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }
        return $this;
    }

    public function getPrimaryImage(): ?ProductImage
    {
        foreach ($this->images as $image) {
            if ($image->isPrimary()) {
                return $image;
            }
        }
        return $this->images->first() ?: null;
    }

    public function decrementStock(int $quantity): self
    {
        $this->stock = max(0, $this->stock - $quantity);
        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function getAverageRating(): float
    {
        if ($this->reviews->isEmpty()) {
            return 0;
        }
        
        $total = 0;
        foreach ($this->reviews as $review) {
            $total += $review->getRating();
        }
        
        return round($total / $this->reviews->count(), 1);
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}

