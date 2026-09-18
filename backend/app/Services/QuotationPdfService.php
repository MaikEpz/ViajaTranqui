<?php

namespace App\Services;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Servicio de infraestructura para la renderización y exportación de comprobantes en PDF.
 */
class QuotationPdfService
{
    /**
     * Genera el documento PDF a partir de la vista Blade y la cotización con imágenes incrustadas en base64.
     */
    public function generate(Quotation $quotation): \Barryvdh\DomPDF\PDF
    {
        $logoBase64 = null;
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string)file_get_contents($logoPath));
        }

        $flagBase64 = null;
        if (!empty($quotation->destination_flag_url)) {
            try {
                $context = stream_context_create([
                    'http' => ['timeout' => 2, 'user_agent' => 'Mozilla/5.0'],
                    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
                $imgData = @file_get_contents($quotation->destination_flag_url, false, $context);
                if ($imgData !== false) {
                    $flagBase64 = 'data:image/png;base64,' . base64_encode($imgData);
                }
            } catch (\Throwable) {
                $flagBase64 = null;
            }
        }

        return Pdf::loadView('pdf.quotation', [
            'quotation'  => $quotation,
            'logoBase64' => $logoBase64,
            'flagBase64' => $flagBase64,
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled'      => true,
              'defaultFont'          => 'sans-serif',
          ]);
    }

    /**
     * Retorna una respuesta de descarga de flujo de bytes para el navegador.
     */
    public function download(Quotation $quotation): Response
    {
        $pdf = $this->generate($quotation);
        $filename = "Cotizacion_ViajaTranqui_VTQ-" . str_pad((string)$quotation->id, 5, '0', STR_PAD_LEFT) . ".pdf";

        return $pdf->download($filename);
    }
}
