<?php

namespace App\Actions;

use App\Models\PlanningChat;
use Illuminate\Support\Collection;

class GetAiPlannerHistoryAction
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function __invoke(string $prefectureCode, ?int $currentUserId = null): Collection
    {
        return PlanningChat::query()
            ->with('user:id,name')
            ->forPrefecture($prefectureCode)
            ->orderBy('created_at')
            ->get()
            ->map(fn (PlanningChat $chat) => $chat->toPlannerMessage($currentUserId))
            ->values();
    }
}
