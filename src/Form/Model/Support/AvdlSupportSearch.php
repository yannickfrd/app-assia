<?php

namespace App\Form\Model\Support;

class AvdlSupportSearch extends SupportSearch
{
    public const DIAG = 1;
    public const SUPPORT = 2;

    public const DIAG_OR_SUPPORT = [
        1 => 'Diagnostic',
        2 => 'Accompagnement',
    ];

    /**
     * @var int|null
     */
    private $diagOrSupport;

    /**
     * @var array
     */
    private $supportType;

    public function getDiagOrSupport(): ?int
    {
        return $this->diagOrSupport;
    }

    public function setDiagOrSupport(int $diagOrSupport): self
    {
        $this->diagOrSupport = $diagOrSupport;

        return $this;
    }

    public function getSupportType(): ?array
    {
        return $this->supportType;
    }

    public function setSupportType(array $supportType): self
    {
        $this->supportType = $supportType;

        return $this;
    }
}
