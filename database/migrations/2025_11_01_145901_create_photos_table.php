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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();

            // どの「旅行」に属しているか (Tripが削除されたら写真も消える)
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();

            // アップロードされたファイルの保存パス
            $table->string('path');

            // 写真のメモ（キャプション）
            $table->text('caption')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
