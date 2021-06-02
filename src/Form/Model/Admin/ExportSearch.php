<?php

namespace App\Form\Model\Admin;

use App\Form\Model\Support\SupportSearch;

class ExportSearch extends SupportSearch
{
    public const MODELS = [
        'full' => 'Export complet',
        'light' => 'Export simplifié',
        // 'avdl' => 'Export AVDL',
        // 'pash' => 'Export PASH',
    ];

    /** @var bool */
    private $formattedSheet;

    /** @var string */
    private $model = 'full';

    /** @var string */
    private $path;

    /** @var bool */
    private $anonymized;

    /**
     * @var bool|null
     */
    private $evalAdm;

    /**
     * @var bool|null
     */
    private $evalBudget;

    /**
     * @var bool|null
     */
    private $evalFamily;

    /**
     * @var bool|null
     */
    private $evalHousing;

    /**
     * @var bool|null
     */
    private $evalProf;

    /**
     * @var bool|null
     */
    private $evalSocial;

    /**
     * @var bool|null
     */
    private $evalJustice;

    public function getEvalAdm(): ?bool
    {
        return $this->evalAdm;
    }

    public function setEvalAdm(bool $evalAdm): self
    {
        $this->evalAdm = $evalAdm;

        return $this;
    }

    public function getEvalBudget(): ?bool
    {
        return $this->evalBudget;
    }

    public function setEvalBudget(bool $evalBudget): self
    {
        $this->evalBudget = $evalBudget;

        return $this;
    }

    public function getEvalFamily(): ?bool
    {
        return $this->evalFamily;
    }

    public function setEvalFamily(bool $evalFamily): self
    {
        $this->evalFamily = $evalFamily;

        return $this;
    }

    public function getEvalHousing(): ?bool
    {
        return $this->evalHousing;
    }

    public function setEvalHousing(bool $evalHousing): self
    {
        $this->evalHousing = $evalHousing;

        return $this;
    }

    public function getEvalProf(): ?bool
    {
        return $this->evalProf;
    }

    public function setEvalProf(bool $evalProf): self
    {
        $this->evalProf = $evalProf;

        return $this;
    }

    public function getEvalSocial(): ?bool
    {
        return $this->evalSocial;
    }

    public function setEvalSocial(bool $evalSocial): self
    {
        $this->evalSocial = $evalSocial;

        return $this;
    }

    public function getEvalJustice(): ?bool
    {
        return $this->evalJustice;
    }

    public function setEvalJustice(bool $evalJustice): self
    {
        $this->evalJustice = $evalJustice;

        return $this;
    }

    public function getFormattedSheet(): ?bool
    {
        return $this->formattedSheet;
    }

    public function setFormattedSheet(bool $formattedSheet): self
    {
        $this->formattedSheet = $formattedSheet;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getAnonymized(): bool
    {
        return $this->anonymized;
    }

    public function setAnonymized(bool $anonymized): self
    {
        $this->anonymized = $anonymized;

        return $this;
    }
}
