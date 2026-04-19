<?php

namespace App\Http\Controllers;

use App\Actions\GenerateTripSummaryAction;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AiSummaryController extends Controller
{
    /**
     * AI要約を生成・保存する (★ 画像認識対応)
     */
    public function generate(Trip $trip, GenerateTripSummaryAction $generateTripSummary): RedirectResponse
    {
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $generateTripSummary($trip);

            return redirect()->route('trips.show', $trip)
                ->with('success', 'AIによる要約が完了しました！');
        } catch (\Throwable $e) {
            return redirect()->route('trips.show', $trip)
                ->with('error', $e->getMessage());
        }
    }
}
