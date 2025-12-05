<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 既存データを配列形式のJSONに変換
        \DB::table('trips')->get()->each(function ($trip) {
            // 既にJSON配列っぽければスキップ（再実行時など）
            $val = $trip->prefecture;
            if (!is_string($val))
                return;

            // "[\"...\"]" の形式でなければ配列化
            // 先頭が [ でなければ
            if (strpos($val, '[') !== 0) {
                \DB::table('trips')
                    ->where('id', $trip->id)
                    ->update(['prefecture' => json_encode([$val], JSON_UNESCAPED_UNICODE)]);
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->text('prefecture')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            //
        });
    }
};
