<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\SupportGroup;
use App\Entity\EvaluationGroup;
use Symfony\Contracts\EventDispatcher\Event;

class EvaluationEditEvent extends Event
{
    public const NAME = 'evaluation.monitoring.send_mail';

    private $user;
    private $supportGroup;
    private $evaluationGroup;

    public function __construct(User $user, SupportGroup $supportGroup, EvaluationGroup $evaluationGroup)
    {
        $this->user = $user;
        $this->supportGroup = $supportGroup;
        $this->evaluationGroup = $evaluationGroup;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getSupportGroup()
    {
        return $this->supportGroup;
    }

    public function getEvaluationGroup()
    {
        return $this->evaluationGroup;
    }
}
