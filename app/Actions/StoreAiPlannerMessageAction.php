<?php

namespace App\Actions;

use App\Models\PlanningChat;
use App\Services\AiPlannerService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StoreAiPlannerMessageAction
{
    public function __construct(
        private readonly AiPlannerService $aiPlannerService,
    ) {
    }

    public function __invoke(int $userId, string $prefectureCode, string $message, bool $triggerAi = true): void
    {
        PlanningChat::query()->create([
            'prefecture_code' => $prefectureCode,
            'user_id' => $userId,
            'message' => $message,
            'is_ai' => false,
        ]);

        if (!$triggerAi) {
            return;
        }

        try {
            $history = PlanningChat::query()
                ->with('user:id,name')
                ->forPrefecture($prefectureCode)
                ->latest('created_at')
                ->limit(50)
                ->get();

            $aiMessage = $this->aiPlannerService->generateResponse($prefectureCode, $history);

            PlanningChat::query()->create([
                'prefecture_code' => $prefectureCode,
                'user_id' => null,
                'message' => $aiMessage,
                'is_ai' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('AiPlanner Error: ' . $e->getMessage());
            throw new RuntimeException('AIとの通信に失敗しました。', previous: $e);
        }
    }
}
