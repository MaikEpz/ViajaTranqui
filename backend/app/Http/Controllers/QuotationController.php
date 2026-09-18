<?php

namespace App\Http\Controllers;

use App\Domain\Actions\CalculateQuotationAction;
use App\Domain\Actions\ContractQuotationAction;
use App\Domain\Actions\CreateQuotationAction;
use App\Domain\Exceptions\InvalidTravelDatesException;
use App\Domain\Exceptions\QuotationAlreadyContractedException;
use App\Http\Requests\CalculateQuotationRequest;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Quotation;
use App\Services\QuotationPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador HTTP para el ciclo de vida de cotizaciones y seguros de viaje.
 * Sigue el patrón Slim Controller delegando la lógica a los Casos de Uso (Actions).
 */
class QuotationController extends Controller
{
    public function __construct(
        protected QuotationPdfService $pdfService
    ) {}

    /**
     * Pre-cálculo interactivo de cotización sin persistir.
     */
    public function calculate(CalculateQuotationRequest $request, CalculateQuotationAction $action): JsonResponse
    {
        try {
            $pricing = $action->execute(
                $request->validated('destination_region'),
                $request->validated('start_date'),
                $request->validated('end_date')
            );

            return response()->json([
                'success' => true,
                'data'    => $pricing->toArray(),
            ]);
        } catch (InvalidTravelDatesException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Registra una nueva cotización en estado 'Cotizado' a través de CreateQuotationAction y su DTO.
     */
    public function store(StoreQuotationRequest $request, CreateQuotationAction $action): JsonResponse
    {
        try {
            $quotation = $action->execute($request->toDTO());

            return response()->json([
                'success' => true,
                'message' => 'Cotización generada exitosamente.',
                'data'    => $quotation,
            ], 201);
        } catch (InvalidTravelDatesException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta y listado de cotizaciones/seguros con búsqueda, filtros y paginación.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = min((int) $request->query('per_page', 10), 100);

        $quotations = Quotation::query()
            ->search($search)
            ->byStatus($status)
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $quotations->items(),
            'meta'    => [
                'current_page' => $quotations->currentPage(),
                'last_page'    => $quotations->lastPage(),
                'per_page'     => $quotations->perPage(),
                'total'        => $quotations->total(),
            ],
        ]);
    }

    /**
     * Consulta del detalle de una cotización puntual.
     */
    public function show(Quotation $quotation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $quotation,
        ]);
    }

    /**
     * Confirmación y transición de estado a 'Contratado' mediante ContractQuotationAction.
     */
    public function contract(Quotation $quotation, ContractQuotationAction $action): JsonResponse
    {
        try {
            $updated = $action->execute($quotation);

            return response()->json([
                'success' => true,
                'message' => '¡Seguro contratado exitosamente!',
                'data'    => $updated,
            ]);
        } catch (QuotationAlreadyContractedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => $quotation,
            ], 422);
        }
    }

    /**
     * Generación y descarga del comprobante oficial de cotización en PDF.
     */
    public function downloadPdf(Quotation $quotation): Response
    {
        return $this->pdfService->download($quotation);
    }
}
