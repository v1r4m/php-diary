# Encrypted Diary Service

Laravel 기반의 완전 암호화된 일기장 서비스입니다. 관리자조차 일기 내용을 읽을 수 없도록 설계되었습니다.

## 주요 기능

- **End-to-End 암호화**: 사용자 비밀번호를 기반으로 한 클라이언트 측 암호화
- **관리자 접근 불가**: 데이터베이스 관리자도 일기 내용을 읽을 수 없음
- **공개/비공개 일기**: 일기를 공개하거나 비공개로 설정 가능
- **Docker 기반**: 여러 컨테이너를 활용한 마이크로서비스 아키텍처

## 보안 설계

### 암호화 방식

1. **AES-256-GCM 암호화**
   - 업계 표준의 강력한 암호화 알고리즘
   - 인증된 암호화(Authenticated Encryption)로 데이터 무결성 보장

2. **PBKDF2 키 유도**
   - 사용자 비밀번호에서 암호화 키를 유도
   - 100,000회 반복으로 브루트포스 공격 방어
   - SHA-256 해시 알고리즘 사용

3. **일기별 고유 Salt & IV**
   - 각 일기마다 랜덤한 Salt(소금값) 생성
   - 각 일기마다 랜덤한 IV(초기화 벡터) 생성
   - 동일한 내용도 매번 다르게 암호화됨

### 보안 특징

- **서버는 암호화 키를 절대 저장하지 않음**
- **사용자 비밀번호는 해시되어 저장** (bcrypt)
- **비밀번호를 잊으면 일기 복구 불가능** (설계상 의도된 동작)
- **관리자가 데이터베이스를 직접 봐도 암호문만 보임**

```
사용자 비밀번호 → PBKDF2 → 암호화 키 → AES-256-GCM → 암호화된 일기
```

## 데이터베이스 구조

### 1. hello_world 테이블
```sql
- id: Primary Key
- message: 테스트 메시지
- timestamps: 생성/수정 시간
```
데이터베이스 연결 테스트용 테이블

### 2. users 테이블
```sql
- id: Primary Key
- email: 이메일 (unique)
- password: 해시된 비밀번호
- name: 사용자 이름
- remember_token: 세션 토큰
- timestamps: 생성/수정 시간
```

### 3. diaries 테이블
```sql
- id: Primary Key
- user_id: 사용자 ID (Foreign Key)
- title: 암호화된 제목
- content: 암호화된 내용 (longtext)
- salt: 키 유도용 소금값
- iv: 암호화 초기화 벡터
- is_public: 공개 여부
- published_at: 발행 일시
- timestamps: 생성/수정 시간
```

## Docker 구성

### 서비스 구조

```yaml
services:
  - app: PHP-FPM (Laravel 애플리케이션)
  - nginx: 웹 서버
  - db: PostgreSQL 16 데이터베이스
```

### 로컬 실행

```bash
# 환경 변수 설정
cp .env.example .env

# Docker 컨테이너 빌드 및 실행
docker-compose up -d

# 데이터베이스 마이그레이션
docker-compose exec app php artisan migrate

# 애플리케이션 키 생성
docker-compose exec app php artisan key:generate
```

애플리케이션이 http://localhost:8000 에서 실행됩니다.

### Render.com 배포

1. GitHub에 코드 푸시
2. Render 대시보드에서 "New +" → "Blueprint"
3. 저장소 연결
4. `render.yaml` 자동 감지 및 배포

## API 엔드포인트

### 테스트 엔드포인트

#### GET `/`
데이터베이스 연결 테스트 (Hello World)

```json
{
  "status": "success",
  "message": "Hello World! Database connection is working!",
  "database": "connected"
}
```

#### GET `/test-encryption`
암호화/복호화 테스트

```json
{
  "status": "success",
  "test": {
    "original_content": "...",
    "encrypted_content": "...",
    "decrypted_content": "...",
    "encryption_verified": true
  },
  "security_notes": {
    "encryption_algorithm": "AES-256-GCM",
    "key_derivation": "PBKDF2 with 100,000 iterations",
    "admin_cannot_decrypt": "Encryption key is derived from user password"
  }
}
```

#### GET `/api/info`
서비스 정보 조회

## 기술 스택

- **Backend**: Laravel 10 (PHP 8.2)
- **Database**: PostgreSQL 16
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **Deployment**: Render.com

## 암호화 사용 예시

```php
use App\Services\DiaryEncryptionService;

$encryptionService = new DiaryEncryptionService();

// 암호화
$salt = $encryptionService->generateSalt();
$iv = $encryptionService->generateIV();
$encrypted = $encryptionService->encrypt($content, $password, $salt, $iv);

// 복호화
$decrypted = $encryptionService->decrypt(
    $encrypted['encrypted'],
    $encrypted['tag'],
    $password,
    $salt,
    $iv
);
```

## 보안 주의사항

1. **비밀번호 분실 = 데이터 손실**
   - 이는 의도된 설계입니다
   - 관리자도 복구할 수 없습니다

2. **HTTPS 필수**
   - 프로덕션 환경에서는 반드시 HTTPS 사용
   - 비밀번호 전송 시 암호화 필요

3. **클라이언트 측 암호화 권장**
   - 이상적으로는 브라우저에서 암호화/복호화 수행
   - 현재는 서버 측에서 처리 (데모용)

## 라이센스

MIT
