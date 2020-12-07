<?php

namespace App\Form\Model;

use App\Entity\SupportGroup;
use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;
use Symfony\Component\Validator\Constraints as Assert;

class SupportSearch
{
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    public const SUPPORT_DATES = [
        1 => 'Début du suivi',
        2 => 'Fin du suivi',
        3 => 'Période de suivi',
    ];

    /**
     * @var string|null
     */
    private $fullname;

    /**
     * @var \DateTimeInterface|null
     * @Assert\Date(message="Date de naissance invalide.")
     */
    private $birthdate;

    /**
     * @var array
     */
    private $familyTypologies;

    /**
     * @var array
     */
    private $status;

    /**
     * @var int|null
     * @Assert\Range(min = 1, max = 9)
     */
    private $nbPeople;

    /**
     * @var int|null
     */
    private $supportDates;

    /**
     * @var bool
     */
    private $head = true;

    /**
     * @var bool
     */
    private $export;

    public function __construct()
    {
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getFamilyTypologies(): ?array
    {
        return $this->familyTypologies;
    }

    public function setFamilyTypologies(?array $familyTypologies): self
    {
        $this->familyTypologies = $familyTypologies;

        return $this;
    }

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusToString(): array
    {
        $status = [];

        foreach ($this->status  as $value) {
            $status[] = SupportGroup::STATUS[$value];
        }

        return $status;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(int $nbPeople): self
    {
        $this->nbPeople = $nbPeople;

        return $this;
    }

    public function getSupportDates(): ?int
    {
        return $this->supportDates;
    }

    public function setSupportDates(int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function getSupportDatesToString(): string
    {
        return $this->supportDates ? self::SUPPORT_DATES[$this->supportDates] : null;
    }

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function setHead(bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }
}
