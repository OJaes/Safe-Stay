# 🛡️ SafeStay

> **호텔 예약 시스템을 대상으로 네트워크 분리, 침입 탐지·차단, 웹 애플리케이션 방어, 중앙 로그 관제를 구현한 통합 보안 인프라 프로젝트**

SafeStay는 실제 서비스 환경을 가정하여 **pfSense → Suricata IPS → ModSecurity WAF → Graylog**로 이어지는 다계층 보안 체계를 구축한 프로젝트입니다.

단순히 보안 솔루션을 설치하는 것에서 끝나지 않고, 각 솔루션의 역할과 한계를 분석하여 네트워크 계층부터 애플리케이션 계층까지 방어 영역을 분리하고 실제 공격 시나리오를 통해 정책을 검증했습니다.

---

## 📌 Project Overview

| 항목 | 내용 |
| --- | --- |
| 프로젝트 | SafeStay |
| 주제 | 호텔 예약 시스템 통합 보안 인프라 구축 |
| 기간 | 2026.07.23 ~ 2026.08.05 |
| 인원 | 5명 |
| 담당 | Suricata, Graylog, pfSense, WAF Rule |
| 가상화 | Oracle VirtualBox |

### 담당 업무

- Suricata IDS/IPS 서버 구축
- AF_PACKET 기반 Inline IPS 구성
- Suricata Custom Rule 작성
- Graylog 중앙 로그 서버 구축
- Filebeat 기반 Suricata 로그 연동
- pfSense VLAN / Firewall / NAT 구성
- OpenVPN 및 Remote Syslog 설정
- ModSecurity WAF Rule 설계 및 적용
- 공격 시나리오 기반 탐지·차단 검증

---

## 🏗️ Architecture

```text
                        Internet
                           │
                           ▼
                       [ pfSense ]
                 Firewall / VLAN / VPN
                           │
                           ▼
                 [ Suricata Inline IPS ]
                           │
                           ▼
                    [ ModSecurity ]
                         WAF
                           │
                           ▼
                     [ Web Server ]
                           │
                      TCP 3306 only
                           │
                           ▼
                    [ DB Master ]
                           │
                           ▼
                     [ DB Slave ]


                 Security Monitoring

     Suricata ── EVE JSON ── Filebeat ──┐
                                        │
     pfSense ─────── Syslog ────────────┼──▶ [ Graylog ]
                                        │
                                  Search / Analysis
```

### Network Segmentation

| Zone | Network | Role |
| --- | --- | --- |
| WEB / DMZ | `192.168.10.0/24` | Web Service |
| Database | `192.168.20.0/24` | DB Master / Slave |
| Security | `192.168.30.0/24` | Suricata / Graylog / PMM |
| Admin | `192.168.40.0/24` | Administrator |
| VPN | `10.10.10.0/24` | Remote Administration |

---

## 🛠️ Tech Stack

| Category | Technology |
| --- | --- |
| Firewall / Network | pfSense, VLAN, OpenVPN |
| IDS / IPS | Suricata, AF_PACKET |
| WAF | ModSecurity |
| Log Collection | Filebeat, Syslog |
| SIEM / Monitoring | Graylog |
| Log Storage | Graylog Data Node / OpenSearch |
| Metadata | MongoDB |
| OS / Tools | Linux, tcpdump, jq, ethtool, systemd |

---

# 🔐 Security Implementation

## 1. pfSense — 네트워크 접근 제어

WEB, DB, Security, Admin 영역을 VLAN으로 분리하고 **필요한 통신만 허용하는 최소 권한 정책**을 적용했습니다.

대표적으로 Web Server는 DB Master의 MySQL 서비스만 사용할 수 있도록 제한했습니다.

```text
Web Server
192.168.10.10
      │
      │ TCP 3306
      ▼
DB Master
192.168.20.10

그 외 내부망 접근
→ Block + Log
```

또한 외부에는 HTTP/HTTPS와 OpenVPN 등 필요한 서비스만 노출하고, Graylog·Suricata·PMM 등의 관리 서비스는 Admin 영역에서만 접근할 수 있도록 제한했습니다.

pfSense의 방화벽·시스템·VPN 로그는 Syslog를 통해 Graylog로 전달하도록 구성했습니다.

