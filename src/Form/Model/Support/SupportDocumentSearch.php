<?php

namespace App\Form\Model\Support;

use App\Entity\Support\Document;
use Doctrine\Common\Collections\ArrayCollection;

class SupportDocumentSearch
{
    /** @var string|null */
    private $name;

    /** @var ArrayCollection|null */
    protected $tags;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    
    public function getTags(): ?ArrayCollection
    {
        return $this->tags;
    }

    public function setTags(?ArrayCollection $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
