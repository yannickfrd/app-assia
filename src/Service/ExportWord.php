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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportWord
{
    protected $phpWord;

    protected $title;
    protected $logoPath;
    protected $infoAdd;
    protected $defaultLogo;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->getSettings()->setThemeFontLang(new Language(Language::FR_FR));
        $this->defaultLogo = 'images/logo_esperer95.jpg';
    }

    /**
     * Export file.
     */
    public function createDocument(string $content, ?string $title, ?string $logoPath = null, string $infoAdd = ''): void
    {
        $this->title = $title ?? 'Document';
        $this->logoPath = $logoPath;
        $this->infoAdd = $infoAdd;

        /** * @var Section $section */
        $section = $this->addSection();
        $this->addHeader($section);
        $this->addFooter($section);
        $this->addTitle($section);
        $this->addContent($section, $content);
        $this->setDefaultStyleDocument();
    }

    /**
     * Save the document.
     *
     * @return StreamedResponse|Response
     */
    public function save(bool $download = true)
    {
        $filename = \str_replace([' ', '/', '\''], '-', $this->title.'-'.$this->infoAdd);
        $filename = \transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove', $filename);

        // Settings::setPdfRendererPath('..\vendor\dompdf\dompdf');
        // Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        // $path = \dirname(__DIR__).'/../public/uploads/exports/'.(new \DateTime())->format('Y/m/d/');
        // $objWriter->save($path.$this->title.'.docx');

        if (true === $download) {
            return $this->download($objWriter, $filename);
        }

        return new Response();
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
    protected function addHeader(Section $section): void
    {
        // Add first page header
        $header = $section->addHeader(HEADER::FIRST);

        $this->addLogo($header, $this->logoPath);

        // Add sub page header
        $headerSub = $section->addHeader();
        $headerSub->addPreserveText('ESPERER 95 | '.$this->title.' | '.$this->infoAdd, $this->getFontStyleFooter(), [
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
    protected function addTitle(Section $section): void
    {
        if ($this->title) {
            $section->addText($this->title, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
            // $section->addTitle($title);
            // $this->phpWord->addTitleStyle(1, $this->getDefaultFontStyleTitle(), $this->getDefaultParagraphStyleTitle());
        }
    }

    /**
     * Add the body content.
     */
    protected function addContent(Section $section, string $content): void
    {
        Html::addHtml($section, $this->editContent($content), false, false);

        if (str_contains($this->title, 'Grille d\'évaluation sociale')) {
            $this->addLogo($section, $this->logoPath, 60, 'right');
        }
    }

    /**
     * Modifie le contenu Html afin d'ajouter certains éléments de mise en forme supprimés par CKEditor.
     */
    protected function editContent(string $content): string
    {
        // $content = \str_replace(['&lt;br /&gt;'], '<br/>', $content);
        $styleTable = 'width: 50%; border: 1px #b5b5b5 solid;';
        $content = \str_replace('<br>', '<br/>', $content);
        $content = \str_replace('<h3>', '<h3 style="font-size: 21.5px;">', $content);
        $content = \str_replace('<h4>', '<h4 style="font-size: 16px;">', $content);
        $content = \str_replace('<table><thead><tr><th><strong>&nbsp;Ménage', '<table style="'.$styleTable.'"><thead><tr><th><strong>&nbsp;Ménage', $content);
        $content = \str_replace('<table><thead><tr><th><strong>&nbsp;Ressources', '<table style="'.$styleTable.'"><thead><tr><th><strong>&nbsp;Ressources', $content);
        $content = \str_replace('<table><thead><tr><th><strong>&nbsp;Charges', '<table style="'.$styleTable.'"><thead><tr><th><strong>&nbsp;Charges', $content);
        $content = \str_replace('<table>', '<table style="width: 100%; border: 1px #b5b5b5 solid;"> ', $content);
        $content = \str_replace('<thead><tr>', '<thead><tr style="background-color: #e9ecef;"> ', $content);

        return $content;
    }

    /**
     * Add a logo.
     *
     * @param Section|Header|Footer $element
     */
    protected function addLogo($element, string $logoPath = null, int $height = 60, string $align = 'left'): void
    {
        if (\file_exists($this->defaultLogo)) {
            $element->addImage($logoPath ?? $this->defaultLogo, [
                'height' => $height,
                'align' => $align,
            ]);
        }
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
            'cellMargin' => 50,
        ]);
    }

    protected function getStyleTable(): array
    {
        return [
            'borderColor' => 'b5b5b5', 'borderSize' => 6, 'cellMargin' => 50,
        ];
    }

    /**
     * Download file.
     */
    protected function download(WriterInterface $objWriter, string $filename): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.ms-word');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename.'.docx');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($objWriter) {
            $objWriter->save('php://output');
        });

        return $response;
    }
}
