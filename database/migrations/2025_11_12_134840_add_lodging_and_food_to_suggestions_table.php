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
            // "おすすめ度" の後ろに2つのカラムを追加

            // おすすめの宿泊先
            $table->string('accommodation')->nullable()->after('recommendation_score');

            // 名産物・料理
            $table->string('local_food')->nullable()->after('accommodation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropColumn(['accommodation', 'local_food']);
        });
    }
};
