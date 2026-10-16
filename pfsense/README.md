# pfSense | SafeStay Network Security

> SafeStay 호텔 예약 시스템의 네트워크 분리, 방화벽 접근 제어, VPN 및 원격 로그 전송 구성 기록

## 1. 담당 범위

- WEB / DB / Security / Admin 영역의 VLAN 및 게이트웨이 구성
- VLAN 간 최소 권한 방화벽 정책 작성
- 외부 HTTPS 서비스용 NAT Port Forward
- OpenVPN 원격 관리 접근 구성
- pfSense Syslog를 Graylog로 전달하도록 설정

## 2. 구축 환경

| 항목 | 내용 |
| --- | --- |
| 가상화 | Oracle VirtualBox |
| 방화벽 | pfSense CE 2.7.2 |
| VM | 2 vCPU / 2 GB RAM / 약 16 GB Disk |
| WAN | `em0` (NAT / DHCP) |
| VLAN Trunk | `em1` (Internal Network `HOTEL_TRUNK`) |
| 임시 관리망 | `em2` (Host-Only, `192.168.56.2`) |

## 3. 네트워크 분리

| Zone | VLAN | Subnet | Gateway | 용도 |
| --- | --- | --- | --- | --- |
| WEB / DMZ | 10 | `192.168.10.0/24` | `192.168.10.1` | 호텔 웹 서비스 |
| Database | 20 | `192.168.20.0/24` | `192.168.20.1` | DB Master / Slave |
| Security | 30 | `192.168.30.0/24` | `192.168.30.1` | Suricata / Graylog / PMM |
| Admin | 40 | `192.168.40.0/24` | `192.168.40.1` | 관리자 단말 |

## 4. 핵심 보안 정책

| 정책 | 의도 |
| --- | --- |
| WEB (`192.168.10.10`) → DB Master (`192.168.20.10:3306/TCP`) | 서비스에 필요한 DB 연결만 허용 |
| VLAN별 내부망 차단 + 로깅 | 불필요한 수평 이동 경로 제한 |
| Admin (`192.168.40.10`) → 관리 서비스별 지정 포트 | 관리자 접근 최소화 |
| WAN → 웹 서버 `443/TCP` NAT | 외부 HTTPS 서비스 연결 |
| WAN → OpenVPN `1194/UDP` | 원격 관리 연결 |
| pfSense → Graylog `192.168.30.20:514/UDP` | 시스템·방화벽 이벤트 전송 |

> 방화벽 룰 목록에는 WAN `80/TCP` 허용 항목도 있지만, 별도 NAT 구성 기록은 `443/TCP`에 대해서만 존재합니다. HTTP 포워딩이 실제로 동작했다고 단정하지 않습니다.

## 5. 관련 문서

- [네트워크 및 인터페이스 구성](docs/network.md)
- [WAN / VLAN 방화벽 규칙](docs/firewall-rules.md)
- [NAT / OpenVPN / Graylog Syslog](docs/services.md)
- [검증 범위 및 미확인 항목](docs/verification.md)

## 6. 확인된 기록과 제한사항

- 원본 구축 기록에는 pfSense 방화벽·VLAN·VPN·Syslog 설정 내용과 백업 수행 사실이 기재되어 있습니다.
- 현재는 VM 접속이 불가능하며, 실제 `config.xml` 백업 파일은 이 저장소 작성에 제공되지 않았습니다.
- 규칙 문서는 **당시 정리한 정책 목록**이며, 현재 장비의 활성 규칙을 직접 추출한 결과가 아닙니다.
- 전체 VM 통합 후의 통신 및 차단 테스트는 원본 문서에서도 후속 확인 항목으로 남아 있습니다.

## 7. 공개 저장소 주의사항

관리자 암호, OpenVPN 사용자 인증서·개인키, CA 개인키, 실제 설정 백업 XML, 계정 비밀값은 포함하지 않습니다. 사설 IP는 프로젝트 테스트 환경의 설계 정보입니다.
