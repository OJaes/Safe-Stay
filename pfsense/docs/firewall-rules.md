# Firewall Rules (Documented Policy)

> **주의:** 아래는 당시 작성한 `pfsense-rule.txt`의 정책 목록을 Markdown으로 정리한 것입니다. 현재 pfSense에서 추출한 활성 Rule Export가 아니며, 통합 테스트 결과를 의미하지 않습니다.

## WAN

| 순서 | Action | Protocol | Source | Destination | Port | 목적 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Pass | UDP | any | WAN address | 1194 | OpenVPN |
| 2 | Pass | TCP | any | Web `192.168.10.10` | 443 | 외부 HTTPS (NAT 연계) |
| 3 | Pass | TCP | any | Web `192.168.10.10` | 80 | 외부 HTTP (NAT 설정 별도 미확인) |
| 4 | Pass | ICMP | Kali IP | WAN address | Echo request | 연결 점검용 임시 정책 |

WAN의 실제 방화벽 매칭은 NAT Port Forward 및 연동 필터 규칙 구성에 따라 달라질 수 있습니다. WAN 규칙에 기록된 Web 목적지는 포워딩 대상 표기입니다.

## VLAN 10 — WEB / DMZ

| 순서 | Action | Protocol | Source | Destination | Port | 목적 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Pass | TCP | `192.168.10.10` | `192.168.20.10` | 3306 | Web → DB Master |
| 2 | Pass | TCP/UDP | `192.168.10.10` | `192.168.10.1` | 53 | DNS |
| 3 | Pass | UDP | `192.168.10.10` | `192.168.10.1` | 123 | NTP |
| 4 | Block + Log | Any | `192.168.10.10` | `192.168.0.0/16` | Any | 미허용 내부망 접근 차단 |
| 5 | Pass | TCP | `192.168.10.10` | Any | 80 | 외부 HTTP |
| 6 | Pass | TCP | `192.168.10.10` | Any | 443 | 외부 HTTPS |

## VLAN 20 — Database

| 순서 | Action | Protocol | Source | Destination | Port | 목적 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Pass | TCP/UDP | `192.168.20.0/24` | `192.168.20.1` | 53 | DNS |
| 2 | Pass | UDP | `192.168.20.0/24` | `192.168.20.1` | 123 | NTP |
| 3 | Block + Log | Any | `192.168.20.0/24` | `192.168.0.0/16` | Any | 미허용 내부망 접근 차단 |
| 4 | Pass | TCP | `192.168.20.0/24` | Any | 80 | 업데이트 HTTP |
| 5 | Pass | TCP | `192.168.20.0/24` | Any | 443 | 업데이트 HTTPS |

## VLAN 30 — Security / Monitoring

| 순서 | Action | Protocol | Source | Destination | Port | 목적 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Pass | TCP/UDP | `192.168.30.0/24` | `192.168.30.1` | 53 | DNS |
| 2 | Pass | UDP | `192.168.30.0/24` | `192.168.30.1` | 123 | NTP |
| 3 | Block + Log | Any | `192.168.30.0/24` | `192.168.0.0/16` | Any | 미허용 내부망 접근 차단 |
| 4 | Pass | TCP | `192.168.30.0/24` | Any | 80 | 패키지/룰 업데이트 |
| 5 | Pass | TCP | `192.168.30.0/24` | Any | 443 | 패키지/룰 업데이트 |

## VLAN 40 — Admin

| 순서 | Action | Protocol | Source | Destination | Port | 목적 |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Pass | TCP | `192.168.40.10` | `192.168.40.1` | 443 | pfSense GUI |
| 2 | Pass | TCP | `192.168.40.10` | `192.168.10.10` | 22 | Web SSH |
| 3 | Pass | TCP | `192.168.40.10` | `192.168.10.10` | 80 | Web HTTP |
| 4 | Pass | TCP | `192.168.40.10` | `192.168.10.10` | 443 | Web HTTPS |
| 5 | Pass | TCP | `192.168.40.10` | `192.168.20.10` | 22 | DB Master SSH |
| 6 | Pass | TCP | `192.168.40.10` | `192.168.20.20` | 22 | DB Slave SSH |
| 7 | Pass | TCP | `192.168.40.10` | `192.168.30.10` | 22 | Suricata SSH |
| 8 | Pass | TCP | `192.168.40.10` | `192.168.30.20` | 22 | Graylog SSH |
| 9 | Pass | TCP | `192.168.40.10` | `192.168.30.20` | 9000 | Graylog Web |
| 10 | Pass | TCP | `192.168.40.10` | `192.168.30.30` | 22 | PMM SSH |
| 11 | Pass | TCP | `192.168.40.10` | `192.168.30.30` | 443 | PMM Web |
| 12 | Pass | ICMP | `192.168.40.10` | `192.168.0.0/16` | Echo request | Ping 점검 |
| 13 | Pass | TCP/UDP | `192.168.40.10` | `192.168.40.1` | 53 | DNS |
| 14 | Pass | UDP | `192.168.40.10` | `192.168.40.1` | 123 | NTP |
| 15 | Block + Log | Any | `192.168.40.10` | `192.168.0.0/16` | Any | 미허용 내부망 접근 차단 |
| 16 | Pass | TCP | `192.168.40.10` | Any | 80 | 외부 HTTP |
| 17 | Pass | TCP | `192.168.40.10` | Any | 443 | 외부 HTTPS |

## 추가 검토 사항

- `Block + Log`는 허용 정책 **아래**에 두어 필요한 트래픽이 먼저 매칭되도록 기록되어 있습니다.
