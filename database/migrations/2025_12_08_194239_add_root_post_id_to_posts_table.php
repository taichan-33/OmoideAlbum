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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('root_post_id')->nullable()->after('parent_post_id');
            $table->foreign('root_post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->index('root_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['root_post_id']);
            $table->dropColumn('root_post_id');
        });
    }
};
