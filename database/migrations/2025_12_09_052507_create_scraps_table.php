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
        Schema::create('scraps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('url');  // URL can be long
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->string('site_name')->nullable();
            $table->json('metadata')->nullable();  // For any extra OGP data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraps');
    }
};
