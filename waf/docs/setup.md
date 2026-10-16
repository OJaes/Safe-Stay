# ModSecurity 설치 및 적용 기록

## 설치

```bash
sudo apt install -y libapache2-mod-security2
sudo a2enmod security2
```

## 기본 설정

```bash
sudo cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf
sudo sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/modsecurity/modsecurity.conf
sudo systemctl restart apache2
```

## Custom Rule 경로

```text
/etc/modsecurity/crs/REQUEST-999-CUSTOM-RULES.conf
```

```bash
# 참고: 재현 시 점검용
sudo apache2ctl -t
sudo grep -n 'SecRuleEngine' /etc/modsecurity/modsecurity.conf
sudo grep -R 'REQUEST-999-CUSTOM-RULES' /etc/apache2 /etc/modsecurity 2>/dev/null
sudo systemctl status apache2 --no-pager
```

`DetectionOnly`는 공격을 차단하지 않고 기록하는 모드이므로, 실험 시 탐지 모드와 차단 모드의 차이를 분리해서 보고해야 합니다.
