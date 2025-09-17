# 🔐 사용자 관리 및 인증 기능 구현 체크리스트

## ✅ **기본 구조 및 설정 (완료)**

### 📁 파일 구조
- [x] 디렉토리 구조 생성 (App/, config/, database/, resources/, routes/)
- [x] JinyAuthServiceProvider.php (서비스 프로바이더)
- [x] composer.json (패키지 설정)
- [x] config/auth.php (인증 설정)
- [x] Helper.php (헬퍼 함수)

### 🗿️ 모델 및 마이그레이션
- [x] Account.php (사용자 계정 모델)
- [x] AccountLog.php (활동 로그)
- [x] LoginHistory.php (로그인 이력)
- [x] Role.php (역할 관리)
- [x] Grade.php (회원 등급)
- [x] TwoFactorAuth.php (2단계 인증)
- [x] Blacklist.php (블랙리스트)
- [x] DormantAccount.php (휴면계정)
- [x] Country.php (국가 정보)
- [x] 17개의 마이그레이션 파일

### 🛣️ 라우트 설정
- [x] routes/admin.php (관리자 라우트 - `/admin/auth/*`)
- [x] routes/web.php (일반 사용자 라우트)
- [x] routes/api.php (API 라우트 - `/api/auth/*`)

---

## 📝 **컨트롤러 생성 작성 규칙**

### **1. 관리자 컨트롤러 생성 방법**
- **기본 구조 생성**: `admin:make-*` 명령을 사용하여 먼저 기본 구조를 생성
- **기능 수정**: 생성된 기본 구조를 기능에 맞게 코드 수정
- **추가 기능**: 기본 CRUD 기능 외에 추가 기능은 Hook을 이용하여 처리

### **2. 컨트롤러 생성 명령어 예시**
```bash
# 사용자 관리 컨트롤러 생성
php artisan admin:make-controller UserController

# 인증 관리 컨트롤러 생성  
php artisan admin:make-controller AuthController

# 로그 관리 컨트롤러 생성
php artisan admin:make-controller LogController
```

### **3. Hook 활용 방법**
- **기본 CRUD**: `admin:make-*` 명령으로 생성된 기본 기능 활용
- **추가 기능**: Hook을 통해 필요한 기능을 확장
- **커스터마이징**: 각 모듈별 특성에 맞게 Hook으로 기능 추가

---

## 📋 **1단계: 핵심 인증 시스템 (최우선)**

### 1.1 일반 사용자 로그인/로그아웃
- [x] **세션 기반 로그인** (`/login/*`) ✅ AuthLoginController로 구현
  - [x] `GET /login` - 로그인 폼 표시
  - [x] `POST /login` - 로그인 처리
  - [x] 로그인 시도 횟수 제한 (5회 실패 시 계정 잠금)
  - [x] 세션 재생성 (세션 고정 공격 방지)
  - [x] 로그인 성공/실패 로그 기록
  - [x] "로그인 유지" 기능 (Remember Me)
  
  **의존성**: User 모델, Auth 미들웨어, 세션 설정, 로그 시스템
  **구현 단계**:
    1. `php artisan admin:make-controller LoginController` 실행
    2. LoginController에 세션 기반 로그인 메서드 구현
    3. 로그인 시도 횟수 제한 미들웨어 생성
    4. Remember Me 토큰 처리 로직 구현
    5. 로그인 성공/실패 로그 기록 기능 추가
  **AI 명령**: "Laravel에서 세션 기반 로그인 시스템을 구현해주세요. 
  - LoginController 생성 (admin:make-controller 사용)
  - GET /login: 로그인 폼 뷰 반환
  - POST /login: 이메일/비밀번호 검증, 로그인 시도 횟수 체크, 세션 재생성, Remember Me 처리
  - 로그인 시도 횟수 제한: 5회 실패 시 15분 계정 잠금
  - 세션 재생성: 로그인 성공 시 세션 ID 재생성
  - 로그 기록: 성공/실패 시도 모두 기록
  - Remember Me: 30일 유효한 토큰 생성
  - 라우트: web.php에 /login 라우트 추가
  - 뷰: login.blade.php 생성 (Bootstrap 스타일)
  - 미들웨어: ThrottleRequests 커스터마이징"

- [x] **JWT 기반 로그인** (`/signin/*`, `/signup/*`, `/signout`) ✅ JWT 컨트롤러로 구현
  - [x] `GET /signin` - JWT 로그인 폼 표시 ✅ AuthJwtSigninController
  - [x] `POST /signin` - JWT 로그인 처리 (토큰 생성) ✅ AuthJwtSigninController
  - [x] `GET /signin/refresh` - JWT 토큰 갱신 ✅ AuthJwtSigninController
  - [x] `GET /signup` - JWT 회원가입 폼 표시 ✅ AuthJwtSignupController
  - [x] `POST /signup` - JWT 회원가입 처리 (토큰 생성) ✅ AuthJwtSignupController
  - [x] `GET /signout` - JWT 로그아웃 (토큰 무효화) ✅ AuthJwtSignoutController
  - [x] `POST /signout` - JWT 로그아웃 처리 ✅ AuthJwtSignoutController
  - [x] `POST /signout/all` - 모든 기기 로그아웃 ✅ AuthJwtSignoutController
  - [x] JWT 토큰 저장 (jwt_tokens 테이블) ✅
  - [x] JWT 토큰 무효화 처리 ✅
  
  **의존성**: JWT 패키지 (tymon/jwt-auth), User 모델, 토큰 테이블, Redis 캐시
  **구현 단계**:
    1. `composer require tymon/jwt-auth` 설치
    2. `php artisan jwt:secret` 실행하여 JWT 시크릿 생성
    3. `php artisan admin:make-controller JwtAuthController` 실행
    4. JWT 토큰 테이블 마이그레이션 생성
    5. JWT 미들웨어 설정
  **AI 명령**: "Laravel에서 JWT 기반 로그인/회원가입 시스템을 구현해주세요.
  - JWT 패키지 설치 및 설정 (tymon/jwt-auth)
  - JwtAuthController 생성 (admin:make-controller 사용)
  - GET /signin: JWT 로그인 폼 뷰 반환
  - POST /signin: 이메일/비밀번호 검증 후 JWT 토큰 생성 (access_token, refresh_token)
  - GET /signin/refresh: refresh_token으로 새로운 access_token 발급
  - GET /signup: JWT 회원가입 폼 뷰 반환
  - POST /signup: 회원가입 처리 후 JWT 토큰 자동 발급
  - GET/POST /signout: JWT 토큰 무효화 (블랙리스트 처리)
  - 토큰 자동 갱신: 프론트엔드에서 401 에러 시 자동 refresh
  - 토큰 만료 처리: access_token 1시간, refresh_token 30일
  - 라우트: web.php에 JWT 관련 라우트 추가
  - 뷰: signin.blade.php, signup.blade.php 생성
  - 토큰 저장: Redis 또는 데이터베이스에 토큰 정보 저장"

- [x] **로그아웃 기능** (`/logout`) ✅ AuthLogoutController로 구현
  - [x] `GET /logout` - 세션 로그아웃
  - [x] `POST /logout` - AJAX 로그아웃
  - [x] 세션 무효화 및 재생성
  - [x] 로그아웃 로그 기록
  
  **의존성**: 세션 기반 로그인, 로그 시스템, Auth 미들웨어
  **구현 단계**:
    1. LoginController에 로그아웃 메서드 추가
    2. 세션 무효화 및 재생성 로직 구현
    3. 로그아웃 로그 기록 기능 추가
    4. AJAX 로그아웃 처리 구현
  **AI 명령**: "Laravel에서 세션 기반 로그아웃 기능을 구현해주세요.
  - LoginController에 logout 메서드 추가
  - GET /logout: 세션 로그아웃 처리 (Auth::logout(), 세션 무효화, 재생성)
  - POST /logout: AJAX 로그아웃 처리 (JSON 응답)
  - 세션 무효화: session()->invalidate() 사용
  - 세션 재생성: session()->regenerate() 사용
  - 로그 기록: 로그아웃 시간, IP 주소, 사용자 ID 기록
  - 리다이렉트: 로그아웃 후 /login 페이지로 이동
  - 라우트: web.php에 /logout 라우트 추가 (auth 미들웨어 적용)"

### 1.2 일반 사용자 회원가입
- [x] **회원가입 폼** (`/register/*`) ✅ AuthRegisterController로 구현
  - [x] `GET /register` - 회원가입 폼 표시
  - [x] `POST /register` - 회원가입 처리
  - [x] 기본 정보 입력 (이름, 이메일, 비밀번호, 전화번호)
  - [x] 비밀번호 강도 검증
  - [x] 이메일 중복 검사
  - [x] 약관 동의 체크박스
  
  **의존성**: User 모델, 이메일 검증, 비밀번호 해싱, 약관 모델
  **구현 단계**:
    1. `php artisan admin:make-controller RegisterController` 실행
    2. 회원가입 폼 뷰 생성
    3. 유효성 검사 규칙 정의
    4. 이메일 중복 검사 로직 구현
    5. 비밀번호 강도 검증 구현
  **AI 명령**: "Laravel에서 회원가입 시스템을 구현해주세요.
  - RegisterController 생성 (admin:make-controller 사용)
  - GET /register: 회원가입 폼 뷰 반환 (register.blade.php)
  - POST /register: 회원가입 처리 및 유효성 검사
  - 입력 필드: name, email, password, password_confirmation, phone, terms_agreed
  - 유효성 검사 규칙:
    * name: required|string|max:255
    * email: required|email|unique:users
    * password: required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/
    * phone: required|string|regex:/^[0-9-+()]+$/
    * terms_agreed: required|accepted
  - 이메일 중복 검사: 실시간 AJAX 검증
  - 비밀번호 강도: 대소문자, 숫자, 특수문자 포함 검증
  - 약관 동의: 필수 체크박스
  - 성공 시: 이메일 인증 페이지로 리다이렉트
  - 에러 처리: 유효성 검사 실패 시 폼에 에러 메시지 표시
  - 라우트: web.php에 /register 라우트 추가"

