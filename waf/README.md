# ModSecurity WAF | SafeStay

> Apache2 웹 서버의 HTTP 요청 검사 및 Suricata IPS의 HTTPS 가시성 한계를 보완하기 위한 WAF 구성·정책 설계 기록

## 1. WAF가 필요한 이유

- pfSense: 네트워크·포트 접근 제어
- Suricata Inline IPS: 네트워크 트래픽과 확인 가능한 시그니처 탐지·차단
- ModSecurity: 웹 서버의 HTTP 처리 계층에서 URI, 헤더, Cookie, 요청 본문 등 검사

## 2. 설치 및 동작 모드 기록

```bash
sudo apt install -y libapache2-mod-security2
sudo a2enmod security2
sudo cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf
sudo sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/modsecurity/modsecurity.conf
sudo systemctl restart apache2
```

- `SecRuleEngine On`: 규칙의 차단 동작을 활성화하는 설정
- `SecRuleEngine DetectionOnly`: 탐지 전용으로 전환하는 설정
- Custom Rule 파일로 기록된 경로: `/etc/modsecurity/crs/REQUEST-999-CUSTOM-RULES.conf`

## 3. 정책 설계 범위

| Rule ID | 설계 항목 |
| --- | --- |
| 2008–2010 | JSON/XML 요청 본문 검사 |
| 2011–2012 | Cookie 기반 SQLi/XSS |
| 2013–2014 | HTTP Request Smuggling |
| 2015 | CRLF Injection |
| 2016–2017 | Log4Shell / Shellshock |
| 2018 | SSRF 및 메타데이터 주소 접근 |
| 2019–2020 | 민감 파일·백업 파일 접근 |
| 2021–2022 | 웹셸·이중 확장자 업로드 패턴 |
| 2023 | POST Content-Type 검사 |
| 2024–2025 | 개인키·클라우드 키 응답 노출 |
| 2026 | Stack Trace 노출 |
| 2027–2028 | Secure / HttpOnly Cookie 속성 |

## 4. 문서

- [Apache2 + ModSecurity 설정 기록](docs/setup.md)
- [Rule ID별 설계 의도](docs/rule-design.md)
- [테스트·검증 범위](docs/validation.md)
- [웹 서버 소스](../web-server/README.md)
