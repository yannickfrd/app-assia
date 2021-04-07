<?php

namespace App\Event\Support;

use App\Form\Model\Admin\ExportSearch;
use App\Form\Model\Support\SupportSearch;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class SupportPersonExportEvent extends Event
{
    public const NAME = 'support_person.full_export';

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