---

## 2. Suricata — Inline IPS

일반적인 IDS처럼 트래픽을 탐지만 하는 것이 아니라 공격 패킷을 실제로 차단하기 위해 **AF_PACKET 기반 Inline IPS**로 구성했습니다.

```text
enp0s8
IP 없음
   │
   ▼
Suricata
   │
   ▼
enp0s9
IP 없음

enp0s10
192.168.30.10
→ 관리 / Graylog 로그 전송
```

Inline 인터페이스에는 IP를 할당하지 않고 패킷 전달만 담당하도록 역할을 분리했습니다.

```yaml
af-packet:
  - interface: enp0s8
    cluster-type: cluster_flow
    copy-mode: ips
    copy-iface: enp0s9

  - interface: enp0s9
    cluster-type: cluster_flow
    copy-mode: ips
    copy-iface: enp0s8
```

### Custom IPS Rules

서비스 환경을 기준으로 `local.rules`를 직접 구성했습니다.

주요 탐지·차단 대상은 다음과 같습니다.

- Directory Traversal
- Sensitive File Access
- SQL Injection
- XSS
- OS Command Injection
- Log4Shell / Shellshock
- XXE / SSRF
- CRLF / Null Byte Injection
- WebShell Upload
- Security Scanner User-Agent
- TCP SYN / NULL / XMAS / FIN Scan
- SSH 반복 접속
- ICMP 이상 트래픽
- TLS 1.0 / 1.1 및 인증서 이상

Rule 적용 전에는 `suricata -T`를 이용하여 설정 및 Rule 문법을 검증했습니다.

---

## 3. ModSecurity — Web Application Firewall

Suricata만으로 처리하기 어려운 **HTTPS 내부의 Application Layer 공격**을 보완하기 위해 ModSecurity WAF Rule을 구성했습니다.

주요 정책은 다음과 같습니다.

- JSON / XML Request 검사
- Cookie 기반 SQL Injection / XSS
- HTTP Request Smuggling
- CRLF Injection
- Log4Shell / Shellshock
- SSRF
- 민감 파일 및 Backup File 접근
- WebShell / Double Extension Upload
- POST Content-Type 검증
- Private Key / Cloud Access Key 노출 탐지
- Stack Trace 노출 탐지
- Secure / HttpOnly Cookie 점검

---

## 4. Graylog — 중앙 로그 관제

Suricata에서 발생한 보안 이벤트를 중앙에서 분석하기 위해 Graylog를 구축했습니다.

```text
Suricata
   │
   ▼
eve.json
   │
   ▼
Filebeat
   │
   │ TCP 5044
   ▼
Graylog Beats Input
   │
   ▼
Suricata Stream
```

Suricata의 `eve.json`을 Filebeat가 NDJSON 형태로 읽어 Graylog로 전달하도록 구성했습니다.

통계성 `stats` 이벤트는 Filebeat 단계에서 제외하여 불필요한 로그 적재를 줄였습니다.

또한 Graylog의 Beats 포트 `5044/TCP`를 전체 네트워크에 공개하지 않고 **Suricata 서버 `192.168.30.10`에서만 접근 가능**하도록 제한했습니다.

---

# 🔧 Troubleshooting

## 1. IDS를 실제 차단 가능한 IPS로 전환

### Problem

초기 IDS 구조에서는 공격을 탐지하더라도 악성 패킷 자체는 Web Server까지 전달될 수 있었습니다.

프로젝트 목표는 단순 탐지가 아닌 **공격 트래픽의 실시간 차단**이었습니다.

### Analysis

Suricata가 패킷을 차단하려면 대상 트래픽이 Suricata를 우회하지 않고 반드시 통과해야 했습니다.

관리 인터페이스에서 트래픽을 수집하는 방식만으로는 Inline 차단이 불가능했습니다.

### Solution

패킷 전달 전용 인터페이스 두 개를 별도로 구성하고 IP를 제거한 뒤 AF_PACKET의 `copy-mode: ips`를 적용했습니다.

관리 및 로그 전송은 별도의 인터페이스로 분리했습니다.

```text
Traffic Interface
      │
      ▼
   Suricata
      │
      ▼
Traffic Interface

Management Interface
      │
      └──▶ Graylog
```

