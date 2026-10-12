# Apache2 / PHP / GoAccess 구축 기록

> 출처: `웹-서버.txt`에 적힌 명령과 코드. 아래 검증 명령은 재현 시 확인하기 위한 절차이며, 현재 VM에서 재실행한 결과는 아닙니다.

## 1. Apache2 설치

원본에 명시된 설치 명령:

```bash
sudo apt install -y apache2
```

PHP 애플리케이션을 실행하려면 서버에 PHP 및 PDO MySQL 드라이버가 필요합니다. **이 패키지들의 구체적인 설치 명령과 버전은 공유된 웹 서버 문서에 남아 있지 않습니다.**

설정 재현 시 점검할 명령(참고):

```bash
apache2 -v
php -v
php -m | grep -E 'PDO|pdo_mysql'
sudo systemctl status apache2 --no-pager
```

## 2. 웹 소스 배치

원본 코드의 웹 루트는 `/var/www/html`이며, `db.php`를 여러 페이지에서 `require_once`로 불러옵니다. 공개본은 `src/`에 정리했습니다.

```bash
# 테스트 환경에서만 실행. 배포 전 보안 검토 필요.
sudo cp -r src/. /var/www/html/
```

## 3. DB 연결

원본은 `db.php`에서 DB IP/계정/암호를 하드코딩했습니다. 공개본은 다음 PHP 환경 변수를 사용하도록 수정했습니다.

| 변수 | 설명 |
| --- | --- |
| `SAFESTAY_DB_HOST` | DB Master 주소 (설계 예: `192.168.20.10`) |
| `SAFESTAY_DB_NAME` | DB 이름 (기본값 `safestay_db`) |
| `SAFESTAY_DB_USER` | DB 전용 사용자 |
| `SAFESTAY_DB_PASSWORD` | DB 사용자 암호 (공개 금지) |

Apache2/PHP 실행 프로세스에 위 환경 변수가 전달되도록 **서버에서 별도로 구성**해야 합니다. 공유된 원본에는 그 배포 방식이 없으므로 이 저장소가 재현 가능한 전체 배포 스크립트를 제공한다고 주장하지 않습니다.

## 4. GoAccess

원본 설치 및 실행 명령:

```bash
sudo apt install goaccess -y
sudo goaccess /var/log/apache2/access.log -o /var/www/html/stats.html --log-format=COMBINED --real-time-html &
sudo systemctl daemon-reload
sudo systemctl enable --now goaccess
```

> 원본에는 GoAccess용 systemd 서비스 유닛 정의가 없습니다. 따라서 `enable --now goaccess`의 실제 성공 여부와 재부팅 후 지속 동작은 별도로 확인해야 합니다. 실시간 HTML 출력 기능은 별도 WebSocket 설정이 필요할 수 있습니다.

## 5. 관련 WAF

동일 웹 서버에서 `libapache2-mod-security2`를 설치하고 Apache 모듈을 활성화하는 명령이 기록되어 있습니다. [WAF 설정 기록](../../waf/docs/setup.md)을 참고하세요.
