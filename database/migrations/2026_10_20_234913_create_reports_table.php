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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('report_categories')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('reports')->nullOnDelete();

            $table->string('title', 200);
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();
            $table->enum('status', [
                'submitted',
                'under_review',
                'verified',
                'assigned',
                'in_progress',
                'resolved',
                'rejected',
                'duplicate',
            ])->default('submitted')->index();

            $table->text('rejection_reason')->nullable();

            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->string('address_text', 255);
            $table->string('barangay', 100)->index();
            $table->string('city', 100)->default('Bay');
            $table->string('province', 100)->default('Laguna');
            $table->string('landmark', 255)->nullable();

            $table->boolean('is_hazard_warning_acknowledged')->default(false);

            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('in_progress_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->text('resolution_notes')->nullable();
            $table->string('resolution_image_path', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
