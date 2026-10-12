<?php
session_start();

// 1. 관리자 권한 체크 (비로그인 및 일반 사용자 접근 차단)
$user_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/login.php';</script>";
    exit;
}

// 2. DB 연결
require_once '../db.php';

// 3. DB 통계 수치 및 최근 예약 조회
$today_count = 0;
$total_count = 0;
$recentBookings = [];

try {
    // 오늘의 예약 건수
    $stmtToday = $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()");
    $today_count = $stmtToday->fetchColumn();

    // 전체 예약 건수
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_count = $stmtTotal->fetchColumn();

    // 최근 예약 목록 10건 (사용자 정보 JOIN)
    $stmtBookings = $pdo->query("
        SELECT b.*, u.username
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.id DESC
        LIMIT 10
    ");
    $recentBookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Admin Dashboard DB Error: " . $e->getMessage());
}

// 4. WAF 차단 로그 읽기
$wafLogPath = '/var/log/safestay_waf.log';
$wafLogs = [];
if (file_exists($wafLogPath) && is_readable($wafLogPath)) {
    $fileLines = file($wafLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($fileLines !== false) {
        $wafLogs = array_reverse(array_slice($fileLines, -10)); // 최근 10개 로그
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeStay 어드민 대시보드</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }

        /* 네비게이션 바 */
        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.3rem; font-weight: bold; }
        .logo a { color: white; text-decoration: none; }
        .nav-links { display: flex; align-items: center; }
        nav { display: flex; align-items: center; gap: 12px; }
        nav a { color: #bdc3c7; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        nav a:hover { color: white; }

        /* 모니터링 링크 전용 스타일 */
        .nav-tool-btn { background: #34495e; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; color: #ecf0f1 !important; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .nav-tool-btn:hover { background: #2980b9; color: white !important; }

        .user-info { font-size: 0.85rem; color: #f39c12; margin-right: 15px; }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        /* 대시보드 요약 카드 */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e1e8ed; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .stat-card h3 { font-size: 0.85rem; color: #7f8c8d; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 1.8rem; font-weight: bold; color: #2c3e50; }

        /* 패널 공통 스타일 */
        .panel { background: white; border-radius: 8px; border: 1px solid #e1e8ed; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .panel-title { font-size: 1.2rem; font-weight: bold; color: #2c3e50; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }

        /* 모니터링 바로가기 카드 영역 */
        .tools-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .tool-card { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: inherit; transition: border-color 0.2s, box-shadow 0.2s; }
        .tool-card:hover { border-color: #3182ce; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .tool-info h4 { font-size: 1rem; color: #2d3748; margin-bottom: 2px; }
        .tool-info p { font-size: 0.8rem; color: #718096; }
        .btn-go { background: #3182ce; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }

        /* 테이블 스타일 */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; }
        th { background-color: #f8fafc; color: #4a5568; font-weight: 600; }
        tr:hover { background-color: #f8fafc; }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-success { background: #e6fffa; color: #234e52; }

        .log-box { background: #1e1e1e; color: #00ff66; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 0.85rem; overflow-x: auto; max-height: 250px; line-height: 1.5; }
    </style>
</head>
<body>

    <header>
        <div class="logo"><a href="/admin/">🛡️  SafeStay Admin</a></div>
        <div class="nav-links">
            <span class="user-info">👑 <?= htmlspecialchars($_SESSION['name'] ?? '관리자') ?>님 접속 중
</span>
            <nav>
                <!-- 상단 서브 링크 추가 -->
                <a href="/admin/users.php" class="nav-tool-btn">👥 고객 관리</a>

                <a href="/index.php" style="margin-left: 10px;">메인 사이트</a>
                <a href="/logout.php" style="color: #e74c3c;">로그아웃</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- 요약 통계 -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>오늘의 예약</h3>
                <div class="value"><?= number_format($today_count) ?> 건</div>
            </div>
            <div class="stat-card">
                <h3>총 누적 예약</h3>
                <div class="value"><?= number_format($total_count) ?> 건</div>
            </div>
            <div class="stat-card">
                <h3>가동 중인 객실</h3>
                <div class="value">85 %</div>
            </div>
            <div class="stat-card">
                <h3>WAF 차단 건수</h3>
                <div class="value" style="color: #e53e3e;"><?= count($wafLogs) ?> 건</div>
            </div>
        </div>

        <!-- 🚀 모니터링 도구 바로가기 패널 -->
        <div class="panel">
            <div class="panel-title">🖥️  시스템 & 모니터링 도구</div>
            <div class="tools-grid">
                <!-- GoAccess 링크 (새 탭 열기) -->
                <a href="/stats.html" target="_blank" class="tool-card">
                    <div class="tool-info">
                        <h4>📊 GoAccess 대시보드</h4>
                        <p>실시간 웹서버 트래픽 및 웹 로그 분석</p>
                    </div>
                    <span class="btn-go">열기 ↗</span>
                </a>

                <!-- PMM 링크 (포트/주소에 맞춰 수정 가능) -->
                <a href="http://localhost:8080" target="_blank" class="tool-card">
                    <div class="tool-info">
                        <h4>🐬 PMM (Percona Monitoring)</h4>
                        <p>MySQL/MariaDB 데이터베이스 성능 모니터링</p>
                    </div>
                    <span class="btn-go" style="background: #2b6cb0;">열기 ↗</span>
                </a>
            </div>
        </div>

        <!-- 예약 관리 -->
        <div class="panel">
            <div class="panel-title">최근 예약 현황</div>
            <table>
                <thead>
                    <tr>
                        <th>예약 번호</th>
                        <th>고객명 (예약자)</th>
                        <th>객실 타입</th>
                        <th>체크인 / 체크아웃</th>
                        <th>결제 금액</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #a0aec0; padding: 30px;">
                                등록된 예약 내역이 없습니다.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td>#RES-<?= str_pad($b['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($b['guest_name']) ?></strong>
                                    <span style="font-size: 0.8rem; color: #718096;">(<?= htmlspecialchars($b['username'] ?? '회원') ?>)</span>
                                </td>
                                <td><?= htmlspecialchars($b['room_name']) ?></td>
                                <td><?= htmlspecialchars($b['check_in']) ?> ~ <?= htmlspecialchars($b['check_out']) ?></td>
                                <td>₩ <?= number_format($b['total_price']) ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= htmlspecialchars($b['status'] ?? '확정') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- WAF 로그 모니터링 -->
        <div class="panel">
            <div class="panel-title">🛡️  최근 WAF 차단 모니터링 (보안 로그)</div>
            <div class="log-box">
                <?php if (empty($wafLogs)): ?>
                    차단된 악성 요청 내역이 없습니다.
                <?php else: ?>
                    <?php foreach ($wafLogs as $log): ?>
                        <?= htmlspecialchars($log) ?><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
