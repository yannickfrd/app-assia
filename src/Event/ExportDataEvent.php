<?php

namespace App\Event;

use App\Form\Model\SupportSearch;
use App\Repository\SupportPersonRepository;
use Symfony\Contracts\EventDispatcher\Event;

class ExportDataEvent extends Event
{
    public const NAME = 'data.export';

    private $search;
    private $repo;

    public function __construct(SupportSearch $search, SupportPersonRepository $repo)
    {
        $this->search = $search;
        $this->repo = $repo;
    }

    public function getSupportSearch()
    {
        return $this->search;
    }

    public function getRepo()
    {
        return $this->repo;
    }
}
