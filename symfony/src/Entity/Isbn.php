<?php

namespace App\Entity;

use App\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="isbn")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Isbn extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    protected $num;

    /**
     * One book can have multiple ISBNs
     * @ORM\ManyToOne(targetEntity="Book")
     * @ORM\JoinColumn(name="book_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    protected $book;

    public function setNum(string $num): self
    {
        $this->num = $num;

        return $this;
    }

    public function setBook(Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }
}
