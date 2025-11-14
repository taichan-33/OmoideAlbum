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
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();

            // どのユーザーへの提案か (Userが消えたら提案も消える)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // AIが生成した提案タイトル (例: 「次の記念日にどうですか？ 北陸・金沢グルメ旅」)
            $table->string('title');

            // AIが生成した提案内容（場所、観光地、泊数、理由など）
            // Trix Editor のHTML形式で保存する
            $table->text('content');

            $table->timestamps(); // created_at と updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
