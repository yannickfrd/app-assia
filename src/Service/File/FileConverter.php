<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Support\Document;
use Dompdf\Dompdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

final class FileConverter
{
    private const TEXT_FILE_TYPES = ['txt', 'doc', 'docx'];

    private string $documentsDirectory;
    private string $downloadsDirectory;
    private ?string $file;

    public function __construct(string $documentsDirectory, string $downloadsDirectory)
    {
        $this->documentsDirectory = $documentsDirectory;
        $this->downloadsDirectory = $downloadsDirectory;
    }

    public function convert(Document $document): ?string
    {
        $this->file = $this->documentsDirectory.$document->getFilePath();

        // Check of the file exists.
        if (false === file_exists($this->file)) {
            return null;
        }

        // Return the file if is not a text document (txt or word).
        if (!in_array($document->getExtension(), self::TEXT_FILE_TYPES)) {
            return $this->file;
        }

        $filePath = $this->getFilePath($document);

        // If the converted file exists, return this one.
        if (file_exists($filePath)) {
            return $filePath;
        }

        // Convert the Word file to PDF.
        if (in_array($document->getExtension(), ['doc', 'docx'])) {
            return $this->wordToPdf($filePath, $document->getName());
        }

        // Convert the text file to PDF.
        return $this->textToPdf($filePath);
    }

    private function wordToPdf(string $filePath, string $title = ''): string
    {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath('.');

        $phpWord = IOFactory::load($this->file);
        $phpWord->getDocInfo()->setTitle($title);
        $phpWord->save($filePath, 'PDF');

        return $filePath;
    }

    private function textToPdf(string $filePath): string
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml('<p>'.nl2br(file_get_contents($this->file)).'</p>');
        $dompdf->setPaper('A4');
        $dompdf->render();

        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }

    private function getFilePath(Document $document): string
    {
        $tempPath = $this->downloadsDirectory.'documents/'.$document->getPath();

        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        return $tempPath.$document->getName().'.pdf';
    }
}
