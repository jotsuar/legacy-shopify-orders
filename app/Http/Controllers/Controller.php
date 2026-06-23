<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Shopify Legacy API',
    version: '1.0.0',
    description: 'Legacy PHP component (Laravel) for querying low-stock packaging materials. Connects read-only to the shared PostgreSQL database.'
)]
#[OA\Server(url: '/api', description: 'Legacy API server')]
abstract class Controller
{
}
