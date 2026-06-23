<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class InventoryController extends Controller
{
    #[OA\Get(
        path: '/legacy/materiales-bajo-stock',
        tags: ['legacy'],
        summary: 'Low stock materials',
        description: 'Returns packaging materials whose stock is below 10 units.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of low-stock materials',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'material', type: 'string', example: 'BOX_SMALL'),
                            new OA\Property(property: 'stock', type: 'integer', example: 5),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Database connection error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Database connection error'),
                    ]
                )
            ),
        ]
    )]
    public function materialesBajoStock(): JsonResponse
    {
        try {
            $materials = DB::table('inventory')
                ->where('stock', '<', 10)
                ->orderBy('code')
                ->get(['code as material', 'stock']);

            return response()->json($materials);
        } catch (\Exception $e) {
            Log::error('Error querying inventory: ' . $e->getMessage());

            return response()->json(
                ['error' => 'Database connection error'],
                500
            );
        }
    }
}
