<?php

namespace App\Export;

use PhpOffice\PhpSpreadsheet\Shared\Date;

trait ExportExcelTrait
{
    public function formatDate(?\DateTime $date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }

    public function formatDatetime(?\DateTime $date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d H:i')) : null;
    }

    /**
     * Ajoute l'objet normalisé.
     */
    protected function add(object $object, string $name = null)
    {
        $this->datas = array_merge($this->datas, $this->normalisation->normalize($object, $name));
    }
}
