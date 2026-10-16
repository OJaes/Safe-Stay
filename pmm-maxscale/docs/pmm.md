# PMM Server 및 Client 구축 기록

## 1. PMM Server (당시 Ubuntu / Docker)

```bash
sudo apt update -y
sudo apt install docker.io ufw -y
sudo systemctl enable --now docker
sudo docker pull percona/pmm-server:2
sudo docker create -v /srv --name pmm-data percona/pmm-server:2 /bin/true
sudo docker run -d -p 80:80 -p 443:443 \
  --volumes-from pmm-data --name pmm-server --restart always \
  percona/pmm-server:2
```

`ufw`에서 `80/tcp`, `443/tcp`를 허용하고 HTTPS 대시보드에 접속하도록 기록합니다. 실제 접근 허용 범위는 관리망으로 제한하는 것이 바람직합니다.

> `percona/pmm-server:2`는 당시 기록된 이미지 태그입니다. 이 문서는 최신 버전 설치 가이드가 아니라 프로젝트 당시 구성을 보존한 기록입니다.

## 2. Master/Slave PMM Client

두 DB 서버에 공통으로 설치한 항목:

```bash
sudo apt update -y
sudo apt install -y wget gnupg2 lsb-release curl
wget https://repo.percona.com/apt/percona-release_latest.$(lsb_release -sc)_all.deb
sudo dpkg -i percona-release_latest.$(lsb_release -sc)_all.deb
sudo apt update -y
sudo percona-release enable pmm2-client release
sudo apt install pmm2-client -y
```

당시 PMM 서버 등록 및 MySQL 서비스 등록에 사용한 명령의 구조:

```bash
# 설명용 형식: 실행 전 주소·계정·비밀번호·인증서 검증 정책을 결정
sudo pmm-admin config --server-insecure-tls \
  --server-url='https://<PMM_USER>:<PMM_PASSWORD>@<PMM_SERVER_IP>:443'

sudo pmm-admin add mysql \
  --username='<DB_MONITOR_USER>' --password='<DB_MONITOR_PASSWORD>' \
  --query-source=perfschema
```

- Master와 Slave 양쪽을 등록하는 명령이 있습니다.
- Slave 쪽 명령에는 노드 식별용 `172.30.1.62 generic slave-db` 인자가 추가되어 있습니다. 설치된 PMM Client 버전에 따라 실제 문법을 확인해야 합니다.
- `--server-insecure-tls` 및 비밀번호를 명령행에 직접 전달하는 방식은 실습 기록의 재현을 위한 표시이며, 운영 배포 권장 방식은 아닙니다.
- 원본 PMM 접속 주소는 `172.30.1.63:443`입니다. pfSense 설계 주소는 `192.168.30.30`입니다.

## 3. 확인 명령

```bash
sudo docker ps
sudo pmm-admin status
```

현재 실행 결과나 PMM 그래프 스크린샷은 제공되지 않았습니다.
