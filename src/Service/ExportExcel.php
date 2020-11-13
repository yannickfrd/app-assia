<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportExcel
{
    protected $name;
    protected $format;
    protected $arrayData;
    protected $columnsWidth;

    protected $spreadsheet;
    protected $sheet;

    protected $nbColumns;
    protected $nbRows;
    protected $highestColumn;
    protected $headers;
    protected $allCells;

    protected $writer;
    protected $contentType;
    protected $now;

    protected $datas;
    protected $normalisation;

    /**
     * Initialize the style sheet.
     */
    public function createSheet(string $name, string $format, array $arrayData, int $columnsWidth = null): void
    {
        $this->name = $name;
        $this->format = $format;
        $this->arrayData = $arrayData;
        $this->columnsWidth = $columnsWidth;
        $this->now = new \DateTime();

        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();

        $this->sheet->fromArray(
            $arrayData,  // The data to set
            null,        // Array values with this value will not be set
            'A1'         // Top left coordinate of the worksheet range where
        );

        $this->nbColumns = count($arrayData[0]);
        $this->nbRows = $this->sheet->getHighestRow();
        $this->highestColumn = $this->sheet->getHighestColumn();
        // $this->selectedCells = $this->sheet->getSelectedCells();

        $this->headers = 'A1:'.$this->highestColumn.'1';
        $this->allCells = 'A1:'.$this->highestColumn.$this->nbRows;

        $this->formatColumnsWidth();
        $this->formatDateColumns();
        $this->formatMoneyColumns();
        // $this->formatUrlColumns();
        $this->formatSheet();
        $this->formatPrint();
    }

    /**
     * Export the file.
     *
     * @return StreamedResponse|Response
     */
    public function exportFile(bool $asynch = false)
    {
        $this->getFormat($this->format);

        $path = \dirname(__DIR__).'/../public/uploads/exports/'.$this->now->format('Y/m/d/');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = $this->name.$this->now->format('_Y_m_d_His').'.'.$this->format;
        $file = $path.$filename;

        $this->writer->save($file);

        if ($asynch) {
            return $this->getPath($path, $filename);
        }

        return $this->getResponse($filename);
    }

    /**
     * Insert a row with subtotal.
     */
    public function addTotalRow(): void
    {
        $this->sheet->insertNewRowBefore(1, 1);
        $this->sheet->getCell('A1')->setValue('Total :');
        $this->sheet->getCell('B1')->setValue('=SUBTOTAL(3,A3:A'.($this->nbRows + 1).')');
        foreach ($this->getColumnsWithType('€') as $col) {
            $this->sheet->getCell($col.'1')->setValue('=SUBTOTAL(9,'.$col.'3:'.$col.($this->nbRows + 1).')');
            $this->sheet->getStyle($col.'1')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        $this->sheet->getStyle('A1:'.$this->highestColumn.'1')->getFont()->setBold(true);
    }

    protected function getPath(string $path, string $filename)
    {
        $file = $path.$filename;
        $zipFile = $file.'.zip';

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE)) {
            // $zip->addFromString('localname', 'file content goes here');
            $zip->addFile($file, $filename);
            $zip->close();
        }

        if (file_exists($file)) {
            unlink($file);
        }

        return  $zipFile;
    }

    /**
     * @return StreamedResponse|Response
     */
    protected function getResponse(string $filename)
    {
        $response = new StreamedResponse();

        $writer = $this->writer;

        $response->headers->set('Content-Type', $this->contentType);
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });

        return $response;
    }

    /**
     * Format the widht of columns.
     */
    protected function formatColumnsWidth(): void
    {
        $columnLetter = 'A';

        if ($this->columnsWidth) {
            $method = 'setWidth';
            $value = $this->columnsWidth;
        } else {
            $method = 'setAutoSize';
            $value = true;
            $this->sheet->getColumnDimension($columnLetter)->setWidth($this->columnsWidth);
        }

        for ($i = 0; $i < $this->nbColumns; ++$i) {
            $this->sheet->getColumnDimension($columnLetter)->$method($value);
            ++$columnLetter;
        }
    }

    /**
     * Format the columns with date.
     */
    protected function formatDateColumns(): void
    {
        // Format les colonnes de date
        foreach ($this->getColumnsWithType('Date') as  $col) {
            for ($i = 2; $i <= $this->nbRows; ++$i) {
                $this->sheet->getStyle($col.$i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }
        }
    }

    /**
     * Format the money columns.
     */
    protected function formatMoneyColumns(): void
    {
        // Format les colonnes de date
        foreach ($this->getColumnsWithType('€') as  $col) {
            for ($i = 2; $i <= $this->nbRows; ++$i) {
                $this->sheet->getStyle($col.$i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            }
        }
    }

    /**
     * Format the URL columns.
     */
    protected function formatUrlColumns(): void
    {
        // Format les colonnes avec une URL
        foreach ($this->getColumnsWithType('Url') as  $col) {
            for ($i = 2; $i <= $this->nbRows; ++$i) {
                $this->sheet->getCell($col.$i)->getHyperlink()->setUrl($this->sheet->getCell($col.$i)->getValue());
                $this->sheet->setCellValue($col.$i, 'Lien Url');
            }
        }
    }

    /**
     * Return the columns with a special type (Date, Url, money...).
     */
    protected function getColumnsWithType(string $word): array
    {
        $alphas = range('A', 'Z');
        $columns = [];
        foreach ($this->arrayData[0] as $key => $value) {
            if (stristr($value, $word)) {
                if ($key < 26) {
                    $columns[] = $alphas[$key];
                } else {
                    $xAlphas = floor($key / 26);
                    $diff = $key - ($xAlphas * 26);
                    $columns[] = $alphas[$xAlphas - 1].$alphas[$diff];
                }
            }
        }

        return $columns;
    }

    /**
     * Format the sheet to print.
     */
    protected function formatPrint(): void
    {
        // Page margins for print
        $this->sheet->getPageMargins()->setTop(0.4);
        $this->sheet->getPageMargins()->setRight(0.2);
        $this->sheet->getPageMargins()->setLeft(0.2);
        $this->sheet->getPageMargins()->setBottom(0.4);
        $this->sheet->getPageMargins()->setHeader(0.1);
        $this->sheet->getPageMargins()->setfooter(0.1);

        // Header and footer for print
        $this->sheet->getHeaderFooter()->setOddHeader('&C&B'.$this->name);
        $this->sheet->getHeaderFooter()->setOddFooter('&L'.$this->name.'&C'.$this->now->format('d/m/Y').'&RPage &P sur &N');

        // Repeat first row  for print
        $this->sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

        // Landscape orientation for print
        $this->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    }

    /**
     * Format the style sheet.
     */
    protected function formatSheet(): void
    {
        // Set name of sheet
        $this->sheet->setTitle($this->name);

        // set headers style
        $this->sheet
            ->setAutoFilter($this->headers) // filtres
            ->getStyle($this->headers)
            ->applyFromArray($this->getStyleHeaders());

        // set table style
        $this->sheet->getStyle($this->allCells)->applyFromArray($this->getStyleTable());

        $this->sheet->getRowDimension('1')->setRowHeight(20); // hauteur de la ligne

        // Hide gridlines
        $this->sheet->setShowGridlines(false);

        // Position on cell "A1"
        $this->sheet->getStyle('A1');
    }

    /**
     * Get format of file.
     */
    protected function getFormat(string $format): void
    {
        switch ($format) {
            case 'xlsx':
                $this->contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $this->writer = new Xlsx($this->spreadsheet);
            break;
            case 'ods':
                $this->contentType = 'application/vnd.oasis.opendocument.spreadsheet';
                $this->writer = new Ods($this->spreadsheet);
                break;
            case 'csv':
                $this->contentType = 'text/csv';
                $this->writer = new Csv($this->spreadsheet);
                break;
            default:
                $this->contentType = 'text/csv';
                $this->writer = new Csv($this->spreadsheet);
        }
    }

    /**
     * Get the style of headers.
     */
    protected function getStyleHeaders(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFF',
                ],
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
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '404040',
                ],
            ],
        ];
    }

    /**
     * Get the style of table.
     */
    protected function getStyleTable(): array
    {
        return  [
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
    }

    /**
     * Formatte une date.
     *
     * @return mixed
     */
    public function formatDate(?\DateTime $date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }

    /**
     * Formatte une date et une heure.
     *
     * @return mixed
     */
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
