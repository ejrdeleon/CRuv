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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('resident')->after('password')->index(); // 'resident', 'staff', 'admin'
            $table->string('phone', 30)->nullable()->after('role');
            $table->string('barangay', 100)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('barangay');
            $table->unsignedBigInteger('department_id')->nullable()->after('address')->index();
            $table->boolean('is_active')->default(true)->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'barangay', 'address', 'department_id', 'is_active']);
        });
    }
};
