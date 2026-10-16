# MariaDB Master / Slave 복제 구축 기록

## 1. 공통: 시간 동기화

```bash
sudo apt update -y
sudo apt install chrony -y
sudo systemctl enable --now chrony
sudo chronyc sources
```

## 2. Master 서버

```bash
sudo apt install mariadb-server ufw -y
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
sudo vi /etc/mysql/mariadb.conf.d/50-server.cnf
```

`50-server.cnf`의 핵심 설정:

```ini
[mysqld]
bind-address = 0.0.0.0
server-id = 1
log_bin = /var/log/mysql/mysql-bin.log
```

아래 SQL은 **자격증명 제거한 명령 형식 예시**입니다.

```sql
-- 실제 실행 전 <SLAVE_IP>, <STRONG_REPLICATION_PASSWORD>를 교체
CREATE USER 'repl_user'@'<SLAVE_IP>' IDENTIFIED BY '<STRONG_REPLICATION_PASSWORD>';
GRANT REPLICATION SLAVE ON *.* TO 'repl_user'@'<SLAVE_IP>';
FLUSH PRIVILEGES;
SHOW MASTER STATUS;
```

`repl_user`의 Host를 `%`로 사용했습니다. 위 예시는 공개 저장소에서 권장할 수 있도록 Slave 주소로 제한한 **수정 예시**입니다.

## 3. Slave 서버

```bash
sudo apt update -y
sudo apt install mariadb-server ufw -y
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
sudo vi /etc/mysql/mariadb.conf.d/50-server.cnf
```

초기 Slave 설정에는 `bind-address=0.0.0.0`, `server-id=2`가 기록되어 있습니다. 이후 MaxScale 장애 조치 단계에서 Binary Log와 `log_slave_updates=1`을 추가했습니다. [Slave 설정 예시](../configs/60-safestay-slave.cnf.example)를 참고하세요.

```sql
-- MASTER_LOG_FILE / MASTER_LOG_POS는 Master에서 확인한 현재 값 사용
CHANGE MASTER TO
    MASTER_HOST = '<MASTER_IP>',
    MASTER_USER = 'repl_user',
    MASTER_PASSWORD = '<STRONG_REPLICATION_PASSWORD>',
    MASTER_LOG_FILE = '<CURRENT_MASTER_LOG_FILE>',
    MASTER_LOG_POS = <CURRENT_MASTER_LOG_POS>;
START SLAVE;
SHOW SLAVE STATUS\G
```

## 4. 후속: GTID 기반 복제 전환 기록

```sql
STOP SLAVE;
CHANGE MASTER TO MASTER_USE_GTID = slave_pos;
START SLAVE;
SHOW SLAVE STATUS\G
```

## 5. 네트워크 주의

- 원본 구축 기록의 Master/Slave 주소: `172.30.1.61` / `172.30.1.62`
- pfSense 설계 문서의 Master/Slave 주소: `192.168.20.10` / `192.168.20.20`
- pfSense 정책상 웹 서버(`192.168.10.10`) → Master(`192.168.20.10`) `3306/TCP` 허용 기록이 있습니다.
- DB 서버 자체 UFW 규칙과 pfSense 방화벽 규칙은 별도로 검토해야 합니다.
