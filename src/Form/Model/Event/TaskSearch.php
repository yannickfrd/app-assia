<?php

namespace App\Form\Model\Event;

use App\Entity\Event\Task;

class TaskSearch extends EventSearch
{
    /** @var array */
    protected $status = [Task::TASK_IS_NOT_DONE];

    /** @var array */
    protected $level;

    public function getLevel(): ?array
    {
        return $this->level;
    }

    public function setLevel(?array $level): self
    {
        $this->level = $level;

        return $this;
    }
}
