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
            $table->string('ma_bill')->unique();

            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('user_addresses_id')->nullable()->constrained('user_addresses');

            $table->dateTime('order_date');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('branch_address')->nullable();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers');

            $table->text('note')->nullable(); 
            $table->enum('order_type', ['in_restaurant', 'online'])->default('online');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'shipping', 'completed', 'cancelled', 'failed'])->default('pending');

            $table->string('table_number')->nullable(); // Số bàn (nếu ăn tại quán)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            Schema::dropIfExists('bills');
            // $table->dropColumn(['ma_bill', 'user_id', 'order_date', 'total_money', 'branch_address', 'payment_id', 'voucher_id', 'note', 'order_type', 'status', 'table_number', 'customer_name', 'customer_phone']);
        });
    }
};
