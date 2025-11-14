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
            // 既存の itinerary_table カラムを削除
            $table->dropColumn('itinerary_table');

            // 新しく itinerary_data カラムを JSON 型で追加
            $table->json('itinerary_data')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     * (ロールバックした場合に戻せるよう、逆の操作を定義)
     */
    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropColumn('itinerary_data');
            $table->text('itinerary_table')->nullable()->after('content');
        });
    }
};
