<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AiPlannerController extends Controller
{
    public function index($prefectureCode)
    {
        $chats = DB::table('planning_chats')
            ->leftJoin('users', 'planning_chats.user_id', '=', 'users.id')
            ->where('planning_chats.prefecture_code', $prefectureCode)
            ->select(
                'planning_chats.id',
                'planning_chats.message',
                'planning_chats.is_ai',
                'planning_chats.user_id',
                'planning_chats.created_at',
                'users.name as user_name'
            )
            ->orderBy('planning_chats.created_at', 'asc')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'role' => $chat->is_ai ? 'assistant' : 'user',
                    'content' => $chat->message,
                    'user_id' => $chat->user_id,
                    'user_name' => $chat->is_ai ? 'AI Planner' : ($chat->user_name ?? 'Unknown'),
                    'is_me' => $chat->user_id === Auth::id(),
                ];
            });

        return response()->json($chats);
    }

    public function store(\App\Http\Requests\ChatMessageRequest $request, \App\Services\AiPlannerService $aiPlannerService)
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        $userId = Auth::id();
        $message = $validated['message'];
        $triggerAi = $request->input('trigger_ai', true);
        $prefectureCode = $validated['prefectureCode'];

        // Save user message
        DB::table('planning_chats')->insert([
            'prefecture_code' => $prefectureCode,
            'user_id' => $userId,
            'message' => $message,
            'is_ai' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If not triggering AI, return early
        if (!$triggerAi) {
            return response()->json(['status' => 'success']);
        }

        // Fetch recent history for context (last 10 messages) with user names
        $history = DB::table('planning_chats')
            ->leftJoin('users', 'planning_chats.user_id', '=', 'users.id')
            ->select('planning_chats.*', 'users.name as user_name')
            ->where('planning_chats.prefecture_code', $prefectureCode)
            ->orderBy('planning_chats.created_at', 'desc')
            ->limit(50)
            ->get();

        try {
            // Use Service to generate response
            $aiMessage = $aiPlannerService->generateResponse($prefectureCode, $history);

            // Save AI response
            DB::table('planning_chats')->insert([
                'prefecture_code' => $prefectureCode,
                'user_id' => null,
                'message' => $aiMessage,
                'is_ai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('AiPlanner Error: ' . $e->getMessage());
            return response()->json(['error' => 'AIとの通信に失敗しました。'], 500);
        }
    }
}
