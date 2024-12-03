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
        Schema::table('tables', function (Blueprint $table) {
            $table->integer('min_guest')->nullable();
            $table->integer('max_guest')->nullable();
            $table->decimal('deposit', 10, 2)->nullable(); // Giá đặt cọc, ví dụ 100.00
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('min_guest');
            $table->dropColumn('max_guest');
            $table->dropColumn('deposit');
        });
    }
};
