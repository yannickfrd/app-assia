<?php

namespace App\Entity\Organization\Traits;

use App\Entity\Organization\Tag;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait TagTrait
{
    /**
     * @var Collection<Tag>|null
     * @ORM\ManyToMany(targetEntity=Tag::class)
     * @ORM\OrderBy({"name": "ASC"})
     * @Groups("show_tag")
     */
    protected $tags;

    /**
     * @return Collection<Tag>|null
     * @Groups("show_tag")
     */
    public function getTags(): ?Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTagsToArray(): array
    {
        $tags = [];

        foreach ($this->tags as $tag) {
            $tags[$tag->getId()] = $tag->getName();
        }

        return $tags;
    }

    public function getTagsToString(): string
    {
        return join(', ', $this->getTagsToArray());
    }

    public function getTagsIdsToString(): string
    {
        if (!$this->tags) {
            return '';
        }

        $tagIds = [];

        foreach ($this->tags as $tag) {
            $tagIds[] = $tag->getId();
        }

        return join('|', $tagIds);
    }
}
