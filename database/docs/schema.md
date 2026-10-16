# 호텔 예약 DB 스키마

## 테이블

| 테이블 | 핵심 컬럼 | 역할 |
| --- | --- | --- |
| `users` | `id`, `username`, `password`, `name`, `email`, `phone`, `role`, `created_at` | 회원 및 관리자 계정 |
| `bookings` | `id`, `user_id`, `check_in`, `check_out`, `room_name`, `guests`, `guest_name`, `guest_phone`, `guest_email`, `special_requests`, `total_price`, `created_at` | 객실 예약 |

- `users.username`에 UNIQUE 제약이 있습니다.
- `bookings.user_id`는 `users.id`를 참조합니다.
- 외래키 `fk_user_booking`에 `ON DELETE CASCADE`가 적용되어 있습니다.
- `users.password`는 Bcrypt 해시 저장을 전제로 하는 컬럼입니다.
- 테이블 엔진은 InnoDB, 문자셋은 `utf8mb4`입니다.

## 공개용 SQL

[sql/schema.sql](../sql/schema.sql)은 원본의 테이블 구조를 유지하면서 다음을 변경했습니다.

1. 원본의 `DROP DATABASE IF EXISTS safestay_db` 제거: 기존 데이터 손실 방지
2. DB 사용자 생성 및 원본 비밀번호 제거: 계정 정보 비공개
3. 초기 관리자 계정 INSERT 제거: 계정/해시/개인정보 공개 방지
4. `CREATE DATABASE IF NOT EXISTS` 및 `CREATE TABLE IF NOT EXISTS` 사용: 공개 예시의 재실행 위험 완화
