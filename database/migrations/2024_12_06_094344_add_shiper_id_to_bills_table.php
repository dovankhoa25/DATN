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
        Schema::table('bills', function (Blueprint $table) {
            Schema::table('bills', function (Blueprint $table) {
                $table->unsignedBigInteger('shiper_id')->nullable()->after('status'); // Sau cột 'status'

                $table->foreign('shiper_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['shiper_id']);
            $table->dropColumn('shiper_id');
        });
    }
};
