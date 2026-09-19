<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Super Score API is running.',
            'data' => [
                'application' => 'Super Score',
                'version' => 'v1',
            ],
        ]);
    }
}
