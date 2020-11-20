<?php

namespace App\Entity;

use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReferentRepository")
 */
class Referent
{
    use CreatedUpdatedEntityTrait;
    use LocationEntityTrait;
    use ContactEntityTrait;

    public const TYPE = [
        2 => 'Accueil de jour',
        3 => 'AVDL',
        4 => 'CCAS',
        5 => "Centre d'hébergement",
        6 => 'Conseil Départemental',
        7 => 'Dispositif asile',
        8 => 'Dispositif logement adapté',
        9 => 'ESPERER 95',
        1 => 'PASH (ex-AMH)',
        10 => 'Service de tutelle',
        11 => 'Service hospitalier',
        12 => 'Service Justice',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $socialWorker;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $socialWorker2;

    /**
     * @ORM\Column(name="email1", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $email2;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PeopleGroup", inversedBy="referents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $peopleGroup;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeToString(): ?string
    {
        return self::TYPE[$this->type];
    }

    public function getSocialWorker(): ?string
    {
        return $this->socialWorker;
    }

    public function setSocialWorker(?string $socialWorker): self
    {
        $this->socialWorker = $socialWorker;

        return $this;
    }

    public function getSocialWorker2(): ?string
    {
        return $this->socialWorker2;
    }

    public function setSocialWorker2(?string $socialWorker2): self
    {
        $this->socialWorker2 = $socialWorker2;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): self
    {
        $this->email2 = $email2;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
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
}
