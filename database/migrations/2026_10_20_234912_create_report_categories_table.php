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
        Schema::create('report_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('alert-triangle');
            $table->string('default_priority', 20)->default('medium'); // low, medium, high, urgent
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('is_hazardous')->default(false); // e.g. fallen electrical lines
            $table->text('safety_advisory')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_categories');
    }
};
