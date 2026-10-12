# Web Server | SafeStay

> 호텔 예약 웹 서비스의 Apache2 + PHP 구축 기록과 소스코드 공개본

**자료 기준:** 프로젝트 당시 공유된 `웹-서버.txt`. 웹 서버 VM의 현재 실행 상태를 새로 확인한 결과는 아닙니다. 아래 PHP 파일은 당시 작성한 코드에서 추출했으며, DB 접속 비밀정보 처리 부분만 공개 저장소에 맞게 수정했습니다.

## 1. 담당 기능 및 구성

- Apache2 기반 PHP 웹 서비스
- 호텔 소개·객실 목록·객실 상세 페이지
- 회원가입, 로그인/로그아웃 및 세션 관리
- 예약 입력·DB 저장, 관리자 예약 현황 및 사용자 관리
- Apache 접근 로그를 GoAccess로 분석하는 구성 기록
- 같은 웹 서버에 ModSecurity 모듈 설치 기록 ([WAF 문서](../waf/README.md))

## 2. 아키텍처

```text
외부 사용자 → pfSense → Suricata Inline IPS → Apache2 + ModSecurity → PHP → MariaDB Master
                                                           └→ Apache access.log → GoAccess
```

- 웹 서버 설계 IP: `192.168.10.10` (WEB VLAN)
- DB Master 설계 IP: `192.168.20.10` (`3306/TCP`)
- 원본 `db.php`에 남은 DB 주소는 `172.30.1.52`로, 설계 IP와 일치하지 않습니다. 공개본은 주소를 하드코딩하지 않고 환경 변수로 분리했습니다.
- 프로젝트 문서의 실제 웹 서버 설치 명령은 **Apache2**입니다. Nginx 구성을 이 서버의 확정 설치 내역으로 표기하지 않습니다.

## 3. 소스 구조

```text
web-server/
├── README.md
├── src/
│   ├── index.php                 # 메인
│   ├── about.php                 # 호텔 소개
│   ├── rooms.php                 # 객실 목록
│   ├── room-standard-twin.php    # 객실 상세
│   ├── room-deluxe-king.php      # 객실 상세
│   ├── room-suite.php            # 객실 상세
│   ├── booking.php               # 예약 처리
│   ├── login.php                 # 로그인
│   ├── logout.php                # 로그아웃
│   ├── register.php              # 회원가입
│   ├── db.php                    # 환경 변수 기반 DB 연결 (공개본 수정)
│   └── admin/
│       ├── index.php             # 관리자 대시보드
│       └── users.php             # 사용자 관리
└── docs/
    ├── setup.md                  # 설치 및 구성 기록
    └── security-review.md        # 공개 전 조치 및 보안 검토
```

## 4. 주요 구현

| 기능 | 소스 근거 |
| --- | --- |
| 로그인 | `password_verify()` 및 세션의 사용자·권한 정보 설정 |
| 회원가입 | `password_hash(..., PASSWORD_DEFAULT)` 사용 |
| DB 접근 | PDO + Prepared Statement + 예외 처리 |
| 예약 | 로그인 여부 검사, 체크인·체크아웃 검증, 예약 DB INSERT |
| 관리자 | `admin` 역할 검사, 예약 조회·회원 권한 변경·삭제 기능 |
| WAF 로그 화면 | 관리자 페이지에서 `/var/log/safestay_waf.log` 읽기 시도 |

> WAF 로그 파일을 실제로 생성하는 ModSecurity 로깅 설정은 원본에 포함되어 있지 않습니다. 화면에 로그가 표시되었다고 단정하지 않습니다.

## 5. 설치 및 검증

- [Apache2 / PHP / GoAccess 설정 기록](docs/setup.md)
- [공개본 수정 내역·보안 검토](docs/security-review.md)
- [WAF 정책 및 설정 문서](../waf/README.md)

**중요:** 이 코드는 팀 프로젝트 당시의 학습·검증용 소스입니다. 운영 환경에 배포하기 전에 CSRF 방어, 관리자 입력 검증, 예약 금액 서버측 검증, 세션 보안, TLS/접근 제어 및 통합 테스트가 필요합니다. 실서비스용으로 보안 검증을 마친 코드가 아닙니다.
