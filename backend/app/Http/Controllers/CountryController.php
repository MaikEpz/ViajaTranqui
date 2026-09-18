<?php

namespace App\Http\Controllers;

use App\Domain\Actions\GetCountriesAction;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * Return list of all destination countries through GetCountriesAction use case.
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
