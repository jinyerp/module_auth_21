<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 약관 테이블 생성
     *
     * 이 테이블은 시스템의 모든 약관 정보를 관리합니다.
     *
     * 도메인 지식:
     * - 이용약관, 개인정보처리방침, 마케팅 수신 동의 등 다양한 약관 관리
     * - 약관 버전 관리 및 시행일/만료일 관리로 법적 요구사항 준수
     * - 필수/선택 약관 분류로 사용자 경험 최적화
     * - 약관 활성화 상태 및 표시 순서 관리
     * - 약관 동의 통계 정보로 분석 및 보고 지원
     * - GDPR, 개인정보보호법, 전자상거래법 등 법적 준수
     * - 약관 변경 시 사용자 재동의 요구사항 대응
     * - 다국어 지원을 위한 메타데이터 구조
     *
     * 법적 목적:
     * - 사용자 동의 이력의 법적 증거 보존
     * - 약관 변경 이력 추적 및 관리
     * - 개인정보처리방침 준수 증명
     */
    public function up(): void
    {
        Schema::create('user_terms', function (Blueprint $table) {
            // ===== 기본 식별자 =====
            $table->id();

            // ===== 기본 정보 =====
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('content')->nullable();
            $table->text('description')->nullable();

            // ===== 약관 설정 =====
            $table->enum('type', ['required', 'optional'])->default('required');
            $table->string('version')->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);

            // ===== 기간 설정 =====
            $table->timestamp('effective_date')->nullable();
            $table->timestamp('expiry_date')->nullable();

            // ===== 관리 정보 =====
            $table->unsignedBigInteger('manager_id');
            $table->string('blade')->nullable();

            // ===== 통계 정보 =====
            $table->integer('users')->default(0);

            // ===== 레거시 필드 =====
            $table->string('enable')->default(1);
            $table->string('required')->default(1);
            $table->integer('pos')->default(1);

            // ===== 확장 정보 =====
            $table->json('metadata')->nullable();

            // ===== 시간 정보 =====
            $table->timestamps();

            // ===== 인덱스 =====
            $table->index(['type', 'is_active']);
            $table->index('display_order');
            $table->index('manager_id');
            $table->index('slug');
            $table->index('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_terms');
    }
};
