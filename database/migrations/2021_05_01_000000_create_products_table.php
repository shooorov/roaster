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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('english_name')->nullable();
            $table->decimal('rate', 16, 2)->nullable();
            $table->decimal('discount', 16, 2)->nullable();
            $table->decimal('production_cost', 16, 2)->nullable();
            $table->decimal('margin_percent', 16, 2)->nullable();
            $table->decimal('margin_amount', 16, 2)->nullable();
            $table->boolean('vat_applicable')->default(true);
            $table->longText('description')->nullable();
            $table->integer('number_of_persons')->default(1);
            $table->string('image')->nullable();

            $table->foreignId('product_category_id')->nullable()->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('products');
    }
};
