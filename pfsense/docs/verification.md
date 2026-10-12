# Verification Status / Evidence Limitations

## 문서의 근거

- 프로젝트 중 작성한 `pfsense.txt` — VM 사양, 인터페이스, VLAN, NAT, OpenVPN, Syslog, 구축 결과
- 프로젝트 중 작성한 `pfsense-rule.txt` — WAN 및 VLAN 10/20/30/40 방화벽 규칙 표

## 현재 상태

- **pfSense VM 접속:** 불가능 (원인은 이 자료만으로 확인되지 않음)
- **실제 `config.xml` 제공:** 없음
- **pfSense GUI Rule Export:** 없음
- **현재 정책 활성 상태 확인:** 불가능
- **실제 통신/차단 테스트 재실행:** 불가능

원본 구축 기록에는 `Diagnostics → Backup & Restore`를 통해 XML 백업을 생성했다는 설명이 있지만, 현재 이 저장소에 제공된 파일 중 실제 백업 XML은 없습니다. 따라서 임의로 XML을 생성하지 않았습니다.

## 통합 후 확인 대상으로 남아 있는 항목

| 점검 항목 | 현재 문서화 상태 |
| --- | --- |
| `HOTEL_TRUNK`와 VLAN 인터페이스 매핑 | 구축 기록 존재 / 재검증 불가 |
| 외부 HTTPS → WEB Port Forward | 설정 기록 존재 / 재검증 불가 |
| WEB → DB Master `3306/TCP` | 허용 규칙 기록 존재 / 재검증 불가 |
| VLAN 간 허용/차단 | 규칙 목록 존재 / 재검증 불가 |
| Graylog UDP 514 Input과 로그 수신 | 전송 설정 기록 존재 / 수신 성공 확인 불가 |
| OpenVPN 클라이언트 접속 및 터널 IP | 서버 설정 기록 존재 / 접속 성공 확인 불가 |
| pfSense 기본 암호 변경 / Web GUI HTTPS | 원본의 후속 확인 항목 / 완료 여부 미확인 |
| 임시 관리망 및 Kali ICMP 테스트 규칙 제거 | 완료 여부 미확인 |

## 공개 원칙

1. **구축 기록**과 **현재 실행 검증 결과**를 혼동하지 않습니다.
2. 실물 장비에서 추출하지 않은 `config.xml`, 스크린샷, 테스트 결과를 만들어 넣지 않습니다.
3. 비밀번호·VPN 개인키·CA 개인키·인증서 비밀정보를 GitHub에 올리지 않습니다.
