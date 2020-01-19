<?php

namespace App\Service;

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
    private $name;
    private $format;
    private $arrayData;
    private $columnsWidth;

    private $spreadsheet;
    private $sheet;
    private $writer;
    private $contentType;

    public function __construct($name, $format, $arrayData,  $columnsWidth)
    {
        $this->name = $name;
        $this->format = $format;
        $this->arrayData = $arrayData;
        $this->columnsWidth = $columnsWidth;

        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();

        $this->sheet->fromArray(
            $arrayData,  // The data to set
            NULL,        // Array values with this value will not be set
            "A1"         // Top left coordinate of the worksheet range where
        );

        $this->nbColumns = count($arrayData[0]);
        $this->nbRows = $this->sheet->getHighestRow();
        $this->highestColumn = $this->sheet->getHighestColumn();
        // $this->selectedCells = $this->sheet->getSelectedCells();

        $this->headers = "A1:" . $this->highestColumn . "1";
        $this->allCells = "A1:" . $this->highestColumn . $this->nbRows;

        $this->init();
    }

    protected function init()
    {
        $columnLetter = "A";

        if ($this->columnsWidth) {
            for ($i = 0; $i < $this->nbColumns; $i++) {
                $this->sheet->getColumnDimension($columnLetter)->setWidth($this->columnsWidth);
                $columnLetter++;
            }
        } else {
            for ($i = 0; $i < $this->nbColumns; $i++) {
                $this->sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                $columnLetter++;
            }
        }

        // Récupère les colonnes de Date
        $alphas = range("A", "Z");
        $columnsWithDate = [];
        foreach ($this->arrayData[0] as $key => $value) {
            if (stristr($value, "Date"))
                if ($key < 26) {
                    $columnsWithDate[] = $alphas[$key];
                }
        }
        // Format les colonnes de date
        foreach ($columnsWithDate as  $value) {
            for ($i = 2; $i <= $this->nbRows; $i++) {
                // $cellValue = $this->sheet->getCell($value . $i)->getValue();
                // $this->sheet->setCellValue($value . $i, Date::PHPToExcel($cellValue));
                $this->sheet->getStyle($value . $i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }
        }
        $this->getFormat($this->format);
        $this->stylePrint();
        $this->styleSheet();
    }

    public function exportFile()
    {
        $filename = $this->name . "." . $this->format;
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

    // Style for print
    protected function stylePrint()
    {
        // Page margins for print
        $this->sheet->getPageMargins()->setTop(0.4);
        $this->sheet->getPageMargins()->setRight(0.2);
        $this->sheet->getPageMargins()->setLeft(0.2);
        $this->sheet->getPageMargins()->setBottom(0.4);
        $this->sheet->getPageMargins()->setHeader(0.1);
        $this->sheet->getPageMargins()->setFooter(0.1);

        $now = new \DateTime();

        // Header and footer for print
        $this->sheet->getHeaderFooter()->setOddHeader("&C&B" .  $this->name);
        $this->sheet->getHeaderFooter()->setOddFooter("&L" .  $this->name . "&C" . $now->format("d/m/Y") . "&RPage &P sur &N");

        // Repeat first row  for print
        $this->sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

        // Landscape orientation for print
        $this->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    }

    protected function styleSheet()
    {
        // Set name of sheet
        $this->sheet->setTitle($this->name);

        // set headers style
        $this->sheet
            ->setAutoFilter($this->headers) // filtres
            ->getStyle($this->headers)
            ->applyFromArray($this->styleHeaders());

        // set table style
        $this->sheet->getStyle($this->allCells)->applyFromArray($this->styleTable());

        $this->sheet->getRowDimension("1")->setRowHeight(20); // hauteur de la ligne

        // Hide gridlines
        $this->sheet->setShowGridlines(false);

        // Position on cell "A1"
        $this->sheet->getStyle("A1");
    }

    // Get format of file 
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
                $this->contentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                $this->writer = new Xlsx($this->spreadsheet);
        }
    }

    // Headers style
    protected function styleHeaders()
    {
        return [
            "font" => [
                "bold" => true,
            ],
            "alignment" => [
                "vertical" => Alignment::VERTICAL_CENTER,
            ],
            "borders" => [
                "top" => [
                    "borderStyle" => Border::BORDER_THIN,
                ],
            ],
            "fill" => [
                "fillType" => Fill::FILL_GRADIENT_LINEAR,
                "rotation" => 90,
                "startColor" => [
                    "argb" => "FFA0A0A0",
                ],
                "endColor" => [
                    "argb" => "FFFFFFFF",
                ],
            ],
        ];
    }

    // Table style
    protected function styleTable()
    {
        return  [
            "alignment" => [
                "vertical" => Alignment::VERTICAL_CENTER,
            ],
            "borders" => [
                "allBorders" => [
                    "borderStyle" => Border::BORDER_THIN,
                    "color" => ["argb" => "a6a6a6"],
                ],
            ],
        ];
    }
}
