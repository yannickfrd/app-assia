<?php

namespace App\Form\Model\Event;

class TaskSearch extends EventSearch
{
    /** @var array */
    protected $status = [];

    /** @var array */
    protected $level = [];

    public function getLevel(): array
    {
        return $this->level;
    }

    public function setLevel(array $level): self
    {
        $this->level = $level;

        return $this;
    }
}
