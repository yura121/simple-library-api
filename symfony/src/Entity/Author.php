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
     * @ORM\Column(name="full_name", type="string", length=200, nullable=false)
     */
    protected $fullName;

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }
}
