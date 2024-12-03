<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedDecimal('discount_percentage', 5, 2)->default(0)->after('value');
            $table->unsignedDecimal('max_discount_value', 10, 2)->nullable()->after('discount_percentage');
        });
    }


    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'max_discount_value']);
        });
    }
};
