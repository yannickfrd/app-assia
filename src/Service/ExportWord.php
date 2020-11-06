<?php

namespace App\Service;

use PhpOffice\PhpWord\Element\Header;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Writer\WriterInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportWord
{
    protected $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->getSettings()->setThemeFontLang(new Language(Language::FR_FR));
    }

    /**
     * Export file.
     */
    public function export(string $content, ?string $title = 'document', ?string $logoPath = null)
    {
        /** * @var Section $section */
        $section = $this->addSection();

        $this->addHeader($section, $title, $logoPath);
        $this->addFooter($section);
        $this->addTitle($section, $title);
        $this->addContent($section, $content);
        $this->setDefaultStyleDocument();

        return $this->save($title);
    }

    /**
     * Add a section.
     */
    protected function addSection(): Section
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

    /**
     * Add first page header.
     */
    protected function addHeader(Section $section, string $title, ?string $logoPath = null): void
    {
        // Add first page header
        $header = $section->addHeader(HEADER::FIRST);
        $defaultLogo = 'images/logo_esperer95.jpg';

        if (\file_exists($defaultLogo)) {
            $header->addImage($logoPath ?? $defaultLogo, [
                'height' => 60,
            ]);
        }

        // Add sub page header
        $headerSub = $section->addHeader();
        $headerSub->addPreserveText('ESPERER 95 | '.$title, $this->getFontStyleFooter(), [
            'alignment' => 'right',
        ]);
    }

    /**
     * Add the footer.
     */
    protected function addFooter(Section $section): void
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

    /**
     * Add the title.
     */
    protected function addTitle(Section $section, ?string $title = null): void
    {
        if ($title) {
            $section->addText($title, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
            // $section->addTitle($title);
            // $this->phpWord->addTitleStyle(1, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
        }
    }

    /**
     * Add the body content.
     */
    protected function addContent(Section $section, string $content): void
    {
        $htmlContent = \str_replace('  ', '', $content);
        $htmlContent = \str_replace('<br>', '<br/>', $content);
        Html::addHtml($section, $htmlContent, false, false);
    }

    /**
     * Get the font style for footer.
     */
    protected function getFontStyleFooter(): array
    {
        return  [
            'name' => 'Calibri Light',
            'size' => 10,
            'italic' => true,
            'color' => '1B2232',
        ];
    }

    /**
     * Police par défaut du titre.
     */
    protected function getDefaultFontStyleTitle(): array
    {
        return [
            'name' => 'Calibri Light',
            'size' => 18,
            'color' => '1B2232',
            'bold' => true,
            'align' => 'center',
        ];
    }

    /**
     * Get the default paragrah style of the title.
     *
     * @return void
     */
    protected function getDefaultParagraphStyleTitle(): array
    {
        return [
            'align' => 'center',
            'spaceAfter' => 500,
              'shading' => [
                'fill' => 'e9ecef',
            ],
        ];
    }

    /**
     * Set the default style of the document.
     */
    protected function setDefaultStyleDocument(): void
    {
        $this->phpWord->setDefaultFontName('Calibri Light');
        $this->phpWord->setDefaultFontSize(11);

        $this->phpWord->setDefaultParagraphStyle([
            'spaceAfter' => 80,
            'spacing' => 1,
        ]);
    }

    /**
     * Save the document.
     */
    public function save(?string $title, bool $download = true): ?StreamedResponse
    {
        $title = \str_replace([' ', '/', '\''], '-', $title ? $title : 'document');
        $title = \transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove', $title);

        // Settings::setPdfRendererPath('..\vendor\dompdf\dompdf');
        // Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        // $path = \dirname(__DIR__).'/../public/uploads/exports/'.(new \DateTime())->format('Y/m/d/');
        // $objWriter->save($path.$title.'.docx');

        if (true === $download) {
            return $this->download($objWriter, $title);
        }
    }

    /**
     * Download file.
     */
    protected function download(WriterInterface $objWriter, string $title): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.ms-word');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$title.'.docx');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($objWriter) {
            $objWriter->save('php://output');
        });

        return $response;
    }
}
