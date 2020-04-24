<?php

namespace App\Form\Model\Traits;

trait DateSearchTrait
{
    /**
     * @var \DateTimeInterface|null
     */
    private $start;

    /**
     * @var \DateTimeInterface|null
     */
    private $end;

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        if ($end) {
            $this->end = $end;
        }

        return $this;
    }
}
