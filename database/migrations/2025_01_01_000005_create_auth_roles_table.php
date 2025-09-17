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
        // Roles table
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->integer('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('level');
            $table->index('is_active');
        });
        
        // Permissions table
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('group');
            $table->index('is_active');
        });
        
        // Role-Permission pivot table
        Schema::create('auth_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('auth_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('auth_permissions')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role_id', 'permission_id']);
            $table->index('role_id');
            $table->index('permission_id');
        });
        
        // User-Role pivot table
        Schema::create('auth_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('auth_roles')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'role_id']);
            $table->index('user_id');
            $table->index('role_id');
            $table->index('expires_at');
        });
        
        // User-Permission pivot table (direct permissions)
        Schema::create('auth_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('auth_permissions')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'permission_id']);
            $table->index('user_id');
            $table->index('permission_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_user_permissions');
        Schema::dropIfExists('auth_user_roles');
        Schema::dropIfExists('auth_role_permissions');
        Schema::dropIfExists('auth_permissions');
        Schema::dropIfExists('auth_roles');
    }
};