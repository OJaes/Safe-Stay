<?php
session_start();

// 1. 관리자 권한 체크
$user_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/login.php';</script>";
    exit;
}

// 2. DB 연결
require_once '../db.php'; 

// 3. POST 요청 처리 (권한 변경 및 삭제)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // [권한 변경]
    if ($_POST['action'] === 'update_role') {
        $target_user_id = (int)($_POST['user_id'] ?? 0);
        $new_role = $_POST['role'] ?? 'user';

        if ($target_user_id === (int)$_SESSION['user_id']) {
            echo "<script>alert('자기 자신의 권한은 변경할 수 없습니다.'); location.href='/admin/users.php';</script>";
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $target_user_id]);
            echo "<script>alert('회원 권한이 변경되었습니다.'); location.href='/admin/users.php';</script>";
            exit;
        } catch (PDOException $e) {
            $msg = addslashes($e->getMessage());
            echo "<script>alert('DB 오류: {$msg}'); location.href='/admin/users.php';</script>";
            exit;
        }
    }

    // [회원 삭제]
    if ($_POST['action'] === 'delete_user') {
        $target_user_id = (int)($_POST['user_id'] ?? 0);

        if ($target_user_id === (int)$_SESSION['user_id']) {
            echo "<script>alert('자기 자신은 삭제할 수 없습니다.'); location.href='/admin/users.php';</script>";
            exit;
        }

        try {
            // 1) 연관된 예약 내역 먼저 삭제 (FK 에러 방지)
            $stmtBooking = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
            $stmtBooking->execute([$target_user_id]);

            // 2) 회원 정보 삭제
            $stmtUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtUser->execute([$target_user_id]);

            echo "<script>alert('회원 및 연관 예약 내역이 삭제되었습니다.'); location.href='/admin/users.php';</script>";
            exit;

        } catch (PDOException $e) {
            $msg = addslashes($e->getMessage());
            echo "<script>alert('삭제 중 DB 오류 발생: {$msg}'); location.href='/admin/users.php';</script>";
            exit;
        }
    }
}

// 4. 검색 및 회원 목록 조회
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE u.username LIKE ? OR u.name LIKE ? OR u.email LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$users = [];
try {
    $sql = "
        SELECT u.*, 
               COUNT(b.id) AS total_bookings,
               COALESCE(SUM(b.total_price), 0) AS total_spent
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id
        {$whereClause}
        GROUP BY u.id
        ORDER BY u.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 쿼리 에러 시 화면에 에러 표시
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>고객 정보 관리 - SafeStay Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }

        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
        .logo a { color: white; text-decoration: none; font-size: 1.3rem; font-weight: bold; }
        nav { display: flex; align-items: center; gap: 12px; }
        nav a { color: #bdc3c7; text-decoration: none; font-size: 0.9rem; }
        nav a:hover, nav a.active { color: white; }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        .panel { background: white; border-radius: 8px; border: 1px solid #e1e8ed; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .panel-title { font-size: 1.2rem; font-weight: bold; color: #2c3e50; }

        .search-form { display: flex; gap: 10px; }
        .search-input { padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 0.9rem; width: 250px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: bold; }
        .btn-primary { background: #3182ce; color: white; }
        .btn-danger { background: #e53e3e; color: white; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; }
        th { background-color: #f8fafc; color: #4a5568; font-weight: 600; }
        tr:hover { background-color: #f8fafc; }

        select.role-select { padding: 4px 8px; border-radius: 4px; border: 1px solid #cbd5e0; font-size: 0.85rem; }
        .error-box { background: #fff5f5; color: #c53030; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #feb2b2; }
    </style>
</head>
<body>

    <header>
        <div class="logo"><a href="/admin/">🛡️ SafeStay Admin</a></div>
        <nav>
            <a href="/admin/">대시보드</a>
            <a href="/admin/users.php" class="active" style="color:white; font-weight:bold;">고객 관리</a>
            <a href="/index.php" style="margin-left: 15px;">메인 사이트</a>
            <a href="/logout.php" style="color: #e74c3c;">로그아웃</a>
        </nav>
    </header>

    <div class="container">

        <?php if (!empty($db_error)): ?>
            <div class="error-box">
                ⚠️ DB 조회 중 오류가 발생했습니다: <?= htmlspecialchars($db_error) ?>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">👥 회원 정보 관리 (총 <?= count($users) ?>명)</div>

                <form class="search-form" method="GET" action="/admin/users.php">
                    <input type="text" name="search" class="search-input" placeholder="아이디, 이름, 이메일 검색" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <?php if(!empty($search)): ?>
                        <a href="/admin/users.php" class="btn" style="background:#e2e8f0; color:#4a5568; text-decoration:none;">초기화</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>아이디</th>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>총 예약 건수</th>
                        <th>총 이용 금액</th>
                        <th>권한</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #a0aec0; padding: 30px;">
                                등록된 회원이 없거나 일치하는 검색 결과가 없습니다.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($u['name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td><?= number_format((int)($u['total_bookings'] ?? 0)) ?> 건</td>
                                <td>₩ <?= number_format((int)($u['total_spent'] ?? 0)) ?></td>
                                <td>
                                    <form method="POST" action="/admin/users.php" style="display:inline-block;">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <select name="role" class="role-select" onchange="this.form.submit()">
                                            <option value="user" <?= ($u['role'] ?? '') === 'user' ? 'selected' : '' ?>>일반 회원</option>
                                            <option value="admin" <?= ($u['role'] ?? '') === 'admin' ? 'selected' : '' ?>>관리자</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                        <form method="POST" action="/admin/users.php" style="display:inline-block;" onsubmit="return confirm('정말 이 회원을 삭제하시겠습니까?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 3px 8px; font-size: 0.75rem;">삭제</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size:0.8rem; color:#a0aec0;">(본인)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
