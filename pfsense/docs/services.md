# NAT / OpenVPN / Graylog Syslog

## 1. NAT Port Forward

| 항목 | 기록된 설정 |
| --- | --- |
| Interface | WAN |
| Protocol | TCP/IPv4 |
| Destination | WAN address |
| Destination Port | 443 |
| Redirect Target IP | `192.168.10.10` |
| Redirect Target Port | 443 |
| Description | `WAN_HTTPS_to_HOTEL_WEB` |

기록된 통신 경로: 외부 사용자 → pfSense WAN `443/TCP` → Web `192.168.10.10:443`.

**주의:** 방화벽 룰 목록에는 `80/TCP` 허용도 있으나 `80/TCP` Port Forward의 구체적인 설정 기록은 제공되지 않았습니다.

## 2. OpenVPN

| 항목 | 기록된 설정 |
| --- | --- |
| 모드 | Remote Access / Local User Access |
| 이름 | `SafeStay_Remote_Access_VPN` |
| Protocol / Interface | UDP IPv4 / WAN |
| Port | 1194 |
| Tunnel Network | `10.10.10.0/24` |
| TLS Authentication | 활성화 |
| DH Parameters | 2048 bit |
| Redirect Gateway | 비활성화 |
| Inter-Client Communication | 비활성화 |
| 서버 이름 | `vpn.safestay.local` |

프로젝트 문서의 VPN 대상 네트워크는 WEB/DB/Security/Admin VLAN입니다. 실제 클라이언트의 접근 가능 범위는 OpenVPN 인터페이스 규칙 및 라우팅과 함께 검증해야 합니다.

CA 이름 및 서버/사용자 인증서 명칭은 구축 기록에 있으나, 인증서·개인키 파일은 이 저장소에 포함하지 않습니다.

## 3. Graylog Remote Syslog

pfSense 메뉴: `Status → System Logs → Settings → Remote Logging Options`

| 항목 | 기록된 설정 |
| --- | --- |
| Remote Logging | 활성화 |
| Source Address | Default (any) |
| IP Protocol | IPv4 |
| Destination | `192.168.30.20:514` |
| Transport | UDP 514 |
| Syslog Contents | Everything |

의도한 흐름: pfSense 시스템/방화벽/인증/VPN 로그 → Graylog Syslog Input

## 4. 임시 관리 인터페이스

- Host-Only `em2` / `OPT4`: `192.168.56.2/24`
