<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds targeted indexes for high-frequency filters, joins, and sorting:
     * - created_at: Prevents full-table filesorts on default DESC ordering.
     * - (user_id, created_at): Fast lookup and chronological order for resident mobile list.
     * - (status, created_at): Accelerates admin queue tabs (e.g. status='submitted' ORDER BY created_at DESC).
     * - (assigned_to, status): Enables quick staff workload filtering.
     * - (category_id, created_at): Speeds up category-specific reporting feeds.
     * - (barangay, status): Optimizes municipal geographic triage.
     * - (latitude, longitude): Bounding box lookups for interactive map queries.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->index('created_at', 'reports_created_at_index');
            $table->index(['user_id', 'created_at'], 'reports_user_id_created_at_index');
            $table->index(['status', 'created_at'], 'reports_status_created_at_index');
            $table->index(['assigned_to', 'status'], 'reports_assigned_to_status_index');
            $table->index(['category_id', 'created_at'], 'reports_category_id_created_at_index');
            $table->index(['barangay', 'status'], 'reports_barangay_status_index');
            $table->index(['latitude', 'longitude'], 'reports_lat_lng_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_created_at_index');
            $table->dropIndex('reports_user_id_created_at_index');
            $table->dropIndex('reports_status_created_at_index');
            $table->dropIndex('reports_assigned_to_status_index');
            $table->dropIndex('reports_category_id_created_at_index');
            $table->dropIndex('reports_barangay_status_index');
            $table->dropIndex('reports_lat_lng_index');
        });
    }
};
