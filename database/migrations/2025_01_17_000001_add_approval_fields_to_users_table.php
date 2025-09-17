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
            // 승인 상태 관련 필드
            if (!Schema::hasColumn('users', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('email_verified_at')
                    ->comment('승인 상태');
            }
            
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()
                    ->after('approval_status')
                    ->comment('승인 일시');
            }
            
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()
                    ->after('approved_at')
                    ->comment('승인한 관리자 ID');
            }
            
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()
                    ->after('approved_by')
                    ->comment('거부 사유');
            }
            
            if (!Schema::hasColumn('users', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()
                    ->after('rejection_reason')
                    ->comment('거부 일시');
            }
            
            if (!Schema::hasColumn('users', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()
                    ->after('rejected_at')
                    ->comment('거부한 관리자 ID');
            }
            
            // 인덱스 추가
            $table->index('approval_status');
            $table->index('approved_at');
            $table->index('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 인덱스 삭제
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['approved_at']);
            $table->dropIndex(['rejected_at']);
            
            // 컬럼 삭제
            $table->dropColumn([
                'approval_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
                'rejected_at',
                'rejected_by'
            ]);
        });
    }
};