<?php

namespace App\Event;

use App\Form\Model\SupportGroupSearch;
use App\Repository\SupportPersonRepository;
use Symfony\Contracts\EventDispatcher\Event;

class ExportDataEvent extends Event
{
    public const NAME = 'data.export';

    private $supportGroupSearch;
    private $repo;

    public function __construct(SupportGroupSearch $supportGroupSearch, SupportPersonRepository $repo)
    {
        $this->supportGroupSearch = $supportGroupSearch;
        $this->repo = $repo;
    }

    public function getSupportGroupSearch()
    {
        return $this->supportGroupSearch;
    }

    public function getRepo()
    {
        return $this->repo;
    }
}
