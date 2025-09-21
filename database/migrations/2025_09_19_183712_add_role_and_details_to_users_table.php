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
            $table->enum('role', ['super_admin', 'admin', 'nurse', 'inventory_manager', 'account_manager'])->default('nurse')->after('email');
            $table->string('employee_id')->unique()->nullable()->after('name');
            $table->string('department')->nullable()->after('role');
            $table->string('contact_number')->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('contact_number');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('created_by')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'employee_id', 
                'department',
                'contact_number',
                'is_active',
                'last_login_at',
                'created_by'
            ]);
        });
    }
};
