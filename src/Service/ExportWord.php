<?php

namespace App\Service;

use PhpOffice\PhpWord\Element\Header;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ExportWord
{
    protected $phpWord;
    protected $slugger;

    protected $title;
    protected $logo = false;
    protected $logoPath;
    protected $infoAdd;
    protected $defaultLogo;
    protected $fullHTML;

    public function __construct($fullHTML = false)
    {
        $this->phpWord = new PhpWord();
        $this->slugger = new AsciiSlugger();
        $this->fullHTML = $fullHTML;
        $this->phpWord->getSettings()->setThemeFontLang(new Language(Language::FR_FR));
        $this->defaultLogo = 'images/logo_esperer95.png';
    }

    /**
     * Create Word document.
     */
    public function createDocument(string $content, ?string $title, ?string $logoPath = null, string $infoAdd = ''): void
    {
        $this->title = $title ?? 'Note';
        $this->logoPath = $logoPath;
        $this->infoAdd = $infoAdd;

        $section = $this->addSection();
        $this->addTitle($section);
        $this->addContent($section, $content);
        $this->addHeader($section);
        $this->addFooter($section);
        $this->setDefaultStyleDocument();
    }

    /**
     * Save the document.
     */
    public function save()
    {
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
    }

    /**
     * Download file.
     *
     * @return Response|StreamedResponse
     */
    public function download($download = true)
    {
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.ms-word');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$this->getFilename().'.docx');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($objWriter) {
            $objWriter->save('php://output');
        });

        return $download === true ? $response : new Response();
    }

    /**
     * Get the formated file name.
     */
    protected function getFileName(): string
    {
        $slug = $this->slugger->slug($this->title.'-'.$this->infoAdd);

        return (new \DateTime())->format('Y_m_d_').$slug;
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
        $headerSub->addPreserveText('ESPERER 95 | '.$this->title.($this->infoAdd ? ' | '.$this->infoAdd : ''), $this->getFontStyleFooter(), [
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
        $footer->addPreserveText((new \Datetime())->format('d/m/Y').'                                                                           
            {PAGE}/{NUMPAGES}', $this->getFontStyleFooter(), [
            'alignment' => 'right',
            // 'positioning' => 'absolute',
        ]);

        // Add footer for all other pages
        $footer = $section->addFooter();
        $footer->addPreserveText('{PAGE}/{NUMPAGES}', $this->getFontStyleFooter(), [
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
        Html::addHtml($section, $this->formatContent($content), $this->fullHTML, false);

        if (true === $this->logo) {
            $this->addLogo($section, $this->logoPath, 60, 'right');
        }
    }

    /**
     * Modifie le contenu HTML afin d'ajouter certains éléments de mise en forme supprimés par CKEditor.
     */
    protected function formatContent(string $content): string
    {
        $styleTable = 'width: 50%; border: 1px #b5b5b5 solid;';
        $content = \str_replace(
            ['<br>', '<hr>', '<h2>', '<h3>'],
            ['<br/>', '<hr/>', '<h2 style="font-size: 21.5px;">', '<h3 style="font-size: 16px;">'],
            $content
        );
        $content = \str_replace('<h2', '<br/><h2', $content);
        $content = \str_replace('<table><thead><tr><th><strong>Ménage', '<table style="'.$styleTable.'"><thead><tr><th><strong>Ménage', $content);
        $content = \str_replace('<table><thead><tr><th><strong>Ressources', '<table style="'.$styleTable.'"><thead><tr><th><strong>Ressources', $content);
        $content = \str_replace('<table><thead><tr><th><strong>Charges', '<table style="'.$styleTable.'"><thead><tr><th><strong>Charges', $content);
        $content = \str_replace('<table>', '<table style="width: 100%; border: 1px #b5b5b5 solid;">', $content);
        $content = \str_replace(['<td><p style="text-align:left;">', '<td><p style="text-align:justify;">'], '<td>&nbsp;', $content);
        $content = \str_replace(['<td><p', '</p></td>'], ['<td', '</td>'], $content);
        $content = \str_replace('<thead><tr>', '<thead><tr style="background-color: #e9ecef;">&nbsp;', $content);
        $content = \str_replace(
            ['<th>', '</th>', '<td>', '</td>'],
            ['<th>&nbsp;', '&nbsp;</th>', '<td>&nbsp;', '&nbsp;</td>'],
            $content
        );
        $content = \str_replace('&amp;', 'et', $content); // &amp; provoque une erreur à l'ouverture du fichier Word.
        $content = \str_replace(' & ', ' et ', $content); // & provoque une erreur à l'ouverture du fichier Word.

        if (\strstr($content, '<br/>{LOGO_SIGNATURE}')) {
            $content = \str_replace('<br/>{LOGO_SIGNATURE}', '', $content);
            $this->logo = true;
        }

        return $content;
    }

    /**
     * Add a logo.
     *
     * @param Section|Header|Footer $element
     */
    protected function addLogo($element, string $logoPath = null, int $height = 60, string $align = 'left'): void
    {
        if (\file_exists($logoPath) || \file_exists($this->defaultLogo)) {
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
            'name' => 'Calibri',
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
            'name' => 'Calibri',
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
            'spaceAfter' => 400,
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
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(11);

        $this->phpWord->setDefaultParagraphStyle([
            'spaceAfter' => 120,
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
}
