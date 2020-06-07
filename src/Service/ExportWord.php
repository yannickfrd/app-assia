<?php

namespace App\Service;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Language;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportWord
{
    protected $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->getSettings()->setThemeFontLang(new Language(Language::FR_FR));
    }

    public function export(string $content, string $title = null, string $logoPath = null)
    {
        $section = $this->addSection();

        $this->addHeader($section, $logoPath);
        $this->addFooter($section);
        $this->addTitle($section, $title);
        $this->addContent($section, $content);
        $this->setDefaultStyleDocument();

        return $this->save($title);
    }

    protected function addSection()
    {
        return $this->phpWord->addSection([
            'marginLeft' => 1136,
            'marginRight' => 1136,
            'marginTop' => 1136,
            'marginBottom' => 1136,
            'headerHeight' => 250,
            'footerHeight' => 250,
        ]);
    }

    // Add first page header
    protected function addHeader($section, $logoPath = null)
    {
        $header = $section->addHeader('first');
        $header->addImage($logoPath ?? 'images/logo_esperer95.jpg', [
            'height' => 60,
        ]);
    }

    // Add footer
    protected function addFooter($section)
    {
        // Add first page footer
        $footer = $section->addFooter('first');
        $footer->addPreserveText((new \Datetime())->format('d/m/Y'), $this->getFontStyleFooter(), [
            'alignment' => 'center',
            'positioning' => 'absolute',
            'spaceAfter' => 0,
        ]);
        $footer->addPreserveText('{PAGE} / {NUMPAGES}', $this->getFontStyleFooter(), [
            'alignment' => 'right',
        ]);

        // Add footer for all other pages
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE} / {NUMPAGES}', $this->getFontStyleFooter(), [
            'alignment' => 'right',
        ]);
    }

    // Add title
    protected function addTitle($section, string $title)
    {
        $section->addText($title, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
        // $section->addTitle($title);
        // $this->phpWord->addTitleStyle(1, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
    }

    // Add body content
    protected function addContent($section, string $content)
    {
        $htmlContent = str_replace('  ', '', $content);
        $htmlContent = str_replace('<br>', '<br/>', $content);
        Html::addHtml($section, $htmlContent, false, false);
    }

    protected function getFontStyleFooter()
    {
        return  [
            'name' => 'Calibri Light',
            'size' => 10,
            'italic' => true,
            'color' => '1B2232',
        ];
    }

    // Police par défaut du titre
    protected function getDefaultFontStyleTitle()
    {
        return [
            'name' => 'Calibri Light',
            'size' => 18,
            'color' => '1B2232',
            'bold' => true,
            'align' => 'center',
        ];
    }

    // Style de paragraphe par défaut du titre
    protected function getDefaultParagraphStyleTitle()
    {
        return [
            'align' => 'center',
            'spaceAfter' => 500,
        ];
    }

    // Style par défaut du contenu
    protected function setDefaultStyleDocument()
    {
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(11);
    }

    // Save the document
    public function save(string $title, $download = true)
    {
        $title = str_replace([' ', '/'], '-', $title ? $title : 'document');
        $title = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()', $title);

        // Settings::setPdfRendererPath('..\vendor\dompdf\dompdf');
        // Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $path = '/public/uploads/exports/';

        $objWriter->save($path.$title.'.docx', true);

        if (true === $download) {
            return $this->download($objWriter, $title);
        }
    }

    protected function download($objWriter, string $title)
    {
        $response = new StreamedResponse();

        $contentType = 'application/vnd.ms-word';
        $filename = $title.'.docx';

        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($objWriter) {
            $objWriter->save('php://output');
        });

        return $response;
    }
}
