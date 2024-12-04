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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table');
            $table->text('description')->nullable();
            $table->integer('min_guest')->nullable();
            $table->integer('max_guest')->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->enum('reservation_status', ['open', 'close', 'pending'])->default('close');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
