<?php

namespace Lza\LazyAdmin\Utility\Tool;


use FPDM;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Pdf Form helps fill PDF
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PdfFormFiller
{
    use Singleton;

    const OUTPUT_STRING = 'S';
    const MIME_TYPE = 'application/pdf';

    /**
     * @throws
     */
    public function fill(
        $filePath, $fields, $useCheckboxParser = true, $utf8 = true, $outputType = self::OUTPUT_STRING
    )
    {
        $pdf = new FPDM($filePath);
        $pdf->useCheckboxParser = $useCheckboxParser;
        $pdf->Load($fields, $utf8);
        $pdf->Merge();
        return $pdf->Output($outputType);
    }
}
