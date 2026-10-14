# Apache2 / PHP / GoAccess 구축 기록


## 1. Apache2 설치

```bash
sudo apt install -y apache2
```

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

| 변수 | 설명 |
| --- | --- |
| `SAFESTAY_DB_HOST` | DB Master 주소 (설계 예: `192.168.20.10`) |
| `SAFESTAY_DB_NAME` | DB 이름 (기본값 `safestay_db`) |
| `SAFESTAY_DB_USER` | DB 전용 사용자 |
| `SAFESTAY_DB_PASSWORD` | DB 사용자 암호 (공개 금지) |

Apache2/PHP 실행 프로세스에 위 환경 변수가 전달되도록 **서버에서 별도로 구성**해야 합니다. 

## 4. GoAccess

```bash
sudo apt install goaccess -y
sudo goaccess /var/log/apache2/access.log -o /var/www/html/stats.html --log-format=COMBINED --real-time-html &
sudo systemctl daemon-reload
sudo systemctl enable --now goaccess
```