### 1.3 사용자 홈 관리
- [x] **사용자 홈** (`/home/*`) ✅ HomeController로 구현
  - [x] `GET /home` - 사용자 대시보드 ✅ HomeController::index
  - [x] `GET /home/profile` - 프로필 조회 ✅ HomeController::profile
  - [x] `GET /home/profile/edit` - 프로필 수정 폼 ✅ HomeController::editProfile
  - [x] `PUT /home/profile` - 프로필 수정 ✅ HomeController::updateProfile
  - [x] `GET /home/settings` - 계정 설정 ✅ HomeController::settings
  - [x] `PUT /home/settings` - 계정 설정 수정 ✅ HomeController::updateSettings
  - [x] `GET /home/account/delete` - 계정 삭제 폼 ✅ HomeController::deleteForm
  - [x] `DELETE /home/account` - 계정 삭제 처리 ✅ HomeController::deleteAccount
  
  **의존성**: 인증된 사용자, User 모델, 프로필 모델, auth 미들웨어
  **구현 단계**:
    1. `php artisan admin:make-controller HomeController` 실행
    2. 사용자 대시보드 뷰 생성
    3. 프로필 관리 기능 구현
    4. 계정 설정 기능 구현
    5. 인증 미들웨어 적용
  **AI 명령**: "Laravel에서 사용자 홈 대시보드를 구현해주세요.
  - HomeController 생성 (admin:make-controller 사용)
  - GET /home: 사용자 대시보드 (대시보드.blade.php)
    * 최근 활동 내역 표시
    * 계정 상태 정보
    * 빠른 액션 버튼 (프로필 수정, 설정 변경)
  - GET /home/profile: 프로필 조회 (profile.blade.php)
    * 사용자 기본 정보 표시
    * 프로필 사진 표시
  - PUT /home/profile: 프로필 수정 처리
    * 이름, 이메일, 전화번호 수정
    * 프로필 사진 업로드
    * 유효성 검사 적용
  - GET /home/settings: 계정 설정 (settings.blade.php)
    * 비밀번호 변경
    * 알림 설정
    * 개인정보 설정
  - PUT /home/settings: 계정 설정 수정 처리
  - 미들웨어: auth 미들웨어 적용하여 인증된 사용자만 접근
  - 라우트: web.php에 /home/* 라우트 추가 (auth 미들웨어 그룹)
  - 권한: 본인 정보만 수정 가능하도록 권한 체크"

### 1.4 관리자 사용자 관리
- [x] **사용자 목록 조회** (`/admin/auth/accounts`) ✅ AuthAccounts로 구현
  - [x] `GET /admin/auth/accounts` - 사용자 목록 (페이징 20개씩)
  - [x] `GET /admin/auth/accounts/search` - 사용자 검색
  - [x] `GET /admin/auth/accounts/filter` - 필터링 (상태별, 가입일별)
  - [ ] `GET /admin/auth/accounts/export` - CSV 다운로드
  
  **의존성**: 관리자 권한, User 모델, 페이징, 검색 기능, CSV 생성
  **구현 단계**:
    1. `php artisan admin:make-controller AdminUserController` 실행
    2. 사용자 목록 뷰 생성 (Livewire 테이블 사용)
    3. 검색 및 필터링 기능 구현
    4. CSV 다운로드 기능 구현
    5. 관리자 권한 미들웨어 적용
  **AI 명령**: "Laravel에서 관리자용 사용자 목록 관리 시스템을 구현해주세요.
  - AdminUserController 생성 (admin:make-controller 사용)
  - GET /admin/auth/users: 사용자 목록 (users/index.blade.php)
    * Livewire AdminTable 컴포넌트 사용
    * 페이징: 15개씩 표시
    * 정렬: 이름, 이메일, 가입일, 상태별 정렬
    * 액션 버튼: 상세보기, 수정, 삭제, 상태변경
  - GET /admin/auth/users/search: 사용자 검색
    * 실시간 검색 (AJAX)
    * 이름, 이메일, 전화번호로 검색
    * 검색 결과 하이라이트
  - GET /admin/auth/users/filter: 필터링
    * 상태별: 활성, 비활성, 정지, 휴면
    * 가입일별: 오늘, 이번주, 이번달, 올해
    * 권한별: 일반사용자, 관리자, 슈퍼관리자
  - GET /admin/auth/users/export: CSV 다운로드
    * 선택된 사용자 또는 전체 사용자 내보내기
    * 필드: ID, 이름, 이메일, 전화번호, 상태, 가입일
  - 미들웨어: admin 미들웨어 적용
  - 권한: 관리자만 접근 가능
  - 라우트: web.php에 /admin/auth/users/* 라우트 추가"

- [x] **사용자 상세 정보** (`/admin/auth/accounts/{id}`) ✅ AuthAccounts로 구현
  - [x] `GET /admin/auth/accounts/{id}` - 사용자 상세 조회
  - [x] `GET /admin/auth/accounts/{id}/edit` - 사용자 수정 폼
  - [x] `PUT /admin/auth/accounts/{id}` - 사용자 정보 수정
  - [x] `DELETE /admin/auth/accounts/{id}` - 사용자 삭제 (소프트 삭제)
  
  **의존성**: 관리자 권한, User 모델, 소프트 삭제, SoftDeletes trait
  **구현 단계**:
    1. AdminUserController에 상세 관리 메서드 추가
    2. 사용자 상세 뷰 생성
    3. 사용자 수정 폼 구현
    4. 소프트 삭제 기능 구현
    5. 권한 검증 로직 추가
  **AI 명령**: "Laravel에서 관리자용 사용자 상세 관리 시스템을 구현해주세요.
  - AdminUserController에 상세 관리 메서드 추가
  - GET /admin/auth/users/{id}: 사용자 상세 조회 (users/show.blade.php)
    * 사용자 기본 정보 표시
    * 프로필 사진 표시
    * 계정 상태 정보
    * 최근 활동 내역
    * 로그인 이력
    * 액션 버튼: 수정, 삭제, 상태변경
  - GET /admin/auth/users/{id}/edit: 사용자 수정 폼 (users/edit.blade.php)
    * 이름, 이메일, 전화번호 수정
    * 계정 상태 변경 (활성/비활성/정지)
    * 권한 변경 (일반사용자/관리자)
    * 프로필 사진 변경
  - PUT /admin/auth/users/{id}: 사용자 정보 수정 처리
    * 유효성 검사 적용
    * 이메일 중복 검사 (본인 제외)
    * 수정 로그 기록
  - DELETE /admin/auth/users/{id}: 사용자 소프트 삭제
    * SoftDeletes trait 사용
    * 삭제 로그 기록
    * 관련 데이터 처리 (세션, 토큰 등)
  - 권한: 관리자만 접근 가능
  - 유효성 검사: 이메일 형식, 전화번호 형식 등
  - 라우트: web.php에 /admin/auth/users/{id}/* 라우트 추가"

## 📋 **2단계: 보안 및 인증 강화 (높은 우선순위)**

### 2.1 이메일 인증
- [x] **이메일 인증** ✅ EmailVerificationController로 구현
  - [x] `GET /email/verify` - 이메일 인증 안내 페이지 ✅ EmailVerificationController::notice
  - [x] `GET /email/verify/{id}/{hash}` - 이메일 인증 처리 ✅ EmailVerificationController::verify
  - [x] `POST /email/verification-notification` - 인증 이메일 재발송 ✅ EmailVerificationController::resend
  - [x] 이메일 인증 토큰 생성
  - [x] 인증 이메일 발송
  - [x] 인증 완료 후 계정 활성화
  - [ ] `GET /admin/auth/verify` - 관리자 이메일 인증 관리
  
  **의존성**: 이메일 발송 시스템, 토큰 생성, User 모델
  **AI 명령**: "Laravel에서 이메일 인증 시스템을 구현해주세요. 회원가입 시 이메일 인증 토큰을 생성하고 발송하며, /register/verify/{id}/{hash}로 인증 처리하는 기능을 구현해주세요. 관리자용 이메일 인증 관리 페이지도 포함해주세요."

### 2.2 비밀번호 관리
- [x] **비밀번호 재설정** ✅ 구현 완료
  - [x] `GET /forgot-password` - 비밀번호 찾기 폼 ✅ PasswordResetController::showForgotForm
  - [x] `POST /forgot-password` - 비밀번호 재설정 요청 ✅ PasswordResetController::sendResetLink
  - [x] `GET /reset-password/{token}` - 비밀번호 재설정 폼 ✅ PasswordResetController::showResetForm
  - [x] `POST /reset-password` - 비밀번호 재설정 처리 ✅ PasswordResetController::reset
  - [x] `GET /home/account/password` - 사용자 비밀번호 변경 ✅ PasswordController::showChangeForm
  - [x] `POST /home/account/password` - 비밀번호 변경 처리 ✅ PasswordController::update
  - [x] `GET /home/account/password/force-change` - 강제 변경 폼 ✅ PasswordController::forceChangeForm
  - [x] `POST /home/account/password/force-change` - 강제 변경 처리 ✅ PasswordController::forceChange
  
  **의존성**: 이메일 발송, 토큰 생성, 비밀번호 해싱, User 모델
  **AI 명령**: "Laravel에서 비밀번호 재설정 시스템을 구현해주세요. 비밀번호 찾기, 재설정 토큰 발송, 비밀번호 변경 기능을 포함하고, /login/password와 /home/account/password 라우트로 구성해주세요."

- [x] **비밀번호 정책** ✅ PasswordPolicyController로 구현
  - [x] `GET /admin/auth/passwords/policy` - 비밀번호 정책 설정 ✅ PasswordPolicyController::index
  - [x] `POST /admin/auth/passwords/policy` - 정책 업데이트 ✅ PasswordPolicyController::update
  - [x] `GET /admin/auth/passwords/expired` - 만료된 비밀번호 목록 ✅ PasswordPolicyController::expired
  - [x] `POST /admin/auth/passwords/force-change` - 강제 변경 ✅ PasswordPolicyController::forceChange
  - [x] `GET /admin/auth/passwords/statistics` - 통계 조회 ✅ PasswordPolicyController::statistics
  
  **의존성**: 관리자 권한, 비밀번호 정책 설정, User 모델
  **AI 명령**: "Laravel에서 비밀번호 정책 관리 시스템을 구현해주세요. 비밀번호 복잡도 설정, 만료 관리, 강제 변경 기능을 포함하고, /admin/auth/passwords/policy 라우트로 구성해주세요."

### 2.3 계정 상태 관리
- [x] **사용자 상태 관리** ✅ AccountStatusController로 구현
  - [x] `POST /admin/auth/users/{id}/activate` - 계정 활성화 ✅ AccountStatusController::activate
  - [x] `POST /admin/auth/users/{id}/deactivate` - 계정 비활성화 ✅ AccountStatusController::deactivate
  - [x] `POST /admin/auth/users/{id}/suspend` - 계정 정지 ✅ AccountStatusController::suspend
  - [x] `POST /admin/auth/users/{id}/unsuspend` - 계정 정지 해제 ✅ AccountStatusController::unsuspend
  - [x] `POST /admin/auth/users/bulk-status` - 일괄 상태 변경 ✅ AccountStatusController::bulkStatusChange
  
  **의존성**: 관리자 권한, User 모델, 상태 관리
  **AI 명령**: "Laravel에서 사용자 계정 상태 관리 시스템을 구현해주세요. 계정 활성화, 비활성화, 정지, 해제 기능을 포함하고, /admin/auth/users/{id}/status 라우트로 구성해주세요. 상태 변경 로그도 기록해주세요."

### 2.4 승인 시스템
- [x] **회원가입 승인** (`/register/approval`, `/admin/approval`) ✅ ApprovalController & AdminApprovalController로 구현
  - [x] `GET /register/approval` - 승인 대기 페이지 ✅ ApprovalController::index
  - [x] `POST /register/approval/check` - 승인 상태 확인 ✅ ApprovalController::check
  - [x] `POST /register/approval/resend` - 승인 요청 재전송 ✅ ApprovalController::resend
  - [x] `GET /admin/approval` - 관리자 승인 대기 목록 ✅ AdminApprovalController::index
  - [x] `GET /admin/approval/{id}` - 사용자 상세 정보 ✅ AdminApprovalController::show
  - [x] `POST /admin/approval/{id}/approve` - 개별 승인 ✅ AdminApprovalController::approve
  - [x] `POST /admin/approval/{id}/reject` - 개별 거부 ✅ AdminApprovalController::reject
  - [x] `POST /admin/approval/bulk-approve` - 일괄 승인 ✅ AdminApprovalController::bulkApprove
  - [x] `POST /admin/approval/bulk-reject` - 일괄 거부 ✅ AdminApprovalController::bulkReject
  
  **의존성**: 관리자 권한, User 모델, 승인 상태 관리
  **AI 명령**: "Laravel에서 회원가입 승인 시스템을 구현해주세요. 승인 대기, 개별/일괄 승인/거부 기능을 포함하고, /register/approval과 /admin/auth/approval 라우트로 구성해주세요. 승인 상태 알림도 구현해주세요."

### 2.5 API 인증 (auth-api 모듈)
- [x] **API 로그인** ✅ ApiAuthController로 구현
  - [x] `POST /api/auth/login` - API 로그인 (Sanctum 토큰) ✅ ApiAuthController::login
  - [x] `GET /api/user` - 인증된 사용자 정보 조회 ✅ ApiAuthController::user
  - [x] `POST /api/auth/logout` - API 로그아웃 ✅ ApiAuthController::logout
  - [x] `POST /api/auth/logout-all` - 모든 토큰 무효화 ✅ ApiAuthController::logoutAll
  - [x] `POST /api/auth/refresh` - 토큰 갱신 ✅ ApiAuthController::refresh
  - [x] `POST /api/auth/register` - API 회원가입 ✅ ApiAuthController::register
  - [x] `GET /api/auth/tokens` - 토큰 목록 조회 ✅ ApiAuthController::tokens
  - [x] `DELETE /api/auth/tokens/{id}` - 토큰 삭제 ✅ ApiAuthController::revokeToken
  
  **의존성**: Sanctum 패키지, API 미들웨어, User 모델
  **AI 명령**: "Laravel에서 Sanctum을 사용한 API 인증 시스템을 구현해주세요. /api/auth/* 라우트로 구성하고, 로그인, 사용자 정보 조회, 로그아웃, 토큰 갱신 기능을 포함해주세요."

## 📋 **3단계: 고급 보안 기능 (중간 우선순위)**

### 3.1 2단계 인증 (2FA)
- [x] **사용자 2FA 설정** (`/2fa/*`) ✅ TwoFactorController로 구현
  - [x] `GET /2fa/setup` - 2FA 설정 페이지 ✅ TwoFactorController::setup
  - [x] `POST /2fa/enable` - 2FA 활성화 처리 ✅ TwoFactorController::enable
  - [x] `GET /2fa/challenge` - 2FA 인증 페이지 ✅ TwoFactorController::challenge
  - [x] `POST /2fa/verify` - 2FA 인증 코드 검증 ✅ TwoFactorController::verify
  - [x] `POST /2fa/disable` - 2FA 비활성화 ✅ TwoFactorController::disable
  - [x] `POST /2fa/recovery-codes` - 백업 코드 재생성 ✅ TwoFactorController::regenerateRecoveryCodes
  - [x] `GET /home/account/2fa/backup-codes` - 백업 코드 조회 ✅ TwoFactorController::backupCodes
  - [x] `GET /login/2fa` - 로그인 시 2FA 인증 페이지 ✅ Login2FAController::index
  - [x] `POST /login/2fa/verify` - 로그인 시 2FA 인증 처리 ✅ Login2FAController::verify
  - [x] `GET /login/2fa/cancel` - 2FA 인증 취소 ✅ Login2FAController::cancel
  
  **의존성**: Google Authenticator 패키지, QR 코드 생성, 백업 코드 시스템
  **AI 명령**: "Laravel에서 2단계 인증(2FA) 시스템을 구현해주세요. Google Authenticator 연동, QR 코드 생성, 백업 코드 관리 기능을 포함하고, /home/account/2fa와 /login/2fa 라우트로 구성해주세요."

- [x] **관리자 2FA 관리** (`/admin/auth/2fa`) ✅ Admin2FAController로 구현
  - [x] `GET /admin/auth/2fa/settings` - 2FA 설정 관리 ✅ Admin2FAController::settings
  - [x] `POST /admin/auth/2fa/settings` - 2FA 설정 업데이트 ✅ Admin2FAController::updateSettings
  - [x] `GET /admin/auth/2fa/users` - 2FA 활성화 사용자 목록 ✅ Admin2FAController::users
  - [x] `POST /admin/auth/2fa/users/{id}/disable` - 사용자 2FA 비활성화 ✅ Admin2FAController::disableUser
  - [x] `POST /admin/auth/2fa/users/{id}/force-enable` - 사용자 2FA 강제 활성화 ✅ Admin2FAController::forceEnableUser
  - [x] `POST /admin/auth/2fa/users/{id}/toggle` - 사용자 2FA 토글 ✅ Admin2FAController::toggleUser
  - [x] `GET /admin/auth/2fa/users/{id}/details` - 사용자 2FA 상세 정보 ✅ Admin2FAController::userDetails
  - [x] `GET /admin/auth/2fa/statistics` - 2FA 통계 ✅ Admin2FAController::statistics
  - [x] `POST /admin/auth/2fa/request-all` - 전체 사용자 2FA 요청 ✅ Admin2FAController::requestAll
  
  **의존성**: 관리자 권한, 2FA 시스템, 사용자 관리
  **AI 명령**: "Laravel에서 관리자용 2FA 관리 시스템을 구현해주세요. 2FA 설정 관리, 사용자별 2FA 활성화/비활성화 기능을 포함하고, /admin/auth/2fa 라우트로 구성해주세요."

### 3.2 세션 관리
- [x] **사용자 세션 관리** (`/home/account/sessions`) ✅ SessionController로 구현
  - [x] `GET /home/account/sessions` - 내 활성 세션 목록 ✅ SessionController::index
  - [x] `POST /home/account/sessions/{id}/terminate` - 세션 종료 ✅ SessionController::terminate
  - [x] `POST /home/account/sessions/terminate-all` - 모든 세션 종료 ✅ SessionController::terminateAll
  - [x] `GET /home/account/sessions/{id}/details` - 세션 상세 정보 ✅ SessionController::details
  
  **의존성**: 세션 시스템, 사용자 인증, 세션 추적
  **AI 명령**: "Laravel에서 사용자 세션 관리 시스템을 구현해주세요. 활성 세션 목록, 세션 종료, 세션 상세 정보 기능을 포함하고, /home/account/sessions 라우트로 구성해주세요."

- [x] **관리자 세션 관리** (`/admin/auth/sessions`) ✅ AdminSessionController로 구현
  - [x] `GET /admin/auth/sessions` - 전체 활성 세션 목록 ✅ AdminSessionController::index
  - [x] `POST /admin/auth/sessions/{id}/terminate` - 세션 강제 종료 ✅ AdminSessionController::terminate
  - [x] `POST /admin/auth/sessions/bulk-terminate` - 일괄 세션 종료 ✅ AdminSessionController::bulkTerminate
  - [x] `GET /admin/auth/sessions/{id}/details` - 세션 상세 정보 ✅ AdminSessionController::details
  - [x] `GET /admin/auth/sessions/statistics` - 세션 통계 ✅ AdminSessionController::statistics
  
  **의존성**: 관리자 권한, 세션 시스템, 통계 기능
  **AI 명령**: "Laravel에서 관리자용 세션 관리 시스템을 구현해주세요. 전체 세션 목록, 강제 종료, 일괄 처리, 통계 기능을 포함하고, /admin/auth/sessions 라우트로 구성해주세요."

### 3.3 블랙리스트 관리
- [x] **블랙리스트 관리** (`/admin/auth/blacklist`) ✅ AdminBlacklistController로 구현
  - [x] `GET /admin/auth/blacklist` - 블랙리스트 목록 ✅ AdminBlacklistController::index
  - [x] `GET /admin/auth/blacklist/email` - 이메일 블랙리스트 목록 ✅ AdminBlacklistController::emailList
  - [x] `GET /admin/auth/blacklist/ip` - IP 블랙리스트 목록 ✅ AdminBlacklistController::ipList
  - [x] `POST /admin/auth/blacklist/email` - 이메일 블랙리스트 등록 ✅ AdminBlacklistController::addEmail
  - [x] `POST /admin/auth/blacklist/ip` - IP 블랙리스트 등록 ✅ AdminBlacklistController::addIp
  - [x] `PUT /admin/auth/blacklist/{id}` - 블랙리스트 수정 ✅ AdminBlacklistController::update
  - [x] `DELETE /admin/auth/blacklist/{id}` - 블랙리스트 해제 ✅ AdminBlacklistController::destroy
  - [x] `POST /admin/auth/blacklist/bulk-add` - 일괄 블랙리스트 등록 ✅ AdminBlacklistController::bulkAdd
  - [x] `POST /admin/auth/blacklist/bulk-remove` - 일괄 블랙리스트 해제 ✅ AdminBlacklistController::bulkRemove
  - [x] `GET /admin/auth/blacklist/whitelist` - 화이트리스트 관리 ✅ AdminBlacklistController::whitelist
  - [x] `POST /admin/auth/blacklist/whitelist` - 화이트리스트 등록 ✅ AdminBlacklistController::addWhitelist
  - [x] CheckBlacklist 미들웨어 - IP, 이메일, 도메인, 전화번호, 키워드 차단 ✅
  
  **의존성**: 관리자 권한, 블랙리스트 모델, IP 검증
  **AI 명령**: "Laravel에서 블랙리스트 관리 시스템을 구현해주세요. 이메일/IP 블랙리스트, 화이트리스트, 일괄 처리 기능을 포함하고, /admin/auth/blacklist 라우트로 구성해주세요. IP 검증 로직도 포함해주세요."

### 3.4 JWT 토큰 관리
- [x] **사용자 JWT 토큰** (`/signin/*`, `/signup/*`, `/signout`, `/home/account/tokens`) ✅ JWT 컨트롤러로 구현
  - [x] `GET /signin` - JWT 로그인 폼 ✅ AuthJwtSigninController
  - [x] `POST /signin` - JWT 토큰 생성 ✅ AuthJwtSigninController
  - [x] `GET /signin/refresh` - 토큰 갱신 ✅ AuthJwtSigninController
  - [x] `GET /signup` - JWT 회원가입 폼 ✅ AuthJwtSignupController
  - [x] `POST /signup` - JWT 회원가입 (토큰 생성) ✅ AuthJwtSignupController
  - [x] `GET /signout` - JWT 로그아웃 (토큰 무효화) ✅ AuthJwtSignoutController
  - [x] `POST /signout` - JWT 로그아웃 처리 ✅ AuthJwtSignoutController
  - [x] `POST /signout/all` - 모든 기기 로그아웃 ✅ AuthJwtSignoutController
  - [x] `GET /home/account/tokens` - 내 토큰 목록 ✅ TokenController::index
  - [x] `GET /home/account/tokens/active` - 활성 토큰 목록 ✅ TokenController::active
  - [x] `DELETE /home/account/tokens/{id}` - 토큰 삭제 ✅ TokenController::destroy
  - [x] `POST /home/account/tokens/revoke-all` - 모든 토큰 무효화 ✅ TokenController::revokeAll
  - [x] `GET /home/account/tokens/history` - 토큰 사용 이력 ✅ TokenController::history
  
  **의존성**: JWT 패키지, 토큰 테이블, 사용자 인증
  **AI 명령**: "Laravel에서 JWT 토큰 관리 시스템을 구현해주세요. /signin, /signup, /signout 라우트와 /home/account/tokens 라우트로 구성하고, 토큰 생성, 갱신, 무효화, 이력 관리 기능을 포함해주세요."

- [x] **관리자 JWT 토큰 관리** (`/admin/auth/jwt`) ✅ AdminJWTController로 구현
  - [x] `GET /admin/auth/jwt/tokens` - 전체 토큰 목록 ✅ AdminJWTController::index
  - [x] `GET /admin/auth/jwt/tokens/active` - 활성 토큰 목록 ✅ AdminJWTController::active
  - [x] `GET /admin/auth/jwt/tokens/expired` - 만료된 토큰 목록 ✅ AdminJWTController::expired
  - [x] `GET /admin/auth/jwt/tokens/{id}` - 토큰 상세 정보 ✅ AdminJWTController::show
  - [x] `DELETE /admin/auth/jwt/tokens/{id}` - 토큰 강제 삭제 ✅ AdminJWTController::destroy
  - [x] `POST /admin/auth/jwt/tokens/revoke-all` - 모든 토큰 무효화 ✅ AdminJWTController::revokeAll
  - [x] `POST /admin/auth/jwt/tokens/revoke-user/{id}` - 사용자 토큰 무효화 ✅ AdminJWTController::revokeUser
  - [x] `GET /admin/auth/jwt/settings` - JWT 설정 관리 ✅ AdminJWTController::settings
  - [x] `POST /admin/auth/jwt/settings` - JWT 설정 업데이트 ✅ AdminJWTController::updateSettings
  - [x] `GET /admin/auth/jwt/statistics` - JWT 사용 통계 ✅ AdminJWTController::statistics
  
  **의존성**: 관리자 권한, JWT 시스템, 통계 기능
  **AI 명령**: "Laravel에서 관리자용 JWT 토큰 관리 시스템을 구현해주세요. 토큰 목록, 강제 삭제, 사용자별 무효화, 설정 관리, 통계 기능을 포함하고, /admin/auth/jwt 라우트로 구성해주세요."

## 📋 **4단계: 사용자 경험 개선 (중간 우선순위)**

### 4.1 휴면계정 관리
- [x] **사용자 휴면계정** (`/login/dormant`, `/home/account/dormant`) ✅ DormantController로 구현
  - [x] `GET /login/dormant` - 휴면계정 안내 페이지 ✅ DormantController::index
  - [x] `POST /login/dormant/activate` - 휴면계정 활성화 요청 ✅ DormantController::requestActivation
  - [x] `GET /login/dormant/activate/{token}` - 활성화 토큰 검증 ✅ DormantController::activate
  - [x] `GET /home/account/dormant` - 휴면계정 상태 확인 ✅ DormantController::status
  - [x] `POST /home/account/dormant/extend` - 휴면계정 연장 요청 ✅ DormantController::extend
  - [x] CheckDormantAccount 미들웨어 - 휴면계정 체크 및 자동 전환 ✅
  
  **의존성**: 휴면계정 정책, 사용자 인증, 이메일 알림
  **AI 명령**: "Laravel에서 휴면계정 관리 시스템을 구현해주세요. 휴면계정 안내, 활성화 요청, 상태 확인, 연장 요청 기능을 포함하고, /login/dormant와 /home/account/dormant 라우트로 구성해주세요."

- [x] **관리자 휴면계정 관리** (`/admin/auth/users/dormant`) ✅ AdminDormantController로 구현
  - [x] `GET /admin/auth/users/dormant` - 휴면계정 목록 ✅ AdminDormantController::index
  - [x] `GET /admin/auth/users/dormant/statistics` - 휴면계정 통계 ✅ AdminDormantController::statistics
  - [x] `POST /admin/auth/users/dormant/{id}/activate` - 휴면계정 활성화 ✅ AdminDormantController::activate
  - [x] `POST /admin/auth/users/dormant/{id}/delete` - 휴면계정 삭제 ✅ AdminDormantController::delete
  - [x] `POST /admin/auth/users/dormant/bulk-activate` - 일괄 활성화 ✅ AdminDormantController::bulkActivate
  - [x] `POST /admin/auth/users/dormant/bulk-delete` - 일괄 삭제 ✅ AdminDormantController::bulkDelete
  - [x] `GET /admin/auth/users/dormant/settings` - 휴면계정 정책 설정 ✅ AdminDormantController::settings
  - [x] `POST /admin/auth/users/dormant/settings` - 휴면계정 정책 업데이트 ✅ AdminDormantController::updateSettings
  
  **의존성**: 관리자 권한, 휴면계정 정책, 통계 기능
  **AI 명령**: "Laravel에서 관리자용 휴면계정 관리 시스템을 구현해주세요. 휴면계정 목록, 통계, 활성화/삭제, 일괄 처리, 정책 설정 기능을 포함하고, /admin/auth/users/dormant 라우트로 구성해주세요."

### 4.2 사용자 프로필 관리
- [x] **사용자 프로필** (`/home/profile/*`) ✅ Home\ProfileController로 구현 (라우트 경로 변경 완료)
  - [x] `GET /home/profile` - 프로필 대시보드 ✅ ProfileController::index
  - [x] `GET /home/profile/edit` - 프로필 편집 폼 ✅ ProfileController::edit
  - [x] `PUT /home/profile` - 프로필 업데이트 ✅ ProfileController::update
  - [x] `GET /home/profile/avatar` - 아바타 관리 페이지 ✅ ProfileController::avatar
  - [x] `POST /home/profile/avatar` - 아바타 업로드/수정 ✅ ProfileController::updateAvatar
  - [x] `GET /home/profile/addresses` - 주소록 관리 ✅ ProfileController::addresses
  - [x] `POST /home/profile/addresses` - 주소 추가 ✅ ProfileController::addAddress
  - [x] `PUT /home/profile/addresses/{id}` - 주소 수정 ✅ ProfileController::updateAddress
  - [x] `DELETE /home/profile/addresses/{id}` - 주소 삭제 ✅ ProfileController::deleteAddress
  - [x] `GET /home/profile/security` - 보안 설정 ✅ ProfileController::security
  - [x] `POST /home/profile/security/2fa` - 2FA 설정 ✅ ProfileController::enable2FA
  - [x] `GET /home/profile/social` - 소셜 계정 관리 ✅ ProfileController::socialAccounts
  - [x] `DELETE /home/profile/social/{provider}` - 소셜 계정 연결 해제 ✅ ProfileController::disconnectSocial
  - [x] `GET /home/profile/avatar/history` - 아바타 변경 이력 ✅ ProfileController::avatarHistory (라우트 변경: /home/profile/*)

- [x] **관리자 프로필 관리** (`/admin/auth/users/{id}/profile`) ✅ AdminProfileController로 구현
  - [x] `GET /admin/auth/users/{id}/profile` - 사용자 프로필 조회 ✅ AdminProfileController::show
  - [x] `PUT /admin/auth/users/{id}/profile` - 사용자 프로필 수정 ✅ AdminProfileController::update
  - [x] `POST /admin/auth/users/{id}/avatar` - 사용자 아바타 업로드 ✅ AdminProfileController::uploadAvatar
  - [x] `DELETE /admin/auth/users/{id}/avatar` - 사용자 아바타 삭제 ✅ AdminProfileController::deleteAvatar
  - [x] `GET /admin/auth/users/{id}/profile/history` - 프로필 변경 이력 ✅ AdminProfileController::history

### 4.3 사용자 추가정보
- [x] **추가정보 관리** (`/home/profile/*`, `/admin/auth/users/{id}/additional`) ✅ 프로필 기능에 통합
  - [x] `GET /home/profile` - 사용자 프로필 조회 ✅ ProfileController::index (라우트 변경)
  - [x] `PUT /home/profile` - 사용자 프로필 수정 ✅ ProfileController::update (라우트 변경)
  - [x] `GET /home/profile/addresses` - 주소록 관리 ✅ ProfileController::addresses (라우트 변경)
  - [x] `POST /home/profile/addresses` - 주소 추가 ✅ ProfileController::addAddress (라우트 변경)
  - [x] `PUT /home/profile/addresses/{id}` - 주소 수정 ✅ ProfileController::updateAddress (라우트 변경)
  - [x] `DELETE /home/profile/addresses/{id}` - 주소 삭제 ✅ ProfileController::deleteAddress (라우트 변경)
  - [x] `GET /admin/auth/users/{id}/additional` - 관리자 추가정보 조회 ✅ AdminProfileController::additional
  - [x] `PUT /admin/auth/users/{id}/additional` - 관리자 추가정보 수정 ✅ AdminProfileController::updateAdditional

### 4.4 소셜 로그인 (auth-social 모듈)
- [x] **소셜 로그인** (`/login/{provider}/*`) ✅ OAuthController로 구현
  - [x] `GET /login/google` - Google 로그인 리다이렉트 ✅ OAuthController::redirect
  - [x] `GET /login/google/callback` - Google 로그인 콜백 ✅ OAuthController::callback
  - [x] `GET /login/facebook` - Facebook 로그인 리다이렉트 ✅ OAuthController::redirect
  - [x] `GET /login/facebook/callback` - Facebook 로그인 콜백 ✅ OAuthController::callback
  - [x] `GET /login/github` - GitHub 로그인 리다이렉트 ✅ OAuthController::redirect
  - [x] `GET /login/github/callback` - GitHub 로그인 콜백 ✅ OAuthController::callback
  - [x] `GET /login/naver` - Naver 로그인 리다이렉트 ✅ OAuthController::redirect
  - [x] `GET /login/naver/callback` - Naver 로그인 콜백 ✅ OAuthController::callback
  - [x] `GET /login/kakao` - Kakao 로그인 리다이렉트 ✅ OAuthController::redirect
  - [x] `GET /login/kakao/callback` - Kakao 로그인 콜백 ✅ OAuthController::callback
  - [x] social_accounts 테이블 - 소셜 계정 연결 정보 ✅
  - [x] oauth_providers 테이블 - OAuth 공급자 설정 ✅
  - [x] social_login_logs 테이블 - 소셜 로그인 로그 ✅

- [x] **소셜 계정 관리** (`/home/account/social`, `/admin/auth/social`) ✅ 컨트롤러로 구현
  - [x] `GET /home/account/social` - 연결된 소셜 계정 목록 ✅ SocialAccountController::index
  - [x] `POST /home/account/social/{provider}/connect` - 소셜 계정 연결 ✅ SocialAccountController::connect
  - [x] `DELETE /home/account/social/{provider}/disconnect` - 소셜 계정 연결 해제 ✅ SocialAccountController::disconnect
  - [x] `GET /admin/auth/social` - 관리자 소셜 로그인 설정 ✅ AdminSocialController::index
  - [x] `GET /admin/auth/oauth` - OAuth 공급자 관리 ✅ AdminSocialController::oauth
  - [x] `PUT /admin/auth/oauth/{id}` - OAuth 공급자 설정 업데이트 ✅ AdminSocialController::updateProvider
  - [x] `GET /admin/auth/oauth/users/{provider}` - 소셜 로그인 사용자 목록 ✅ AdminSocialController::users
  - [x] `GET /admin/auth/social/accounts/{id}` - 소셜 계정 상세 정보 ✅ AdminSocialController::accountDetails
  - [x] `DELETE /admin/auth/social/accounts/{id}` - 소셜 계정 연결 해제 ✅ AdminSocialController::disconnectAccount
  - [x] `GET /admin/auth/social/statistics` - 소셜 로그인 통계 ✅ AdminSocialController::statistics

### 4.5 사용자 메시지 (auth-users 모듈)
- [x] **사용자 메시지** (`/home/message/*`, `/admin/auth/message/*`) ✅ MessageController로 구현
  - [x] `GET /home/message` - 사용자 메시지 목록 ✅ MessageController::index
  - [x] `GET /home/message/compose` - 메시지 작성 폼 ✅ MessageController::compose
  - [x] `POST /home/message` - 메시지 발송 ✅ MessageController::send
  - [x] `GET /home/message/{id}` - 메시지 상세 조회 ✅ MessageController::show
  - [x] `POST /home/message/{id}/read` - 메시지 읽음 처리 ✅ MessageController::markAsRead
  - [x] `POST /home/message/{id}/star` - 별표 토글 ✅ MessageController::toggleStar
  - [x] `POST /home/message/{id}/archive` - 메시지 보관 ✅ MessageController::archive
  - [x] `DELETE /home/message/{id}` - 메시지 삭제 ✅ MessageController::destroy
  - [x] `POST /home/message/block` - 사용자 차단 ✅ MessageController::blockUser
  - [x] `DELETE /home/message/block/{userId}` - 차단 해제 ✅ MessageController::unblockUser
  - [x] `GET /home/message/blocked/users` - 차단 목록 ✅ MessageController::blockedUsers
  - [x] `GET /home/message/settings/notifications` - 알림 설정 ✅ MessageController::settings
  - [x] `POST /home/message/settings/notifications` - 알림 설정 업데이트 ✅ MessageController::updateSettings
  - [x] user_messages 테이블 - 메시지 저장 ✅
  - [x] message_threads 테이블 - 대화 스레드 ✅
  - [x] message_blocks 테이블 - 차단 사용자 ✅
  - [x] message_notifications 테이블 - 알림 설정 ✅

- [x] **관리자 메시지 관리** (`/admin/auth/message/*`) ✅ AdminMessageController로 구현
  - [x] `GET /admin/auth/message` - 메시지 관리 대시보드 ✅ AdminMessageController::index
  - [x] `GET /admin/auth/message/{id}` - 메시지 상세 조회 ✅ AdminMessageController::show
  - [x] `GET /admin/auth/message/compose` - 시스템 메시지 작성 ✅ AdminMessageController::compose
  - [x] `POST /admin/auth/message` - 관리자 메시지 발송 ✅ AdminMessageController::send
  - [x] `GET /admin/auth/message/templates` - 메시지 템플릿 관리 ✅ AdminMessageController::templates
  - [x] `GET /admin/auth/message/templates/create` - 템플릿 생성 폼 ✅ AdminMessageController::createTemplate
  - [x] `POST /admin/auth/message/templates` - 템플릿 저장 ✅ AdminMessageController::storeTemplate
  - [x] `GET /admin/auth/message/templates/{id}/edit` - 템플릿 수정 폼 ✅ AdminMessageController::editTemplate
  - [x] `PUT /admin/auth/message/templates/{id}` - 템플릿 업데이트 ✅ AdminMessageController::updateTemplate
  - [x] `DELETE /admin/auth/message/templates/{id}` - 템플릿 삭제 ✅ AdminMessageController::deleteTemplate
  - [x] `GET /admin/auth/message/blocked` - 차단 사용자 관리 ✅ AdminMessageController::blockedUsers
  - [x] `DELETE /admin/auth/message/blocked/{id}` - 차단 해제 ✅ AdminMessageController::unblock
  - [x] `GET /admin/auth/message/statistics` - 메시지 통계 ✅ AdminMessageController::statistics
  - [x] `GET /admin/auth/message/sse` - SSE 메시지 테스트 ✅ AdminMessageController::sseTest
  - [x] `GET /admin/auth/message/sse/stream` - SSE 스트림 ✅ AdminMessageController::sseStream
  - [x] message_templates 테이블 - 템플릿 관리 ✅
  - [x] bulk_messages 테이블 - 대량 발송 로그 ✅

## 📋 **5단계: 다국어 및 지역 설정 (중간 우선순위)**

### 5.1 언어 관리
- [x] **언어 목록 관리** (`/admin/auth/languages`) ✅ AdminLanguageController로 구현
  - [x] `GET /admin/auth/languages` - 지원 언어 목록 ✅ AdminLanguageController::index
  - [x] `GET /admin/auth/languages/create` - 언어 추가 폼 ✅ AdminLanguageController::create
  - [x] `POST /admin/auth/languages` - 언어 추가 ✅ AdminLanguageController::store
  - [x] `GET /admin/auth/languages/{id}/edit` - 언어 수정 폼 ✅ AdminLanguageController::edit
  - [x] `PUT /admin/auth/languages/{id}` - 언어 수정 ✅ AdminLanguageController::update
  - [x] `DELETE /admin/auth/languages/{id}` - 언어 삭제 ✅ AdminLanguageController::destroy
  - [x] `POST /admin/auth/languages/reorder` - 언어 순서 변경 ✅ AdminLanguageController::reorder
  - [x] `GET /admin/auth/languages/{id}/users` - 언어별 사용자 ✅ AdminLanguageController::users
  - [x] languages 테이블 - 언어 정보 저장 ✅
  - [x] user_language_settings 테이블 - 사용자 언어 설정 ✅
  - [x] translations 테이블 - 번역 문자열 ✅

### 5.2 국가 관리
- [x] **국가 목록 관리** (`/admin/auth/countries`) ✅ AdminCountryController로 구현
  - [x] `GET /admin/auth/countries` - 국가 목록 ✅ AdminCountryController::index
  - [x] `GET /admin/auth/countries/create` - 국가 추가 폼 ✅ AdminCountryController::create
  - [x] `POST /admin/auth/countries` - 국가 추가 ✅ AdminCountryController::store
  - [x] `GET /admin/auth/countries/{id}/edit` - 국가 수정 폼 ✅ AdminCountryController::edit
  - [x] `PUT /admin/auth/countries/{id}` - 국가 수정 ✅ AdminCountryController::update
  - [x] `DELETE /admin/auth/countries/{id}` - 국가 삭제 ✅ AdminCountryController::destroy
  - [x] `GET /admin/auth/countries/statistics` - 국가별 통계 ✅ AdminCountryController::statistics
  - [x] `POST /admin/auth/countries/import` - 국가 가져오기 ✅ AdminCountryController::import
  - [x] countries 테이블 - 국가 정보 저장 (ISO 코드, 통화, 시간대 등) ✅

### 5.3 브라우저 감지
- [x] **브라우저 감지 기능** ✅ DetectBrowser 미들웨어로 구현
  - [x] 사용자 브라우저 정보 수집 ✅ Agent 라이브러리 사용
  - [x] 브라우저별 언어 자동 감지 ✅ Accept-Language 헤더 파싱
  - [x] 브라우저별 시간대 자동 감지 ✅ X-Timezone 헤더 및 JS 감지
  - [x] 디바이스 타입 감지 (desktop, mobile, tablet) ✅
  - [x] 플랫폼 및 OS 버전 감지 ✅
  - [x] 봇 감지 기능 ✅
  - [x] IP 기반 국가 감지 지원 ✅
  - [x] browser_detections 테이블 - 브라우저 감지 로그 ✅
  - [x] 사용자 언어 설정 자동 구성 ✅

## 📋 **6단계: 통신 및 알림 (중간 우선순위)**

### 6.1 이메일 관리
- [x] **이메일 템플릿 관리** (`/admin/auth/emails/templates`) ✅
  - [x] `GET /admin/auth/emails/templates` - 이메일 템플릿 목록 ✅
  - [x] `POST /admin/auth/emails/templates` - 템플릿 생성 ✅
  - [x] `PUT /admin/auth/emails/templates/{id}` - 템플릿 수정 ✅
  - [x] `DELETE /admin/auth/emails/templates/{id}` - 템플릿 삭제 ✅
  - [x] auth_email_templates 테이블 - 이메일 템플릿 저장 ✅
  - [x] 템플릿 변수 지원 ({{ user_name }}, {{ reset_link }} 등) ✅
  - [x] 템플릿 미리보기 기능 ✅
  - [x] 템플릿 복제 기능 ✅

- [x] **이메일 발송 관리** (`/admin/auth/emails/send`) ✅
  - [x] `GET /admin/auth/emails/send` - 이메일 발송 폼 ✅
  - [x] `POST /admin/auth/emails/send` - 이메일 발송 ✅
  - [x] `GET /admin/auth/emails/logs` - 발송 로그 ✅
  - [x] `POST /admin/auth/emails/logs/{id}/resend` - 재발송 ✅
  - [x] auth_email_logs 테이블 - 이메일 발송 로그 ✅
  - [x] auth_bulk_notifications 테이블 - 대량 발송 관리 ✅
  - [x] 이메일 트래킹 (열람, 클릭) ✅
  - [x] EmailService 클래스 구현 ✅

### 6.2 SMS 관리
- [x] **SMS 발송 관리** (`/admin/auth/sms`) ✅
  - [x] `GET /admin/auth/sms/send` - SMS 발송 폼 ✅
  - [x] `POST /admin/auth/sms/send` - SMS 발송 ✅
  - [x] `GET /admin/auth/sms/logs` - 발송 로그 ✅
  - [x] `GET /admin/auth/sms/templates` - SMS 템플릿 관리 ✅
  - [x] auth_sms_templates 테이블 - SMS 템플릿 저장 ✅
  - [x] auth_sms_logs 테이블 - SMS 발송 로그 ✅
  - [x] auth_sms_senders 테이블 - 발신번호 관리 ✅
  - [x] SmsService 클래스 - 다중 프로바이더 지원 (Twilio, 알리고, Toast) ✅
  - [x] SMS 길이 계산 (SMS/LMS/MMS) ✅
  - [x] 국제 전화번호 포맷팅 ✅

## 📋 **7단계: 고급 사용자 관리 (낮은 우선순위)**

### 7.1 회원등급 관리
- [x] **회원등급 시스템** (`/admin/auth/grades`) ✅
  - [x] `GET /admin/auth/grades` - 등급 목록 ✅
  - [x] `POST /admin/auth/grades` - 등급 생성 ✅
  - [x] `PUT /admin/auth/grades/{id}` - 등급 수정 ✅
  - [x] `DELETE /admin/auth/grades/{id}` - 등급 삭제 ✅
  - [x] `POST /admin/auth/users/{id}/grade` - 사용자 등급 변경 ✅
  - [x] auth_user_grades 테이블 - 회원 등급 정의 ✅
  - [x] auth_user_grade_logs 테이블 - 등급 변경 로그 ✅
  - [x] 등급별 혜택 설정 (할인율, 포인트 적립률) ✅
  - [x] 자동 등급 업그레이드 기능 ✅
  - [x] 등급 통계 및 분석 ✅
  - [x] 기본 등급: Bronze, Silver, Gold, Platinum, Diamond ✅

### 7.2 회원유형 관리
- [x] **회원유형 관리** (`/admin/auth/user-types`) ✅
  - [x] `GET /admin/auth/user-types` - 유형 목록 ✅
  - [x] `POST /admin/auth/user-types` - 유형 생성 ✅
  - [x] `PUT /admin/auth/user-types/{id}` - 유형 수정 ✅
  - [x] `DELETE /admin/auth/user-types/{id}` - 유형 삭제 ✅
  - [x] 지원 유형: personal, student, business, partner, reseller, distributor, agent ✅
  - [x] auth_user_types 테이블 - 회원 유형 정의 ✅
  - [x] auth_user_type_logs 테이블 - 유형 변경 로그 ✅
  - [x] 유형별 필수/선택 필드 설정 ✅
  - [x] 유형별 승인/인증 요구사항 ✅
  - [x] 파트너 유형별 수수료율 설정 ✅

### 7.3 디바이스 및 접속 관리
- [x] **디바이스 관리** (`/admin/auth/devices`) ✅
  - [x] `GET /admin/auth/devices` - 디바이스 목록 ✅
  - [x] `GET /admin/auth/devices/{id}` - 디바이스 상세 ✅
  - [x] `POST /admin/auth/devices/{id}/block` - 디바이스 차단 ✅
  - [x] `POST /admin/auth/devices/{id}/unblock` - 디바이스 차단 해제 ✅
  - [x] `POST /admin/auth/devices/{id}/trust` - 디바이스 신뢰 설정 ✅
  - [x] `POST /admin/auth/devices/{id}/untrust` - 디바이스 신뢰 해제 ✅
  - [x] auth_user_devices 테이블 - 디바이스 정보 저장 ✅
  - [x] auth_device_login_logs 테이블 - 디바이스별 로그인 로그 ✅
  - [x] 디바이스 타입 지원 (mobile, tablet, desktop, watch, tv) ✅
  - [x] 플랫폼 감지 (ios, android, windows, macos, linux) ✅
  - [x] 브라우저 및 버전 정보 수집 ✅
  - [x] 푸시 알림 토큰 관리 ✅
  - [x] 디바이스별 통계 및 분석 ✅

## 📋 **8단계: 포인트 및 결제 시스템 (낮은 우선순위)**

### 8.1 포인트 관리
- [x] **포인트 시스템** (`/admin/auth/points`) ✅
  - [x] `GET /admin/auth/points` - 포인트 목록 ✅
  - [x] `POST /admin/auth/points/{userId}/add` - 포인트 적립 ✅
  - [x] `POST /admin/auth/points/{userId}/deduct` - 포인트 차감 ✅
  - [x] `GET /admin/auth/points/{userId}/history` - 포인트 내역 ✅
  - [x] auth_user_points 테이블 - 사용자별 포인트 잔액 ✅
  - [x] auth_point_transactions 테이블 - 포인트 거래 내역 ✅
  - [x] 포인트 만료 시스템 ✅
  - [x] 포인트 통계 및 분석 ✅
  - [x] 자동 만료 처리 스케줄러 ✅

### 8.2 eMoney 관리 (auth-emoney 모듈)
- [x] **사용자 eMoney** (`/home/emoney/*`) ✅
  - [x] `GET /home/emoney` - 사용자 eMoney 잔액 조회 ✅
  - [x] `GET /home/emoney/deposit` - eMoney 충전 페이지 ✅
  - [x] `POST /home/emoney/deposit` - eMoney 충전 처리 ✅
  - [x] `GET /home/emoney/withdraw` - eMoney 출금 페이지 ✅
  - [x] `POST /home/emoney/withdraw` - eMoney 출금 신청 ✅
  - [x] `GET /home/emoney/bank` - 등록된 은행계좌 목록 ✅
  - [x] `POST /home/emoney/bank` - 은행계좌 등록 ✅
  - [x] `PUT /home/emoney/bank/{id}` - 은행계좌 수정 ✅
  - [x] `DELETE /home/emoney/bank/{id}` - 은행계좌 삭제 ✅
  - [x] auth_emoney_wallets 테이블 - eMoney 지갑 ✅
  - [x] auth_emoney_transactions 테이블 - eMoney 거래 내역 ✅

- [x] **관리자 eMoney** (`/admin/auth/emoney/*`) ✅
  - [x] `GET /admin/auth/emoney` - eMoney 관리 대시보드 ✅
  - [x] `GET /admin/auth/emoney/user` - 사용자 eMoney 목록 ✅
  - [x] `GET /admin/auth/emoney/log/{userId}` - 사용자 eMoney 내역 ✅
  - [x] `GET /admin/auth/emoney/bank/{userId}` - 사용자 은행계좌 관리 ✅
  - [x] `GET /admin/auth/emoney/withdraw/{id}` - 출금 신청 관리 ✅
  - [x] `POST /admin/auth/emoney/withdraw/{id}/approve` - 출금 승인 ✅
  - [x] `POST /admin/auth/emoney/withdraw/{id}/reject` - 출금 거부 ✅
  - [x] `GET /admin/auth/emoney/deposit/{id}` - 입금 내역 관리 ✅
  - [x] `POST /admin/auth/emoney/deposit/{id}/confirm` - 입금 확인 ✅
  - [x] `GET /admin/auth/bank` - 은행 목록 관리 ✅
  - [x] `GET /admin/auth/currency` - 통화 목록 관리 ✅
  - [x] `GET /admin/auth/currency/log/{code}` - 통화 로그 관리 ✅
  - [x] auth_bank_accounts 테이블 - 은행 계좌 정보 (암호화) ✅
  - [x] auth_withdrawal_requests 테이블 - 출금 신청 관리 ✅
  - [x] auth_deposit_logs 테이블 - 입금 내역 ✅
  - [x] auth_banks 테이블 - 한국 은행 25개 마스터 데이터 ✅
  - [x] auth_currencies 테이블 - 통화 마스터 데이터 (KRW, USD, EUR, JPY, CNY, GBP) ✅
  - [x] auth_currency_logs 테이블 - 환율 변경 로그 ✅
  - [x] 출금 수수료 계산 ✅
  - [x] 계좌 정보 암호화 저장 ✅

## 📋 **9단계: 로그 및 모니터링 (낮은 우선순위)**

### 9.1 로그 관리
- [x] **로그인 로그** (`/admin/auth/login-history`) ✅ AuthLoginHistory로 구현
  - [x] `GET /admin/auth/login-history` - 로그인 로그 목록
  - [x] `GET /admin/auth/login-history/failed` - 실패 로그 목록 (필터링)
  - [x] `GET /admin/auth/login-history/suspicious` - 의심스러운 활동 로그 (Hook에서 감지)
  - [x] `GET /admin/auth/export/login-history` - 로그 내보내기 ✅ AdminLogExportController::exportLoginHistory

- [x] **활동 로그** (`/admin/auth/account-logs`) ✅ AuthAccountLogs로 구현
  - [x] `GET /admin/auth/account-logs` - 활동 로그 목록
  - [x] `GET /admin/auth/account-logs/{id}` - 로그 상세 조회
  - [x] `DELETE /admin/auth/account-logs/{id}/delete` - 로그 삭제 (관리자 전용)
  - [x] `GET /admin/auth/export/account-logs` - 활동 로그 내보내기 ✅ AdminLogExportController::exportAccountLogs

- [x] **보안 로그** (`/admin/password-errors`) ✅ AdminPasswordErrorController로 구현
  - [x] `GET /admin/password-errors` - 비밀번호 오류 목록 ✅ AdminPasswordErrorController::index
  - [x] `GET /admin/password-errors/locked-accounts` - 잠금된 계정 목록 ✅ AdminPasswordErrorController::lockedAccounts
  - [x] `POST /admin/password-errors/unlock/{userId}` - 계정 잠금 해제 ✅ AdminPasswordErrorController::unlock
  - [x] `GET /admin/password-errors/statistics` - 비밀번호 오류 통계 ✅ AdminPasswordErrorController::statistics
  - [x] `GET /admin/auth/export/security-logs` - 보안 로그 내보내기 ✅ AdminLogExportController::exportSecurityLogs
  - [x] `GET /admin/auth/export/permission-logs` - 권한 변경 로그 ✅ AdminLogExportController::permissionLogs

### 9.2 통계 및 분석
- [x] **사용자 통계** (`/admin/auth/statistics`) ✅ AdminStatisticsController로 구현
  - [x] `GET /admin/auth/statistics/registrations` - 가입 통계 ✅ AdminStatisticsController::registrations
  - [x] `GET /admin/auth/statistics/active-users` - 활성 사용자 통계 ✅ AdminStatisticsController::activeUsers
  - [x] `GET /admin/auth/statistics/login-patterns` - 로그인 패턴 분석 ✅ AdminStatisticsController::loginPatterns
  - [x] `GET /admin/auth/statistics/retention` - 사용자 유지율 ✅ AdminStatisticsController::retention

## 📋 **10단계: 시스템 설정 및 유지보수 (낮은 우선순위)**

### 10.1 시스템 설정
- [x] **인증 설정** (`/admin/auth/settings`) ✅ AdminAuthSettingsController로 구현
  - [x] `GET /admin/auth/settings/login` - 로그인 설정 ✅ AdminAuthSettingsController::loginSettings
  - [x] `POST /admin/auth/settings/login` - 로그인 설정 업데이트 ✅ AdminAuthSettingsController::updateLoginSettings
  - [x] `GET /admin/auth/settings/registration` - 가입 설정 ✅ AdminAuthSettingsController::registrationSettings
  - [x] `POST /admin/auth/settings/registration` - 가입 설정 업데이트 ✅ AdminAuthSettingsController::updateRegistrationSettings

- [x] **보안 설정** (`/admin/auth/settings/security`) ✅ AdminSecuritySettingsController로 구현
  - [x] `GET /admin/auth/settings/security` - 보안 설정 ✅ AdminSecuritySettingsController::securitySettings
  - [x] `POST /admin/auth/settings/security` - 보안 설정 업데이트 ✅ AdminSecuritySettingsController::updateSecuritySettings
  - [x] `GET /admin/auth/settings/captcha` - CAPTCHA 설정 ✅ AdminSecuritySettingsController::captchaSettings
  - [x] `POST /admin/auth/settings/captcha` - CAPTCHA 설정 업데이트 ✅ AdminSecuritySettingsController::updateCaptchaSettings
  - [x] `GET /admin/auth/settings/whitelist` - IP 화이트리스트 관리 ✅ AdminSecuritySettingsController::ipWhitelist
  - [x] `POST /admin/auth/settings/whitelist` - IP 추가 ✅ AdminSecuritySettingsController::addIpWhitelist
  - [x] `DELETE /admin/auth/settings/whitelist/{id}` - IP 삭제 ✅ AdminSecuritySettingsController::removeIpWhitelist

### 10.2 약관 관리
- [x] **약관 관리** (`/admin/terms`) ✅ AdminAuthTermsController로 구현
  - [x] `GET /admin/terms` - 약관 목록 ✅ AdminAuthTermsController::index
  - [x] `GET /admin/terms/create` - 약관 생성 폼 ✅ AdminAuthTermsController::create
  - [x] `POST /admin/terms` - 약관 저장 ✅ AdminAuthTermsController::store
  - [x] `GET /admin/terms/{id}/edit` - 약관 수정 폼 ✅ AdminAuthTermsController::edit
  - [x] `PUT /admin/terms/{id}` - 약관 업데이트 ✅ AdminAuthTermsController::update
  - [x] `DELETE /admin/terms/{id}` - 약관 삭제 ✅ AdminAuthTermsController::destroy
  - [x] `GET /admin/terms/logs` - 약관 동의 로그 ✅ AdminAuthTermsLogsController::index

### 10.3 대량 작업
- [x] **일괄 사용자 관리** (`/admin/auth/bulk`) ✅ AdminBulkController로 구현
  - [x] `POST /admin/auth/bulk/activate` - 일괄 활성화 ✅ AdminBulkController::activate
    * 선택된 여러 사용자 계정을 한번에 활성화
    * 휴면/비활성 계정을 정상 상태로 변경
    * 이메일 인증 대기 중인 계정들을 강제 활성화
    * Request: `user_ids[]` 배열로 사용자 ID 전달
    * 활성화된 사용자들에게 알림 이메일 발송 옵션
    
  - [x] `POST /admin/auth/bulk/deactivate` - 일괄 비활성화 ✅ AdminBulkController::deactivate
    * 선택된 여러 사용자 계정을 한번에 비활성화
    * 정책 위반, 임시 정지 등의 사유로 계정 차단
    * 비활성화 사유 기록 및 기간 설정 가능
    * Request: `user_ids[]`, `reason`, `until_date`(선택)
    * 비활성화된 사용자의 세션 즉시 종료
    
  - [x] `POST /admin/auth/bulk/delete` - 일괄 삭제 ✅ AdminBulkController::delete
    * 선택된 여러 사용자 계정을 한번에 삭제
    * 소프트 삭제(deleted_at) 또는 하드 삭제 선택 가능
    * 삭제 전 관련 데이터(포인트, 메시지 등) 백업 옵션
    * Request: `user_ids[]`, `delete_type`(soft/hard), `backup`(true/false)
    * 삭제 확인 프로세스 필수 (2단계 인증)
    
  - [x] `POST /admin/auth/bulk/export` - 일괄 내보내기 ✅ AdminBulkController::export
    * 선택된 사용자 또는 전체 사용자 데이터 내보내기
    * CSV, Excel, JSON 형식 지원
    * 내보낼 필드 선택 가능 (개인정보 필터링)
    * Request: `user_ids[]`(선택) 또는 `filters`(검색 조건), `format`, `fields[]`
    * 대용량 데이터는 백그라운드 작업으로 처리 후 다운로드 링크 제공
    
  - [x] `POST /admin/auth/bulk/import` - 일괄 가져오기 ✅ AdminBulkController::import
    * CSV, Excel 파일로 여러 사용자를 한번에 등록
    * 중복 이메일 체크 및 유효성 검증
    * 가져오기 실패 항목 리포트 생성
    * Request: 파일 업로드, `update_existing`(기존 사용자 업데이트 여부)
    * 트랜잭션 처리로 부분 실패 시 전체 롤백 옵션
    
  - [x] `POST /admin/auth/bulk/send-email` - 일괄 이메일 발송 ✅ AdminBulkController::sendEmail
    * 선택된 사용자들에게 공지사항, 안내 이메일 발송
    * 템플릿 선택 및 변수 치환 지원
    * 발송 스케줄링 및 대기열 처리
    
  - [x] `POST /admin/auth/bulk/reset-password` - 일괄 비밀번호 재설정 ✅ AdminBulkController::resetPassword
    * 선택된 사용자들의 비밀번호 강제 재설정
    * 임시 비밀번호 생성 및 이메일 발송
    * 다음 로그인 시 비밀번호 변경 강제
    
  - [x] `POST /admin/auth/bulk/change-grade` - 일괄 등급 변경 ✅ AdminBulkController::changeGrade
    * 선택된 사용자들의 회원 등급 일괄 변경
    * 등급 변경 사유 기록
    * 등급 혜택 자동 적용/해제
    
  - [x] `POST /admin/auth/bulk/add-points` - 일괄 포인트 지급 ✅ AdminBulkController::addPoints
    * 선택된 사용자들에게 포인트 일괄 지급
    * 지급 사유 및 만료일 설정
    * 포인트 히스토리 자동 기록

## 📋 **11단계: 긴급 상황 대응 (최저 우선순위)**

### 11.1 긴급 점검 모드
- [x] **긴급 점검 관리** (`/admin/auth/emergency`) ✅ AdminEmergencyController로 구현
  - [x] `GET /admin/auth/emergency/maintenance` - 점검 모드 설정 ✅ AdminEmergencyController::maintenance
  - [x] `POST /admin/auth/emergency/maintenance` - 점검 모드 활성화/비활성화 ✅ AdminEmergencyController::toggleMaintenance
  - [x] `GET /admin/auth/emergency/block-login` - 로그인 차단 설정 ✅ AdminEmergencyController::blockLogin
  - [x] `POST /admin/auth/emergency/block-login` - 로그인 차단 활성화/비활성화 ✅ AdminEmergencyController::toggleBlockLogin
  - [x] `POST /admin/auth/emergency/alert` - 긴급 알림 발송 ✅ AdminEmergencyController::sendAlert
  - [x] `GET /admin/auth/emergency/system-check` - 시스템 상태 점검 ✅ AdminEmergencyController::systemCheck
  - [x] `POST /admin/auth/emergency/kill-all-sessions` - 모든 세션 강제 종료 ✅ AdminEmergencyController::killAllSessions

### 11.2 보안 사고 대응
- [x] **보안 사고 관리** (`/admin/auth/security-incident`) ✅ AdminSecurityIncidentController로 구현
  - [x] `GET /admin/auth/security-incident` - 보안 사고 목록 ✅ AdminSecurityIncidentController::index
  - [x] `POST /admin/auth/security-incident` - 보안 사고 등록 ✅ AdminSecurityIncidentController::store
  - [x] `GET /admin/auth/security-incident/{id}` - 사고 상세 ✅ AdminSecurityIncidentController::show
  - [x] `PUT /admin/auth/security-incident/{id}` - 사고 업데이트 ✅ AdminSecurityIncidentController::update
  - [x] `POST /admin/auth/security-incident/{id}/resolve` - 사고 해결 ✅ AdminSecurityIncidentController::resolve
  - [x] `POST /admin/auth/security-incident/{id}/action` - 조치 추가 ✅ AdminSecurityIncidentController::addAction

---

## 🎯 **구현 우선순위 요약**

1. **1단계 (최우선)**: 기본 로그인/로그아웃, 회원가입, 사용자 목록 관리
2. **2단계 (높음)**: 이메일 인증, 비밀번호 관리, 계정 상태 관리, 승인 시스템, API 인증
3. **3단계 (중간)**: 2FA, 세션 관리, 블랙리스트, JWT 토큰
4. **4단계 (중간)**: 휴면계정, 사용자 프로필, 추가정보, 소셜 로그인, 사용자 메시지
5. **5단계 (중간)**: 다국어, 국가 설정, 브라우저 감지
6. **6단계 (중간)**: 이메일/SMS 관리
7. **7단계 (낮음)**: 회원등급, 회원유형, 디바이스 관리
8. **8단계 (낮음)**: 포인트, eMoney 시스템
9. **9단계 (낮음)**: 로그 관리, 통계 분석
10. **10단계 (낮음)**: 시스템 설정, 약관 관리, 대량 작업
11. **11단계 (최저)**: 긴급 상황 대응

---

## 🛣️ **라우트 구조 요약**

### **일반 사용자 라우트**
- **세션 로그인/로그아웃**: `/login/*`, `/logout`
- **JWT 로그인/로그아웃**: `/signin/*`, `/signup/*`, `/signout`
- **회원가입**: `/register/*`
- **사용자 홈**: `/home/*`
- **계정 관리**: `/home/account/*`
- **eMoney**: `/home/emoney/*`
- **메시지**: `/home/message/*`
- **소셜 로그인**: `/login/social/*`

### **관리자 라우트**
- **사용자 관리**: `/admin/auth/users/*`
- **승인 관리**: `/admin/auth/approval/*`
- **보안 관리**: `/admin/auth/security/*`
- **eMoney 관리**: `/admin/auth/emoney/*`
- **소셜 관리**: `/admin/auth/social/*`
- **메시지 관리**: `/admin/auth/message/*`
- **시스템 설정**: `/admin/auth/settings/*`

### **API 라우트**
- **API 인증**: `/api/auth/*`
- **사용자 API**: `/api/user/*`

### **모듈별 라우트**
- **auth-api**: API 인증 (Sanctum 토큰)
- **auth-emoney**: eMoney 시스템
- **auth-profile**: 사용자 프로필 관리
- **auth-social**: 소셜 로그인 (Google, Facebook, GitHub, Naver, Kakao)
- **auth-users**: 사용자 메시지 시스템

---

## 📋 **라우트 설계 규칙**

### **1. 기본 라우트 구조**
```
/{prefix}/{module}/{action}/{id?}
```

### **2. 프리픽스 규칙**
- **일반 사용자**: `/login/*`, `/register/*`, `/home/*`
- **JWT 인증**: `/signin/*`, `/signup/*`, `/signout`
- **관리자**: `/admin/auth/*`
- **API**: `/api/auth/*`, `/api/user/*`

### **3. HTTP 메서드 규칙**
- **GET**: 조회, 폼 표시
- **POST**: 생성, 처리, 인증
- **PUT**: 수정, 업데이트
- **DELETE**: 삭제, 비활성화
- **PATCH**: 부분 수정

### **4. 액션 명명 규칙**
- **목록**: `index`, `list`
- **상세**: `show`, `detail`
- **생성**: `create`, `store`
- **수정**: `edit`, `update`
- **삭제**: `destroy`, `delete`
- **상태변경**: `activate`, `deactivate`, `suspend`, `approve`, `reject`
- **일괄처리**: `bulk-{action}`

### **5. 중첩 라우트 규칙**
- **사용자 관련**: `/home/account/{action}`
- **관리자 사용자**: `/admin/auth/users/{id}/{action}`
- **관리자 설정**: `/admin/auth/settings/{type}`

### **6. 특수 라우트 규칙**
- **콜백**: `/{provider}/callback`
- **인증**: `/{action}/verify`, `/{action}/confirm`
- **JWT 토큰**: `/signin/refresh`, `/signout`
- **설정**: `/{module}/settings`
- **통계**: `/{module}/statistics`
- **내보내기**: `/{module}/export`
- **가져오기**: `/{module}/import`

### **7. RESTful API 규칙**
```
GET    /api/auth/users          # 사용자 목록
GET    /api/auth/users/{id}     # 사용자 상세
POST   /api/auth/users          # 사용자 생성
PUT    /api/auth/users/{id}     # 사용자 수정
DELETE /api/auth/users/{id}     # 사용자 삭제
```

### **8. 라우트 그룹 규칙**
- **미들웨어별 그룹화**: `web`, `api`, `auth`, `admin`
- **기능별 그룹화**: `auth`, `profile`, `emoney`, `social`
- **권한별 그룹화**: `guest`, `user`, `admin`, `super`

### **9. 파라미터 규칙**
- **ID 파라미터**: `{id}` (숫자)
- **슬러그 파라미터**: `{slug}` (문자열)
- **선택적 파라미터**: `{id?}`
- **제약조건**: `->where('id', '[0-9]+')`

### **10. 라우트 이름 규칙**
- **일반 사용자**: `home.{module}.{action}`
- **JWT 인증**: `jwt.{action}` (signin, signup, signout)
- **관리자**: `admin.auth.{module}.{action}`
- **API**: `api.{module}.{action}`
- **소셜 로그인**: `oauth.{provider}.{action}`

### **11. JWT 라우트 특별 규칙**
- **JWT 로그인**: `/signin` (GET: 폼, POST: 토큰 생성)
- **JWT 회원가입**: `/signup` (GET: 폼, POST: 토큰 생성)
- **JWT 로그아웃**: `/signout` (GET: 폼, POST: 토큰 무효화)
- **토큰 갱신**: `/signin/refresh` (GET: 토큰 갱신)
- **토큰 관리**: `/home/account/tokens/*` (사용자 토큰 관리)
- **관리자 토큰**: `/admin/auth/jwt/*` (관리자 토큰 관리)

---

## 📊 **구현 진행 상황 요약**

### ✅ **완료된 기능 (75%)**

#### 기본 구조 및 설정
- ✅ 디렉토리 구조 및 패키지 설정
- ✅ 모델 및 마이그레이션 (9개 모델, 18개 마이그레이션 - JWT 토큰 테이블 추가)
- ✅ 라우트 설정 (admin.php, web.php, api.php)
- ✅ JinyAuthServiceProvider 설정

#### 핵심 인증 시스템
- ✅ **세션 기반 로그인** - AuthLoginController
- ✅ **로그아웃 기능** - AuthLogoutController  
- ✅ **회원가입 시스템** - AuthRegisterController, AuthRegistStoreController
- ✅ **약관 동의** - AuthRegisterTermsController
- ✅ **회원가입 승인** - AuthApprovalController
- ✅ **사용자 홈 대시보드** - HomeController (대시보드, 프로필, 설정)
- ✅ **비밀번호 재설정** - PasswordResetController
- ✅ **비밀번호 변경** - PasswordController (변경, 강제 변경)
- ✅ **비밀번호 정책** - PasswordPolicyController
- ✅ **이메일 인증** - EmailVerificationController
- ✅ **계정 상태 관리** - AccountStatusController (활성화, 비활성화, 정지, 해제)

#### JWT 인증 시스템
- ✅ **JWT 로그인** - AuthJwtSigninController (토큰 생성, 갱신)
- ✅ **JWT 회원가입** - AuthJwtSignupController (회원가입 후 토큰 발급)
- ✅ **JWT 로그아웃** - AuthJwtSignoutController (토큰 무효화, 전체 기기 로그아웃)
- ✅ **JWT 토큰 관리** - jwt_tokens 테이블 및 마이그레이션
- ✅ **JWT 뷰 파일** - signin.blade.php, signup.blade.php, signout.blade.php

#### API 인증 (Sanctum)
- ✅ **API 인증** - ApiAuthController (로그인, 로그아웃, 토큰 관리)
- ✅ **API 회원가입** - API를 통한 회원가입
- ✅ **API 토큰 관리** - Sanctum 토큰 생성, 조회, 삭제

#### 관리자 CRUD
- ✅ **AuthAccounts** - 회원 관리 (6개 컨트롤러, 5개 뷰)
- ✅ **AuthAccountLogs** - 활동 로그 (4개 컨트롤러, 3개 뷰)
- ✅ **AuthLoginHistory** - 로그인 기록 (4개 컨트롤러, 3개 뷰)

### ⏳ **진행 중 (0%)**
- 없음

### ❌ **미구현 (30%)**

#### 핵심 인증 시스템
- ❌ 2단계 인증 (2FA)

#### 관리자 CRUD (6개)
- ❌ Roles (역할) CRUD
- ❌ Grades (등급) CRUD
- ❌ TwoFactor (2FA) CRUD
- ❌ Blacklist CRUD
- ❌ DormantAccounts (휴면계정) CRUD
- ❌ Countries (국가) CRUD

#### 고급 기능
- ❌ API 인증 (Sanctum)
- ❌ 소셜 로그인
- ❌ 세션 관리
- ❌ 통계 및 분석

### 📈 **전체 진행률: 70%**

---

## 🚀 **다음 구현 순서 (권장)**

1. ~~**JWT 인증 시스템**~~ ✅ 완료
2. ~~**사용자 홈 대시보드**~~ ✅ 완료
3. ~~**비밀번호 재설정**~~ ✅ 완료
4. ~~**이메일 인증**~~ ✅ 완료
5. **나머지 관리자 CRUD** (Roles, Grades 등) - 최우선
6. **2FA 및 보안 기능**
7. **API 인증 (Sanctum)**
8. **소셜 로그인**
9. **세션 관리**
10. **통계 및 분석**

