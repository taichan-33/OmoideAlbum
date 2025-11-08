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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            // ユーザー（夫婦）と紐づけるためのID
            // $table->foreignId('user_id')->constrained();

            $table->string('title'); // 旅行のタイトル
            $table->text('description')->nullable(); // 旅行の説明

            $table->string('prefecture'); // 旅行先の都道府県
            $table->date('start_date'); // 旅行開始日
            $table->date('end_date')->nullable(0); // 旅行終了日
            $table->integer('nights')->default(0); // 泊数
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
