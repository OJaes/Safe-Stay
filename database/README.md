# MariaDB Master / Slave | SafeStay

> 호텔 예약 서비스의 MariaDB 데이터베이스, Master–Slave 복제 및 예약 데이터 스키마 구축 기록

## 1. 구성 및 역할

| 구성 요소 | 역할 | pfSense 설계 주소 |
| --- | --- | --- | --- |
| MariaDB Master | 호텔 예약 서비스 쓰기·복제 원본 | `192.168.20.10` |
| MariaDB Slave | Master 데이터 복제, 장애 조치 후보 | `192.168.20.20` |
| PMM + MaxScale | DB 모니터링·장애 조치 설정 | PMM `192.168.30.30` (MaxScale 주소는 별도 확정 근거 없음) |

> `172.30.1.x`는 DB/PMM 구축 명령에 기록된 주소이고, `192.168.x.x`는 pfSense 네트워크 설계 문서에 기록된 주소입니다. 두 주소 체계가 실제로 동일한 최종 VM 환경에 적용되었다고 단정하지 않습니다.

## 2. 주요 구성

- Ubuntu 계열 환경에서 MariaDB 서버 설치 및 `chrony` 시간 동기화 설정
- Master: `server-id=1`, Binary Log 활성화
- Slave: `server-id=2`, Master 접속 정보와 로그 위치를 통한 복제 설정
- 후속 장애 조치 구성 단계에서 Slave에 `log_bin`, `log_slave_updates=1` 추가 및 GTID 기반 복제 전환 기록
- 호텔 예약 서비스용 `safestay_db` 및 `users`, `bookings` 테이블 구성
- PMM으로 Master/Slave 모니터링, MaxScale로 읽기·쓰기 라우팅 및 자동 장애 조치 **설정 기록**

## 3. 문서 및 파일

- [MariaDB 설치·복제 구축 기록](docs/replication.md)
- [DB 스키마 및 공개본 변경점](docs/schema.md)
- [검증 절차와 확인되지 않은 항목](docs/verification.md)
- [Master 설정 예시](configs/60-safestay-master.cnf.example)
- [Slave 설정 예시](configs/60-safestay-slave.cnf.example)
- [민감정보·초기 데이터 제거한 SQL 스키마](sql/schema.sql)
- [PMM / MaxScale 문서](../pmm-maxscale/README.md)

## 4. 주의 사항

- 원본의 복제 계정, 웹 DB 계정, PMM 계정 및 MaxScale 비밀번호는 공개하지 않습니다. **실제 환경에서 동일한 비밀번호를 사용했다면 변경이 필요합니다.**
- 원본의 `ufw allow 3306/tcp`, `bind-address=0.0.0.0`은 접근 범위가 넓습니다. 운영 시 방화벽에서 허용 IP를 제한해야 합니다.
- 원본 DB 초기화 구문의 `DROP DATABASE IF EXISTS` 및 초기 관리자 계정 삽입문은 공개용 SQL에서 제외했습니다.
- **복제 정상 동작, 장애 전환 성공, 웹 서비스의 MaxScale 경유 여부**는 현재 제공된 명령 기록만으로 검증되지 않습니다.
