<?php

namespace App\Service;

use Dompdf\Css\Stylesheet;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class ExportPDF
{
    protected $dompdf;

    protected $title;
    protected $logoPath;
    protected $infoAdd;
    protected $defaultLogo;

    public function __construct()
    {
        $this->dompdf = new Dompdf();
    }

    /**
     * Create PDF document.
     */
    public function createDocument(string $content, ?string $title, ?string $logoPath = null, string $infoAdd = ''): void
    {
        $this->title = $title ?? 'Document';
        $this->logoPath = $logoPath;
        $this->infoAdd = $infoAdd;

        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->loadHtml($content, 'UTF-8');
        $this->dompdf->render();

        // $options = $this->dompdf->getOptions();
        // $options->setDefaultFont('Courier');
        // $this->dompdf->setOptions($options);
        // $options = new Options([
        //     'defaultFont' => 'sans-serif',
        //     'fontHeightRatio' => 1.1,
        //     'defaultMediaType' => 'all',
        //     'isFontSubsettingEnabled' => true,
        // ]);

        // $styleSheet = new Stylesheet($this->dompdf);
        // $styleSheet->load_css_file(\realpath(\dirname(__DIR__).'/../public/css/pdf.css'));
        // $this->dompdf->setCss($styleSheet);
        // $this->dompdf->setBasePath(\realpath(\dirname(__DIR__).'/../public/css/pdf.css'));

        // $canvas = $this->dompdf->getCanvas();

        // $font = new FontMetrics($canvas, $options);
        // $font->setFontFamily('helvetica', 'bold');

        // $canvas->image(dirname(__DIR__).'/../public/'.$logoPath, 20, 20, 100, 100);
        // $canvas->page_text(275, 810, (new \DateTime())->format('d/m/Y'), 'Calibri', 9);
        // $canvas->page_text(543, 810, '{PAGE_NUM} / {PAGE_COUNT}', 'Calibri', 9);
    }

    /**
     * Save the document.
     *
     * @return StreamedResponse|Response
     */
    public function save(bool $download = true): StreamedResponse
    {
        if (true === $download) {
            return $this->download($this->getFilename());
        }

        return new Response('OK');
    }

    /**
     * Output the generated PDF to Browser.
     */
    public function download(): StreamedResponse
    {
        $this->dompdf->stream($this->getFileName());

        return new StreamedResponse();
    }

    protected function getFileName()
    {
        $filename = \str_replace([' ', '/', '\''], '-', $this->title.'-'.$this->infoAdd);
        $filename = \transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove', $filename);

        return (new \DateTime())->format('Y_m_d_').$filename;
    }

    /**
     * Format the HTML content.
     */
    public function formatContent(string $content, Environment $renderer, string $title, string $logoPath, string $infoAdd): string
    {
        $style = $renderer->render('pdf/style/pdf.css.twig');

        $logoPath = $this->getPathImage($logoPath);

        $headerFooter = $renderer->render('pdf/headerFooterPdf.html.twig', [
            'logo_path' => $logoPath,
            'header_info' => 'ESPERER 95 | '.$title.' | '.$infoAdd,
        ]);

        $content = \str_replace('<h2', '<hr/><h2', $content);
        $content = \str_replace('{LOGO_SIGNATURE}', '<p class="text-right"><img src="'.$logoPath.'" width="120"/></p>', $content);
        $content = $style.$headerFooter."<h1>$title</h1>".$content;

        return $content;
    }

    /**
     * Retourne le chemin d'une image au bon format (base 64).
     */
    public function getPathImage(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        return 'data:image/'.$extension.';base64,'.base64_encode($data);
    }
}
