<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Export

{
    private $spreadsheet;
    private $writer;
    private $contentType;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function exportFile($name, $format, $arrayData,  $columnsWithDate = null)
    {
        $styleHeaders = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];

        $styleTable = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'a6a6a6'],
                ],
            ],
        ];

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $arrayData,  // The data to set
                NULL,        // Array values with this value will not be set
                "A1"         // Top left coordinate of the worksheet range where
            );

        // Page margins
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setTop(0.4);
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setRight(0.2);
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setLeft(0.2);
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setBottom(0.4);
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setHeader(0.1);
        $this->spreadsheet->getActiveSheet()->getPageMargins()->setFooter(0.1);

        // Hide gridlines
        $this->spreadsheet->getActiveSheet()->setShowGridlines(false);

        // Set name of sheet
        $this->spreadsheet->getActiveSheet()->setTitle($name);

        // Header and footer
        $this->spreadsheet->getActiveSheet()->getHeaderFooter()
            ->setOddHeader('&L&B' .  $name);
        $this->spreadsheet->getActiveSheet()->getHeaderFooter()
            ->setOddFooter($name . '&RPage &P sur &N');

        // Repeat first row
        $this->spreadsheet->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

        // Landscape orientation
        $this->spreadsheet->getActiveSheet()->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $nbColumns = count($arrayData[0]);
        $nbRows = $this->spreadsheet->getActiveSheet()->getHighestRow();
        $highestColumn = $this->spreadsheet->getActiveSheet()->getHighestColumn();
        $selectedCells = $this->spreadsheet->getActiveSheet()->getSelectedCells();

        $headers = "A1:" . $this->spreadsheet->getActiveSheet()->getHighestColumn() . "1";
        $allCells = "A1:" . $this->spreadsheet->getActiveSheet()->getHighestColumn() . $nbRows;

        $this->spreadsheet->getActiveSheet()
            ->setAutoFilter($headers) // filtres
            ->getStyle($headers)
            ->applyFromArray($styleHeaders);

        $this->spreadsheet->getActiveSheet()->getRowDimension("1")->setRowHeight(20); // hauteur de la ligne

        $this->spreadsheet->getActiveSheet()
            ->getStyle($allCells)
            ->applyFromArray($styleTable);

        $columnLetter = "A";
        for ($i = 0; $i < $nbColumns; $i++) {
            $this->spreadsheet->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnLetter++;
        }

        foreach ($columnsWithDate as  $value) {

            for ($i = 2; $i <= $nbRows; $i++) {
                // $cellValue = $this->spreadsheet->getActiveSheet()->getCell($value . $i)->getValue();
                // dd($cellValue);
                // $this->spreadsheet->getActiveSheet()->setCellValue($value . $i, Date::PHPToExcel($cellValue));
                $this->spreadsheet->getActiveSheet()
                    ->getStyle($value . $i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }
        }


        $this->getFormat($format);

        $filename = $name . "." . $format;
        $this->writer->save($filename);

        $response = new StreamedResponse();

        $writer = $this->writer;

        $response->headers->set("Content-Type", $this->contentType);
        $response->headers->set("Content-Disposition", "attachment;filename=" . $filename);
        $response->setPrivate();
        $response->headers->addCacheControlDirective("no-cache", true);
        $response->headers->addCacheControlDirective("must-revalidate", true);
        $response->setCallback(function () use ($writer) {
            $writer->save("php://output");
        });

        return $response;
    }

    protected function getFormat($format)
    {
        switch ($format) {
            case "ods":
                $this->contentType = "application/vnd.oasis.opendocument.spreadsheet";
                $this->writer = new Ods($this->spreadsheet);
                break;
            case "xlsx":
                $this->contentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                $this->writer = new Xlsx($this->spreadsheet);
                break;
            case "csv":
                $this->contentType = "text/csv";
                $this->writer = new Csv($this->spreadsheet);
                break;
            default:
                $this->contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $this->writer = new Xlsx($this->spreadsheet);
        }
    }
}
