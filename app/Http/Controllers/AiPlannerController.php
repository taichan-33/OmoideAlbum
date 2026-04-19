<?php

namespace App\Http\Controllers;

use App\Actions\GetAiPlannerHistoryAction;
use App\Actions\StoreAiPlannerMessageAction;
use Illuminate\Support\Facades\Auth;

class AiPlannerController extends Controller
{
    public function index(string $prefectureCode, GetAiPlannerHistoryAction $getAiPlannerHistory)
    {
        return response()->json(
            $getAiPlannerHistory($prefectureCode, Auth::id())
        );
    }

    public function store(\App\Http\Requests\ChatMessageRequest $request, StoreAiPlannerMessageAction $storeAiPlannerMessage)
    {
        $validated = $request->validated();

        try {
            $storeAiPlannerMessage(
                Auth::id(),
                $validated['prefectureCode'],
                $validated['message'],
                (bool) ($validated['trigger_ai'] ?? true),
            );

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'AIとの通信に失敗しました。'], 500);
        }
    }
}
