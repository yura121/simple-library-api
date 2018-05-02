<?php

namespace App\Entity;

use App\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="author")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Author extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    protected $name;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
