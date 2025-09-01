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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->string('number');
            $table->enum('type', ['self', 'delivery'])->default('self');
            $table->decimal('sub_total')->nullable();
            $table->decimal('delivery_cost')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('cash')->nullable();
            $table->decimal('change')->nullable();

            $table->enum('discount_type', ['flat', 'percent'])->default('flat');
            $table->decimal('discount_rate')->nullable()->comment('in percent');
            $table->decimal('discount_amount')->nullable();

            $table->decimal('vat_rate')->nullable();
            $table->decimal('vatable_amount')->nullable()->comment('after removing the value of non taxable items.');
            $table->decimal('vat_amount')->nullable();

            $table->string('dine_type');
            $table->text('delivery_address')->nullable();
            $table->integer('guest_number')->nullable();
            $table->string('note')->nullable();
            $table->string('status')->default('pending');
            $table->json('history')->nullable();

            $table->foreignId('stuff_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('orders');
    }
};
