<?php

namespace App\Form\Model\Event;

use App\Entity\Event\Task;
use App\Entity\Traits\DeletedTrait;

class TaskSearch extends EventSearch
{
    use DeletedTrait;

    /** @var array */
    protected $status = [Task::TASK_IS_NOT_DONE];

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
