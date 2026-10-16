# Network / Interface Configuration

## VirtualBox 인터페이스

| Adapter | 연결 방식 | pfSense NIC | 용도 |
| --- | --- | --- | --- |
| 1 | NAT | `em0` | WAN, DHCP |
| 2 | Internal Network: `HOTEL_TRUNK` | `em1` | 802.1Q VLAN Trunk |
| 3 | Host-Only | `em2` | 임시 관리망 |

## VLAN 및 게이트웨이

| 네트워크 | VLAN | pfSense 인터페이스 | Gateway |
| --- | --- | --- | --- |
| WEB / DMZ | 10 | `em1.10` / `OPT1` | `192.168.10.1/24` |
| DB | 20 | `em1.20` / `OPT2` | `192.168.20.1/24` |
| Security / Monitoring | 30 | `em1.30` / `OPT3` | `192.168.30.1/24` |
| Admin | 40 | `em1.40` / `LAN` | `192.168.40.1/24` |
| 임시 관리망 | - | `em2` / `OPT4` | `192.168.56.2/24` |

## 호스트 주소

| 서버 | 주소 | Gateway |
| --- | --- | --- |
| Web | `192.168.10.10` | `192.168.10.1` |
| DB Master | `192.168.20.10` | `192.168.20.1` |
| DB Slave | `192.168.20.20` | `192.168.20.1` |
| Suricata (관리 IP) | `192.168.30.10` | `192.168.30.1` |
| Graylog | `192.168.30.20` | `192.168.30.1` |
| PMM | `192.168.30.30` | `192.168.30.1` |
| Admin PC | `192.168.40.10` | `192.168.40.1` |
| OpenVPN Tunnel | `10.10.10.0/24` | - |

## 설계 의도

1. WEB, DB, Security, Admin 네트워크를 분리합니다.
2. 서로 다른 VLAN 간 통신은 pfSense의 허용 규칙을 통해 제한합니다.
3. Admin 단말에는 필요한 관리 포트만 명시적으로 허용합니다.
4. 임시 관리망은 구축 기록에 포함되어 있으나 최종 제거 여부는 확인되지 않았습니다.

> 같은 VLAN 내 호스트 간 통신은 pfSense의 VLAN 간 방화벽 규칙을 통과하지 않을 수 있습니다. Suricata Inline 경로는 별도 IPS 구축 문서를 참조해야 합니다.
