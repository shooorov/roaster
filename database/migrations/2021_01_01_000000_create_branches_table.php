<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_code');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('vat_rate', 8, 2)->nullable();
            $table->decimal('delivery_cost', 8, 2)->nullable();
            $table->string('opening_hours')->nullable();
            $table->longText('pos_end_line')->nullable();
            $table->string('barista_target')->nullable();
            $table->string('chef_target')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');
    }
};
