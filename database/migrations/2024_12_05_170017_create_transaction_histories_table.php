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
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->dateTime('transaction_date');
            $table->string('account_number');
            $table->string('code')->nullable();
            $table->text('content')->nullable();
            $table->enum('transfer_type', ['in', 'out']);
            $table->unsignedBigInteger('transfer_amount');
            $table->unsignedBigInteger('accumulated');
            $table->string('sub_account')->nullable();
            $table->string('reference_code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
