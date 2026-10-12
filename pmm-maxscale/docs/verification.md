# PMM / MaxScale 점검 명령 및 검증 범위

## 기록된 확인 명령

```bash
sudo systemctl status mariadb
sudo pmm-admin status
sudo systemctl status maxscale
sudo maxctrl list servers
```

MariaDB 복제 상태:

```sql
SHOW SLAVE STATUS\G
```

## 결과 구분

| 항목 | 공유 자료로 확인되는 것 | 현재 확인되지 않는 것 |
| --- | --- | --- |
| PMM | Server Docker 실행 명령, Master/Slave Client 등록 명령 | 대시보드의 실측 지표·접속 성공 화면 |
| MaxScale | Backend 2대, Monitor, readwritesplit, Listener 4000 설정 | `maxctrl list servers` 실제 결과 |
| Auto Failover | `auto_failover=true` 설정 | Master 장애 후 자동 승격 성공 |
| Auto Rejoin | `auto_rejoin=true`, 수동 `rejoin` 명령 기록 | 복구 노드 재합류 성공 |
| 서비스 연결 | 웹 → Master 3306 정책 기록 | 웹 → MaxScale 4000 연결 여부 |

운영 환경 또는 새로운 실습 환경에서 재검증한 후에만 '성공'으로 기록해야 합니다.
