<?php

namespace App\Service\File;

use Dompdf\Dompdf;
use Twig\Environment;

/**
 * Service to convert an image to PDF format.
 */
class ImageToPdfConverter
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    private $renderer;
    private $dompdf;

    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
        $this->dompdf = new Dompdf();
    }

    public function convert(string $path, array $options = []): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);

        if (!in_array($type, self::IMAGE_EXTENSIONS)) {
            throw new \Exception('The file is an image with format jpg or png.');
        }

        $data = file_get_contents($path);

        $this->dompdf->setPaper('A4', isset($options['orientation']) ? $options['orientation'] : 'portrait');

        $this->dompdf->loadHtml(
            $this->renderer->render('pdf/imagePdf.html.twig', [
                'orientation' => isset($options['orientation']) ? $options['orientation'] : 'portrait',
                'base64' => 'data:image/'.$type.';base64,'.base64_encode($data),
                'title' => isset($options['title']) ? $options['title'] : '',
                'margin' => isset($options['margin']) ? $options['margin'] : 0,
            ]), 'UTF-8'
        );

        $this->dompdf->render();

        $output = $this->dompdf->output();

        $pdfFilename = str_replace($type, 'pdf', $path);

        file_put_contents($pdfFilename, $output);

        return $pdfFilename;
    }
}
