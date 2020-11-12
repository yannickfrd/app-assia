<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\RdvSearchTrait;

class SupportRdvSearch
{
    use RdvSearchTrait;
    use DateSearchTrait;
}
