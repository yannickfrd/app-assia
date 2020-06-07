<?php

namespace App\Service;

use Dompdf\Dompdf;

class ExportPDF
{
    public function __construct()
    {
    }

    public function init()
    {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();

        $dompdf->loadHtml('hello world');

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->set_option('defaultFont', 'Courier');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}
