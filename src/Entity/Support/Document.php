<?php

namespace App\Entity\Support;

use App\Entity\People\PeopleGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\DocumentRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @ORM\HasLifecycleCallbacks
 */
class Document
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const TYPE = [
        2 => 'Administratif',
        10 => 'Dettes',
        9 => 'Emploi',
        1 => 'Identité/Etat civil',
        4 => 'Impôts',
        6 => 'Logement',
        8 => 'Orientation',
        5 => 'Redevance',
        3 => 'Ressources',
        7 => 'Santé',
        97 => 'Autre',
    ];

    public const TYPE_EXTENSIONS = [
        'cvs' => 'CSV',
        'doc' => 'Word',
        'docx' => 'Word',
        'jpg' => 'Image',
        'jpeg' => 'Image',
        'odp' => 'ODP',
        'ods' => 'ODS',
        'odt' => 'ODT',
        'pdf' => 'PDF',
        'png' => 'Image',
        'rar' => 'Archive',
        'txt' => 'Texte',
        'xls' => 'Excel',
        'xlsx' => 'Excel',
        'zip' => 'Archive',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("get")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank();
     * @Groups("get")
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("get")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("get")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $internalFileName;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("get")
     */
    private $size;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="documents")
     * @Groups("get")
     * @MaxDepth(1)
     */
    private $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\PeopleGroup", inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $peopleGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="documents")
     */
    private $supportGroup;

    /**
     * @ORM\PreFlush
     */
    public function preFlush(): void
    {
        if ($this->supportGroup) {
            $this->supportGroup->setUpdatedAt(new \DateTime());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    /** @Groups("get") */
    public function getTypeToString(): ?string
    {
        return $this->type ? self::TYPE[$this->type] : '';
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getInternalFileName(): ?string
    {
        return $this->internalFileName;
    }

    public function setInternalFileName(string $internalFileName): self
    {
        $this->internalFileName = $internalFileName;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): self
    {
        $this->size = $size;

        return $this;
    }

    /** @Groups("get") */
    public function getExtension(): ?string
    {
        $array = explode('.', $this->internalFileName);

        return $array[count($array) - 1];
    }

    /** @Groups("get") */
    public function getFileType(): ?string
    {
        return self::TYPE_EXTENSIONS[$this->getExtension()] ?? null;
    }

    public function getPeopleGroup(): ?PeopleGroup
    {
        return $this->peopleGroup;
    }

    public function setPeopleGroup(?PeopleGroup $peopleGroup): self
    {
        $this->peopleGroup = $peopleGroup;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }
}
