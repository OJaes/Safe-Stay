# ModSecurity WAF | SafeStay

> Apache2 웹 서버의 HTTP 요청 검사 및 Suricata IPS의 HTTPS 가시성 한계를 보완하기 위한 WAF 구성·정책 설계 기록

**근거 자료:** `웹-서버.txt`의 Apache2 ModSecurity 설치 명령 및 `waf-rule.txt`의 Rule ID별 설계 이유. **실제 `REQUEST-999-CUSTOM-RULES.conf` 본문은 제공되지 않았습니다.** 따라서 이 저장소는 WAF Rule 설계 문서와 설치 기록을 제공하며, 완전한 운영 룰셋을 제공하지 않습니다.

## 1. WAF가 필요한 이유

- pfSense: 네트워크·포트 접근 제어
- Suricata Inline IPS: 네트워크 트래픽과 확인 가능한 시그니처 탐지·차단
- ModSecurity: 웹 서버의 HTTP 처리 계층에서 URI, 헤더, Cookie, 요청 본문 등 검사

Suricata가 TLS 복호화 지점 이전에 있다면 HTTPS 요청 본문을 직접 검사할 수 없습니다. WAF는 웹 서버 측의 애플리케이션 데이터를 검사하도록 역할을 분리했습니다.

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

> 위 경로가 Apache 설정에서 실제로 include 되었는지와 Rule ID별 룰 전문은 제공된 문서만으로 확인할 수 없습니다.

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

**중요:** 위는 공유된 문서의 정책 설명과 ID 목록입니다. 실제로 동작하는 `SecRule` 구현 또는 모든 항목의 차단 테스트 성공을 증명하는 자료는 아닙니다. 프로젝트 웹 소스에는 파일 업로드 기능이 확인되지 않으므로 업로드 공격을 실제 서비스에서 검증했다고 표기하지 않습니다.

## 4. 문서

- [Apache2 + ModSecurity 설정 기록](docs/setup.md)
- [Rule ID별 설계 의도](docs/rule-design.md)
- [테스트·검증 범위](docs/validation.md)
- [웹 서버 소스](../web-server/README.md)
