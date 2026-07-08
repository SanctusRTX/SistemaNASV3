<?php

namespace App\Http\Controllers;

use App\Services\SystemStatsService;
use Illuminate\Http\JsonResponse;

class SystemStatsController extends Controller
{
    public function overview(SystemStatsService $stats): JsonResponse
    {
        if (!session('loggedin')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        return response()->json($stats->getOverview());
    }
}
