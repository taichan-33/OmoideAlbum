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

        switch ($template) {
            case 'basic':
                $items = ['財布', 'スマホ', '充電器', '着替え', '歯ブラシ', '常備薬', '保険証'];
                break;
            case 'onsen':
                $items = ['タオル', 'スキンケア用品', 'ビニール袋', '小銭入れ'];
                break;
            case 'business':
                $items = ['PC', '名刺', 'スーツ', '筆記用具'];
                break;
            case 'summer':
                $items = ['日焼け止め', 'サングラス', '帽子', '虫除けスプレー'];
                break;
            case 'winter':
                $items = ['カイロ', 'マフラー', '手袋', '保湿クリーム'];
                break;
        }

        foreach ($items as $name) {
            // 重複チェック（オプション）
            // if (!$trip->packingItems()->where('name', $name)->exists()) {
            $trip->packingItems()->create(['name' => $name]);
            // }
        }

        return Redirect::back();
    }
}
