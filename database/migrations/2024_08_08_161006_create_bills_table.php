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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('ma_bill');
            $table->foreignId('user_id')->constrained('users');
            $table->date('order_date');
            $table->decimal('total_money', 15, 2);
            $table->string('address'); // chi nhánh
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers');
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
