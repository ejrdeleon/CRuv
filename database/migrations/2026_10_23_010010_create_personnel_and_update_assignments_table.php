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
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('position', 150);
            $table->string('contact_number', 50);
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['department_id', 'is_active']);
            $table->index('name');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('personnel_id')
                ->nullable()
                ->after('department_id')
                ->constrained('personnel')
                ->nullOnDelete();
            $table->index(['department_id', 'personnel_id']);
        });

        Schema::table('report_assignments', function (Blueprint $table) {
            $table->foreignId('personnel_id')
                ->nullable()
                ->after('department_id')
                ->constrained('personnel')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->dropForeign(['personnel_id']);
            $table->dropColumn('personnel_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['department_id', 'personnel_id']);
            $table->dropForeign(['personnel_id']);
            $table->dropColumn('personnel_id');
        });

        Schema::dropIfExists('personnel');
    }
};
