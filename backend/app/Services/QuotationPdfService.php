<?php

namespace App\Services;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class QuotationPdfService
{
    /**
     * Generate PDF binary content or download response for a quotation.
     */
    public function generate(Quotation $quotation): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
              'defaultFont' => 'sans-serif',
          ]);
    }

    /**
     * Download stream response for a quotation.
     */
    public function download(Quotation $quotation): Response
    {
        $pdf = $this->generate($quotation);
        $filename = "Cotizacion_ViajaTranqui_VTQ-" . str_pad((string)$quotation->id, 5, '0', STR_PAD_LEFT) . ".pdf";

        return $pdf->download($filename);
    }
}
