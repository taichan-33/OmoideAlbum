<?php

namespace App\Http\Controllers;

use App\Models\PackingItem;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PackingItemController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $trip->packingItems()->create([
            'name' => $request->name,
        ]);

        return Redirect::back();
    }

    public function update(Request $request, PackingItem $item)
    {
        $request->validate([
            'is_checked' => 'required|boolean',
        ]);

        $item->update([
            'is_checked' => $request->is_checked,
        ]);

        return Redirect::back();
    }

    public function destroy(PackingItem $item)
    {
        $item->delete();
        return Redirect::back();
    }

    public function storeBatch(Request $request, Trip $trip)
    {
        $template = $request->input('template');
        $items = [];

        $templates = config('packing.templates');
        $items = $templates[$template]['items'] ?? [];

        foreach ($items as $name) {
            // 重複チェック（オプション）
            // if (!$trip->packingItems()->where('name', $name)->exists()) {
            $trip->packingItems()->create(['name' => $name]);
            // }
        }

        return Redirect::back();
    }
}