### Result

Custom Rule에 매칭된 공격 패킷에 `drop` Action을 적용할 수 있는 **Inline IPS 구조를 구현**했습니다.

---

## 2. HTTPS 환경에서 Suricata가 웹 공격 Payload를 확인하기 어려운 문제

### Problem

Suricata Custom Rule을 통해 SQL Injection, XSS 등의 공격도 탐지하도록 구성했지만 HTTPS가 적용되면 네트워크 구간의 HTTP Payload가 암호화됩니다.

따라서 Suricata만으로는 Cookie, Request Body, JSON/XML 등 애플리케이션 데이터를 충분히 검사하기 어려웠습니다.

### Solution

보안 솔루션의 역할을 계층별로 분리했습니다.

```text
pfSense
→ IP / Port 접근 제어

Suricata
→ Scan / Flood / Network Signature

ModSecurity
→ 복호화된 HTTP Request / Response 검사

Graylog
→ Security Event 수집 및 분석
```

Suricata가 네트워크 기반 공격을 담당하고, 웹 서버에서 확인할 수 있는 복호화된 HTTP 데이터는 ModSecurity가 검사하도록 구성했습니다.

### Result

하나의 보안 장비에 모든 탐지를 의존하지 않는 **Defense in Depth 구조**를 구현했습니다.

---

## 3. Graylog Data Node PKIX 인증서 오류

### Problem

Graylog Server와 Data Node 연동 과정에서 다음 오류가 발생했습니다.

```text
PKIX path building failed
certificate chain validation failed
```

### Analysis

Data Node/OpenSearch는 HTTPS를 사용하고 있었지만 Graylog Server의 Java 환경이 Data Node의 CA 인증서를 신뢰하지 않아 TLS 인증서 검증이 실패했습니다.

### Solution

TLS 검증을 비활성화하는 대신 정상적인 인증서 신뢰 관계를 구성했습니다.

```text
Data Node CA
     │
     ▼
CA Certificate 추출
     │
     ▼
Java Truststore 생성
     │
     ▼
CA Certificate 등록
     │
     ▼
Graylog JVM에 Truststore 적용
```

systemd 환경 설정을 통해 Graylog Server JVM이 생성한 Truststore를 사용하도록 구성했습니다.

### Result

TLS 인증서 검증을 유지하면서 **Graylog Server와 Data Node를 정상 연동**했습니다.

이를 통해 내부 서비스 간 HTTPS에서도 CA Chain과 Java Truststore 관리가 필요하다는 점을 확인했습니다.

---

## 4. 보안 이벤트가 Suricata 서버에만 남는 문제

### Problem

공격 이벤트가 Suricata의 `eve.json`에만 저장되면 운영자가 여러 보안 이벤트를 중앙에서 확인하기 어렵습니다.

### Solution

다음 로그 파이프라인을 구성했습니다.

```text
Suricata
→ EVE JSON
→ Filebeat
→ TCP 5044
→ Graylog Beats Input
→ Suricata Stream
```

문제가 발생했을 때는 다음 단계를 각각 분리하여 점검했습니다.

```text
Suricata 동작 여부
        ↓
EVE JSON 생성 여부
        ↓
JSON Parsing 여부
        ↓
Filebeat Config
        ↓
Filebeat Output
        ↓
TCP 5044 연결
        ↓
Graylog Input
```

### Result

공격 발생 시 Graylog에서 공격 시간, Source/Destination IP, Signature, Event Type 등을 중앙에서 확인할 수 있는 환경을 구축했습니다.

---

## 5. 서버 침해 시 내부망 확산 가능성

### Problem

Web, DB, 보안 관리 서버가 동일한 네트워크에서 자유롭게 통신하면 하나의 서버가 침해됐을 때 다른 서버로 공격이 확산될 가능성이 있습니다.

### Solution

pfSense에서 서비스를 역할별 VLAN으로 분리하고 필요한 통신만 먼저 허용한 뒤 나머지 내부 통신을 `Block + Log` 처리했습니다.

대표적으로 Web Server는 DB Master의 `3306/TCP`만 접근할 수 있도록 제한했습니다.

관리 서비스 접근 역시 Admin VLAN으로 제한했습니다.

### Result

