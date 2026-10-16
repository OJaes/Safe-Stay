# MaxScale 자동 장애 조치 및 재합류 구성

## 1. 목표 및 설정

MariaDB Monitor 섹션:

```ini
[MariaDB-Monitor]
type=monitor
module=mariadbmon
servers=server1,server2
user=maxscale
password=REPLACE_WITH_STRONG_SECRET
auto_failover=true
auto_rejoin=true
```

- `auto_failover=true`: Master 장애 발생 시 조건을 만족하는 복제 서버를 승격하도록 설정
- `auto_rejoin=true`: 장애 복구 노드를 복제 토폴로지에 재합류시키도록 설정
- Slave 측에서 GTID 기반 복제로 전환한 기록이 있습니다.
- Slave에 Binary Log 및 `log_slave_updates=1`을 추가한 기록이 있습니다.

## 2. 원본에 기록된 수동 재합류 명령

```bash
sudo maxctrl call command mariadbmon rejoin MariaDB-Monitor server1
```

## 3. 서비스 연속성 검증의 핵심 조건

pfSense/웹 기록에는 웹 서버에서 DB Master `3306/TCP`로 직접 연결하는 경로가 존재합니다. MaxScale이 `4000/TCP`를 열어도 애플리케이션이 그 Listener를 사용하도록 설정되지 않았다면 Master 장애 시 웹 서비스가 자동으로 새로운 Master에 연결된다고 보장할 수 없습니다.

실제 무중단/자동 복구를 주장하려면 다음 증빙이 추가로 필요합니다.

1. 웹 애플리케이션의 DB 접속 대상이 MaxScale Listener인지 확인
2. 정상 상태의 `maxctrl list servers` 및 복제 상태 기록
3. 테스트 데이터 정합성·백업 확인 후 통제된 장애 테스트 수행
4. 장애 시 승격 상태 및 웹 서비스 정상 동작 확인
5. 복구 후 재합류 상태 및 데이터 정합성 확인
