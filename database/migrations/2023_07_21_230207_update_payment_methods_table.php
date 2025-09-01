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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('bg_color')->default('#ffffff');
            $table->string('text_color')->default('#6b7280');
            $table->string('text_size')->default('14px');
            $table->string('font_weight')->default('400');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('bg_color');
            $table->dropColumn('text_color');
            $table->dropColumn('text_size');
            $table->dropColumn('font_weight');
        });
    }
};
