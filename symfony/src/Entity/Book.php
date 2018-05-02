<?php

namespace App\Entity;

use App\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="book")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Book extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=4, nullable=false)
     */
    protected $year;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }
}
