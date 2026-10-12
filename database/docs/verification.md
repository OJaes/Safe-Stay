# MariaDB 검증 절차 및 증빙 상태

## 당시 기록에 있는 점검 명령

```bash
sudo systemctl status mariadb
sudo chronyc sources
```

Master SQL:

```sql
SHOW MASTER STATUS;
```

Slave SQL:

```sql
SHOW SLAVE STATUS\G
```

원본 문서에는 Slave에서 `Slave_IO_Running: Yes`와 `Slave_SQL_Running: Yes`를 확인하는 점검 지시가 있습니다. **실제 출력 로그나 결과 스크린샷이 제공된 것은 아니므로, 두 값이 실제로 Yes였다고 확정하지 않습니다.**

## 재구축 시 검증할 항목

- Master/Slave의 `server-id`가 서로 다른지
- Master Binary Log 및 Slave 복제 스레드가 정상인지
- Slave의 복제 지연 및 오류가 없는지
- `users` / `bookings` 스키마가 양쪽에서 일치하는지
- 방화벽이 필요한 DB 접근만 허용하는지
- MaxScale의 `4000/TCP`를 사용하는 실제 애플리케이션 경로가 있는지
- Master 장애 후 자동 승격 및 복구 후 재합류가 **실제로 성공하는지**

현재 이 저장소에는 실시간 VM 점검 결과, SQL 데이터 덤프, 장애 조치 성공 로그를 포함하지 않습니다.
