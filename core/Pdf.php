<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
    /** Renders an HTML string to PDF bytes via DomPDF. */
    public static function fromHtml(string $html, string $paper = 'a4'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper($paper, 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }
}
