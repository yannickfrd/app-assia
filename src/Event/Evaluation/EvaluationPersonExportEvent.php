<?php

namespace App\Event\Evaluation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EvaluationPersonExportEvent extends Event
{
    public const NAME = 'evaluation_person.export';

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
