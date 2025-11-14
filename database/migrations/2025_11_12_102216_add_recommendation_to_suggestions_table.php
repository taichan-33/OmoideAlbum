<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            // AIが生成した提案内容（Trix HTML）の後ろに

            // おすすめ度 (1〜5の整数)
            $table->tinyInteger('recommendation_score')->nullable()->after('content');

            // 日程表用のHTMLテーブル
            $table->text('itinerary_table')->nullable()->after('recommendation_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            //
        });
    }
};
