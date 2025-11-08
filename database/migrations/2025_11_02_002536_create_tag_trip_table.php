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
        Schema::create('tag_trip', function (Blueprint $table) {
            // どの旅行(trip_id)に
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            // どのタグ(tag_id)を付けるか
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            // 2つのIDの組み合わせで重複を防ぐ (PRIMARY KEY)
            $table->primary(['trip_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_trip');
    }
};