서비스에 필요한 통신은 유지하면서 불필요한 내부 접근을 차단하여 **Lateral Movement 가능 범위를 축소**했습니다.

---

# 🧪 Security Validation

구축 후 실제 공격 시나리오를 통해 보안 정책의 동작을 검증했습니다.

### OS Command Injection

취약한 기능을 대상으로 OS Command Injection 시나리오를 구성했습니다.

ModSecurity가 활성화된 상태에서는 공격 요청이 `403 Forbidden`으로 차단되는 것을 확인했습니다.

반대로 WAF를 `DetectionOnly` 상태로 변경했을 때는 취약한 백엔드 기능이 그대로 노출될 수 있음을 확인하여 **WAF 차단 정책의 필요성**을 검증했습니다.

---

### Slowloris

불완전한 HTTP 요청을 장시간 유지하여 Web Server의 Connection Resource를 고갈시키는 Slowloris 시나리오를 수행했습니다.

공격 시 비정상적인 연결 증가와 서비스 지연을 확인하고 다음과 같이 대응했습니다.

- Web Server Request Timeout 정책 적용
- Suricata에서 단시간 다중 연결 시도에 대한 보조 탐지·차단 Rule 적용
- 로그를 이용한 공격 발생 여부 확인

이를 통해 네트워크 장비의 Rule뿐 아니라 **서버 자체의 Timeout 정책을 함께 적용하는 다계층 대응의 필요성**을 확인했습니다.

---

# ✅ Result

프로젝트를 통해 다음 흐름을 하나의 보안 인프라로 구성했습니다.

```text
Network Segmentation
        ↓
Access Control
        ↓
Intrusion Detection / Prevention
        ↓
Web Application Protection
        ↓
Centralized Log Monitoring
```

특히 구축 과정에서 발생한 문제를 단순 우회하지 않고 원인을 분석하여 다음과 같이 해결했습니다.

| 문제 | 해결 |
| --- | --- |
| IDS가 공격을 탐지만 함 | AF_PACKET Inline IPS 구성 |
| HTTPS Payload 검사 한계 | Suricata / ModSecurity 역할 분리 |
| Graylog PKIX 오류 | CA 추출 및 Java Truststore 구성 |
| 분산된 보안 로그 | Filebeat → Graylog Pipeline 구축 |
| 내부망 자유로운 접근 | VLAN + Default Deny 정책 적용 |

---

# 💡 What I Learned

이번 프로젝트를 통해 보안 솔루션 자체보다 **각 솔루션을 어떤 위치에 배치하고 어떤 역할을 맡길 것인지가 중요하다**는 점을 경험했습니다.

Suricata가 모든 공격을 처리하도록 구성하기보다 네트워크 계층은 pfSense와 Suricata, 애플리케이션 계층은 ModSecurity, 이벤트 분석은 Graylog가 담당하도록 역할을 분리했습니다.

또한 공격을 차단하는 것에서 끝나는 것이 아니라 **탐지 → 차단 → 로그 수집 → 분석**이 하나의 흐름으로 연결되어야 실제 운영 가능한 보안 체계가 된다는 점을 배웠습니다.

---

## 📂 Repository

```text
SafeStay/
├── README.md
│
├── suricata/
│   ├── README.md
│   ├── suricata.yaml
│   ├── local.rules
│   └── filebeat.yml
│
├── graylog/
│   ├── README.md
│   └── setup.md
│
├── pfsense/
│   ├── README.md
│   └── firewall-rules.md
│
├── waf/
│   ├── README.md
│   └── custom-rules.md
│
└── pentest/
    ├── os-command-injection.md
    └── slowloris.md
```

> 세부 설치 과정과 전체 Rule은 각 디렉터리의 문서로 분리하고, 메인 README에서는 프로젝트 구조와 문제 해결 과정만 설명합니다.

---

## 👤 My Role

**Security Infrastructure / Network Security**

`Suricata` · `Graylog` · `pfSense` · `ModSecurity` · `Filebeat`

- Inline IDS/IPS 구축 및 Rule 설계
- 중앙 로그 수집 환경 구축
- VLAN / Firewall / VPN 정책 구성
- WAF 보안 정책 설계
- 공격 시나리오 기반 보안 정책 검증
