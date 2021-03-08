<?php

namespace App\Event\Support;

use App\Form\Model\Admin\ExportSearch;
use App\Form\Model\Support\SupportSearch;
use Symfony\Contracts\EventDispatcher\Event;

class SupportPersonExportEvent extends Event
{
    public const NAME = 'support_person.full_export';

    private $search;

    /**
     * @param ExportSearch|SupportSearch $search
     */
    public function __construct($search)
    {
        $this->search = $search;
    }

    public function getSearch()
    {
        return $this->search;
    }
}
