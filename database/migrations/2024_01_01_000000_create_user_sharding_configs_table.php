<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 샤딩 설정 테이블 생성
     *
     * 이 테이블은 사용자 데이터베이스 샤딩 설정을 관리합니다.
     *
     * 도메인 지식:
     * - 데이터베이스 샤딩은 대용량 데이터를 여러 테이블로 분산하여 성능을 향상시키는 기술
     * - 사용자 테이블(users)은 시스템에서 가장 큰 데이터를 보유하는 테이블 중 하나
     * - 샤딩 전략은 데이터 분배 방식에 따라 해시 기반과 범위 기반으로 구분
     * - 샤딩 키는 데이터를 어떤 기준으로 분배할지 결정하는 컬럼
     * - 샤드 개수는 시스템 규모와 성능 요구사항에 따라 결정
     *
     * 비즈니스 규칙:
     * - 하나의 테이블에 대해 하나의 활성 샤딩 설정만 허용
     * - 샤딩 설정 변경 시 기존 설정은 비활성화 후 새 설정 적용
     * - 샤딩 키는 해당 테이블에 존재하는 컬럼이어야 함
     * - 샤드 개수는 1-1000 범위 내에서 설정 가능
     */
    public function up(): void
    {
        Schema::create('user_sharding_configs', function (Blueprint $table) {
            $table->id(); // 샤딩 설정 고유 식별자 (Primary Key, Auto Increment)

            $table->string('table_name')->unique(); // 샤딩할 테이블 이름 (예: users, user_profiles)
            // - 시스템에서 샤딩 대상이 되는 테이블명
            // - unique 제약으로 동일 테이블에 대한 중복 설정 방지
            // - 비즈니스 키로 사용되어 샤딩 설정 식별에 활용

            $table->integer('shard_count'); // 샤드 개수 (1-1000 범위)
            // - 생성할 샤드 테이블의 개수
            // - 성능과 관리 복잡도의 균형을 고려하여 설정
            // - 예: 100개 샤드 시 users_shard_0 ~ users_shard_99 생성

            $table->string('shard_key'); // 샤딩 키 (예: id, email, created_at)
            // - 데이터 분배 기준이 되는 컬럼명
            // - 해시 기반: id, email 등 고유값이 있는 컬럼 권장
            // - 범위 기반: created_at, id 등 순차적 값이 있는 컬럼 권장

            $table->enum('shard_strategy', ['hash', 'range'])->default('hash'); // 샤딩 전략
            // - hash: 해시 함수를 사용한 균등 분배 (예: crc32(id) % shard_count)
            // - range: 범위 기반 순차 분배 (예: id % shard_count)
            // - 기본값은 hash로 설정하여 균등한 데이터 분배 보장

            $table->boolean('is_active')->default(true); // 활성화 상태
            // - 현재 적용 중인 샤딩 설정 여부
            // - 새로운 샤딩 설정 생성 시 기존 설정은 false로 변경
            // - 시스템에서 활성 설정만 사용하여 샤드 테이블 결정

            $table->text('description')->nullable(); // 샤딩 설정 설명
            // - 샤딩 설정의 목적과 배경 정보
            // - 관리자가 샤딩 설정을 이해하는데 도움
            // - 선택사항으로 null 허용

            $table->unsignedBigInteger('created_by')->nullable(); // 생성자 ID
            // - 샤딩 설정을 생성한 관리자 ID
            // - 감사 추적을 위한 필드
            // - 관리자 계정 삭제 시를 대비하여 nullable

            $table->unsignedBigInteger('updated_by')->nullable(); // 수정자 ID
            // - 샤딩 설정을 수정한 관리자 ID
            // - 감사 추적을 위한 필드
            // - 관리자 계정 삭제 시를 대비하여 nullable

            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at)
            // - 샤딩 설정의 생성/수정 이력 추적
            // - 감사 및 모니터링 목적으로 활용

            // 인덱스 설정
            $table->index(['table_name', 'is_active']); // 테이블별 활성 설정 조회 성능 향상
            $table->index('shard_strategy'); // 전략별 샤딩 설정 조회 성능 향상
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sharding_configs');
    }
};
