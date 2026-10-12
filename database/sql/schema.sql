-- SafeStay public schema (sanitized adaptation of db-테이블-구성.txt)
-- Intentionally excludes DROP DATABASE, CREATE USER / GRANT, and admin seed.
-- Do not run against an existing production DB without a backup and review.

CREATE DATABASE IF NOT EXISTS safestay_db
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE safestay_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '회원 고유 식별자',
    username VARCHAR(50) NOT NULL UNIQUE COMMENT '로그인 아이디',
    password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호 (Bcrypt)',
    name VARCHAR(50) NOT NULL COMMENT '회원 이름',
    email VARCHAR(100) NOT NULL COMMENT '이메일 주소',
    phone VARCHAR(20) COMMENT '연락처 (선택적 입력)',
    role VARCHAR(20) DEFAULT 'user' NOT NULL COMMENT '사용자 권한 (user 또는 admin)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '가입 일자'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 정보 테이블';

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '예약 고유 식별자',
    user_id INT NOT NULL COMMENT '회원 고유 식별자 (users 테이블 참조)',
    check_in DATE NOT NULL COMMENT '체크인 날짜',
    check_out DATE NOT NULL COMMENT '체크아웃 날짜',
    room_name VARCHAR(100) NOT NULL COMMENT '선택한 객실명',
    guests INT NOT NULL COMMENT '투숙 인원수',
    guest_name VARCHAR(50) NOT NULL COMMENT '투숙객 성함',
    guest_phone VARCHAR(20) NOT NULL COMMENT '투숙객 연락처',
    guest_email VARCHAR(100) NOT NULL COMMENT '투숙객 이메일',
    special_requests TEXT COMMENT '특별 요청사항',
    total_price INT NOT NULL COMMENT '총 결제 금액',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '예약 접수 일시',
    CONSTRAINT fk_user_booking FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='객실 예약 정보 테이블';
