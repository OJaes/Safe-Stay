# PMM + MaxScale | SafeStay DB Monitoring & Failover

> MariaDB Master/Slave 모니터링 및 MaxScale 기반 자동 장애 조치 구성 기록

**근거 자료:** `db,-pmm-서버-개설-코드.txt`, `db,-pmm-자동-장애-조치-구현.txt`.

**증빙 범위:** 설정 명령과 파일 내용은 기록되어 있지만, PMM 대시보드의 현재 지표, MaxScale의 실제 장애 전환 성공 로그 및 웹 서비스의 장애 중 무중단 동작 증거는 제공되지 않았습니다.

## 1. 구성 요소

| 구성 | 기록된 내용 |
| --- | --- |
| PMM Server | Docker 이미지 `percona/pmm-server:2`, 웹 포트 `80/443` |
| PMM Client | Master/Slave 각각 `pmm2-client` 설치 및 MySQL 서비스 등록 |
| MaxScale | PMM 서버에 패키지 설치, MariaDB Monitor 사용 |
| 백엔드 | Master `172.30.1.61:3306`, Slave `172.30.1.62:3306` |
| Listener | `4000/TCP`, `readwritesplit` 서비스 |
| 장애 조치 | `auto_failover=true`, `auto_rejoin=true` 설정 |

> IP 주소는 당시 DB/PMM 구축 기록의 주소입니다. pfSense 설계상 PMM 주소 `192.168.30.30`과 서로 다른 주소 체계가 남아 있습니다.

## 2. 구성 흐름

```text
PMM Client (Master) ─┐
                     ├──> PMM Server (모니터링 대시보드)
PMM Client (Slave) ──┘

[애플리케이션이 MaxScale 경유로 설정된 경우]
Web Application → MaxScale :4000 → MariaDB Master / Slave
                     └─ MariaDB Monitor (auto_failover / auto_rejoin)
```

## 3. 관련 문서

- [PMM 설치 및 DB 모니터링 기록](docs/pmm.md)
- [MaxScale 설치·설정 기록](docs/maxscale.md)
- [자동 장애 조치·재합류 구성](docs/failover.md)
- [검증 명령과 미확인 사항](docs/verification.md)
- [비밀번호 제거한 MaxScale 설정 예시](config/maxscale.cnf.example)

## 4. 보안 및 운영상 제한

- PMM Client는 DB `root` 계정으로 메트릭을 수집하도록 기록되어 있습니다. 운영 시 전용 최소 권한 모니터링 계정을 권장합니다.
- `--server-insecure-tls`는 인증서 검증을 완화하므로 운영 환경에서는 적절한 TLS 신뢰 구성이 필요합니다.
