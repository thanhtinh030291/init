<?php

namespace Lza\LazyAdmin\Utility\Tool;


use Dompdf\Dompdf;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Pdf Builder helps create Pdf
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PdfBuilder
{
    use Singleton;

    const MIME_TYPE = 'application/pdf';

    /**
     * @throws
     */
    private function __construct($size = 'A4', $orientation = 'portrait')
    {
        $this->pdfBuilder = new Dompdf();
        $this->pdfBuilder->set_paper($size, $orientation);
    }

    /**
     * @throws
     */
    public function build($html)
    {
        $this->pdfBuilder->load_html($html);
        $this->pdfBuilder->render();
        return $this->pdfBuilder->output();
    }
}
