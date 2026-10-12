# WAF Rule 설계 근거

> `waf-rule.txt`에서 정리한 항목. **룰의 목적과 범위를 문서화한 것이며, 실제 ModSecurity 룰 원문이 아닙니다.**

| ID | 분류 | 필요성 및 기대 효과 |
| --- | --- | --- |
| 2008–2010 | JSON/XML 본문 | HTTPS 복호화 후 구조화된 요청 데이터·파서 오류 검사 |
| 2011–2012 | Cookie SQLi/XSS | URL·POST 외 Cookie 영역의 공격 문자열 검사 |
| 2013–2014 | HTTP Request Smuggling | Content-Length/Transfer-Encoding 해석 충돌 점검 |
| 2015 | CRLF Injection | 인코딩된 CR/LF를 통한 헤더 조작 시도 검사 |
| 2016–2017 | Log4Shell/Shellshock | URI·헤더·본문의 JNDI/Bash 함수 선언 패턴 검사 |
| 2018 | SSRF | 내부 주소·클라우드 메타데이터 접근 유도 검사 |
| 2019–2020 | 민감 파일 | `.env`, `.git`, 백업·설정 파일 접근 시도 검사 |
| 2021–2022 | 웹셸/이중 확장자 | 파일명·업로드 패턴 검토를 위한 설계. 프로젝트 웹 기능에 업로드 경로 확인되지 않음 |
| 2023 | POST Content-Type | 애플리케이션에서 허용할 본문 타입 제한 |
| 2024–2025 | 비밀정보 응답 | 개인키·클라우드 자격정보 노출 패턴 검사 |
| 2026 | Stack Trace | PHP/Java/Python 예외 정보 노출 패턴 검사 |
| 2027–2028 | Cookie 보안 속성 | Secure/HttpOnly 누락 여부 점검 |

## 역할 분담

- **pfSense**: IP/포트 접근 허용 및 차단
- **Suricata**: Inline 네트워크 검사 및 알려진 공격 시그니처
- **ModSecurity**: 복호화된 HTTP 요청·응답과 애플리케이션 정책 검사
- **Graylog**: 수집된 보안 이벤트 분석

## 구현 확인에 필요한 추가 자료

1. `/etc/modsecurity/crs/REQUEST-999-CUSTOM-RULES.conf`의 실제 룰 원문
2. Apache의 해당 룰 파일 include 설정
3. `SecRuleEngine On` 상태와 적용된 모듈 목록
4. 공격별 HTTP 응답·ModSecurity 감사 로그 및 재현 결과

이 자료가 확보되기 전에는 `2008–2028`을 **설계 문서에 기록된 ID**로만 취급합니다.
