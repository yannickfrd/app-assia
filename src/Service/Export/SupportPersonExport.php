<?php

namespace App\Service\Export;

use App\Entity\Support\OriginRequest;
use App\Entity\Support\SupportPerson;
use App\Service\ExportExcel;

class SupportPersonExport extends ExportExcel
{
    use SupportPersonDataTrait;

    protected $arrayData;
    protected $originRequest;

    public function __construct()
    {
        $this->arrayData = [];
        $this->originRequest = new OriginRequest();
    }

    /**
     * Exporte les données.
     *
     * @param Collection<SupportPerson>|null $supports
     *
     * @return StreamedResponse|Response|string
     */
    public function exportData(array $supports)
    {
        $arrayData = [];
        $arrayData[] = array_keys($this->getSupportPersonDatas($supports[0]));

        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->getSupportPersonDatas($supportPerson);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_suivis',
            'columnsWidth' => 15,
        ]);

        return $this->exportFile();
    }
}
