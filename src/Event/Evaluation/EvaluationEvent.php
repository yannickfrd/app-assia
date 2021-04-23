<?php

namespace App\Event\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportGroup;
use Symfony\Contracts\EventDispatcher\Event;

class EvaluationEvent extends Event
{
    public const NAME = 'evaluation.event';

    private $evaluation;
    private $supportGroup;

    public function __construct(EvaluationGroup $evaluation, SupportGroup $supportGroup = null)
    {
        $this->evaluation = $evaluation;
        $this->supportGroup = $supportGroup;
    }

    public function getEvaluationGroup(): EvaluationGroup
    {
        return $this->evaluation;
    }

    public function getSupportGroup(): SupportGroup
    {
        return $this->supportGroup ?? $this->evaluation->getSupportGroup();
    }
}
