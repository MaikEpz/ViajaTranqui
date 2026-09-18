<?php

namespace App\Http\Controllers;

use App\Domain\Actions\GetCountriesAction;
use Illuminate\Http\JsonResponse;

/**
 * Controlador HTTP para el catálogo de países de destino.
 */
class CountryController extends Controller
{
    /**
     * Retorna el catálogo completo de países utilizando el caso de uso GetCountriesAction.
     */
    public function index(GetCountriesAction $action): JsonResponse
    {
        $countries = $action->execute();

        return response()->json([
            'success' => true,
            'count'   => count($countries),
            'data'    => array_map(fn ($c) => $c->toArray(), $countries),
        ]);
    }
}
