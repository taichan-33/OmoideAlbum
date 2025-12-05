<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(\App\Services\StatsService $service): Response
    {
        return Inertia::render('Stats/Index', [
            'stats' => $service->getDashboardStats()
        ]);
    }
}
