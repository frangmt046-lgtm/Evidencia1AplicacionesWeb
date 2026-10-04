<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained();   // Sales user who registered the order
            $table->string('delivery_address');
            $table->text('notes')->nullable();
            $table->dateTime('ordered_at');
            $table->string('status')->default('Ordered');  // Ordered, In process, In route, Delivered
            $table->timestamps();
            $table->softDeletes();                         // logical delete (deleted_at column)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
