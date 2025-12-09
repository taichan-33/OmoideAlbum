<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trips = auth()
            ->user()
            ->trips()
            ->with(['photos' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'title' => $trip->title,
                    'start' => $trip->start_date->format('Y-m-d'),
                    'end' => $trip->end_date->format('Y-m-d'),
                    'thumbnail_url' => $trip->photos->first()?->path
                        ? Storage::url($trip->photos->first()->path)
                        : null,
                    'color' => 'blue',  // Default color for now
                ];
            });

        return Inertia::render('Calendar/Index', [
            'trips' => $trips,
        ]);
    }
}
