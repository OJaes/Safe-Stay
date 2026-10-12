# MariaDB MaxScale 구축 기록

## 1. 설치

원본 기록에서 **PMM 서버**에 MaxScale을 설치했습니다.

```bash
sudo apt-get update
sudo apt-get install software-properties-common curl -y
curl -LsS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
sudo apt-get update
sudo apt-get install maxscale -y
sudo vi /etc/maxscale.cnf
```

위 설치 명령은 원본 기록을 보존한 것입니다. 외부 스크립트 실행 전 배포처와 스크립트 내용을 검증해야 합니다.

## 2. 원본 `/etc/maxscale.cnf`의 핵심 항목

| 섹션 | 기록된 값 | 역할 |
| --- | --- | --- |
| `[server1]` | `172.30.1.61:3306` | Master DB |
| `[server2]` | `172.30.1.62:3306` | Slave DB |
| `[MariaDB-Monitor]` | `module=mariadbmon` | 복제 토폴로지 모니터링 |
| `[MariaDB-Monitor]` | `auto_failover=true` | 자동 장애 조치 설정 |
| `[MariaDB-Monitor]` | `auto_rejoin=true` | 복구 노드 재합류 설정 |
| `[Read-Write-Service]` | `router=readwritesplit` | 읽기·쓰기 요청 라우팅 |
| `[Read-Write-Listener]` | `port=4000` | 애플리케이션 접속 포트 |

[비밀번호를 제거한 설정 예시](../config/maxscale.cnf.example)는 **원본 설정의 주요 섹션을 옮긴 샘플**이며, 특정 MaxScale 버전에서의 실행 가능성을 검증한 완전한 운영 설정 파일은 아닙니다.

## 3. 계정과 권한

원본에는 `maxscale` 계정 생성과 `SELECT ON mysql.*`, `SHOW DATABASES`, `REPLICATION CLIENT`, `REPLICATION SLAVE`, `SUPER`, 후속 `RELOAD`, `READ_ONLY ADMIN` 권한 추가가 기록되어 있습니다.

- 이 권한 목록은 **당시 구축 기록**입니다. 실제로 필요한 권한은 MariaDB/MaxScale 버전 및 모니터 기능에 따라 검토해야 합니다.
- 원본의 `maxscale@'%'`는 범위가 넓으므로 운영 시 접속 출발지를 제한해야 합니다.
- 계정 비밀번호는 예시 파일에서 플레이스홀더로 대체했습니다.

## 4. 실행 및 점검

```bash
sudo systemctl enable maxscale
sudo systemctl start maxscale
sudo maxctrl list servers
```

원본에는 위 점검 명령이 있으나 `maxctrl list servers`의 실제 출력은 제공되지 않았습니다.
