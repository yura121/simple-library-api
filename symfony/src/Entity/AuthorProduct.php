<?php

namespace App\Entity;

use App\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="author__product")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AuthorProduct extends AbstractEntity
{
    /**
     * @ORM\Column(name="author_id", type="integer", nullable=false)
     */
    protected $authorId;

    /**
     * @ORM\Column(name="product_type", type="integer", nullable=false)
     */
    protected $productType;

    /**
     * @ORM\Column(name="product_id", type="integer", nullable=false)
     */
    protected $productId;

    public function setAuthorId(int $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function setProductType(int $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }
}
