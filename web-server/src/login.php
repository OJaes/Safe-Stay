<?php
session_start();
require_once 'db.php'; // DB 연결 파일 불러오기

$error_msg = '';

// 실제 로그인 처리 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_msg = '아이디와 비밀번호를 모두 입력해 주세요.';
    } else {
        try {
            // 1. SELECT 쿼리에 role 컬럼 추가!
            $stmt = $pdo->prepare("SELECT id, username, password, name, role FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            // 사용자 존재 여부 및 비밀번호 검증
            if ($user && password_verify($password, $user['password'])) {
                
                // role 값 정제 (공백 제거 및 소문자화)
                $user_role = strtolower(trim($user['role'] ?? 'user'));

                // 2. 세션에 role 정보까지 함께 저장!
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name']     = $user['name'];
                $_SESSION['role']     = $user_role;

                // 3. role 권한에 따라 리다이렉트 분기 처리!
                if ($user_role === 'admin') {
                    header('Location: /admin/index.php');
                } else {
                    header('Location: /index.php');
                }
                exit;
            } else {
                $error_msg = '아이디 또는 비밀번호가 올바르지 않습니다.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error_msg = '로그인 처리 중 오류가 발생했습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - SafeStay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }

        /* 네비게이션 바 */
        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 1.5rem; font-weight: bold; letter-spacing: 2px; }
        .logo a { color: white; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-left: 20px; font-size: 0.95rem; }
        nav a:hover, nav a.active { color: #f39c12; }

        /* 서브 페이지 헤더 */
        .sub-header { background: #2c3e50; color: white; padding: 40px 20px; text-align: center; }
        .sub-header h1 { font-size: 1.8rem; margin-bottom: 8px; }
        .sub-header p { color: #bdc3c7; font-size: 0.9rem; }

        /* 로그인 컨테이너 */
        .login-container { max-width: 400px; margin: 50px auto; background: white; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .login-title { font-size: 1.4rem; font-weight: bold; color: #2c3e50; text-align: center; margin-bottom: 25px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; font-weight: bold; color: #555; margin-bottom: 8px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        input:focus { border-color: #f39c12; outline: none; }

        .error-box { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; font-size: 0.85rem; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; }

        .btn-login { width: 100%; background: #f39c12; color: white; border: none; padding: 13px; border-radius: 5px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-login:hover { background: #d35400; }

        .login-footer { margin-top: 20px; text-align: center; font-size: 0.85rem; color: #7f8c8d; }
        .login-footer a { color: #f39c12; text-decoration: none; font-weight: bold; }

        /* 푸터 */
        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; margin-top: 60px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <!-- 네비게이션 바 -->
    <header>
        <div class="logo"><a href="/">🛡️ SafeStay</a></div>
        <nav>
            <a href="/about.php">소개</a>
            <a href="/rooms.php">객실안내</a>
            <a href="/booking.php">예약하기</a>
            <a href="/login.php" class="active">로그인</a>
        </nav>
    </header>

    <!-- 서브 헤더 -->
    <div class="sub-header">
        <h1>MEMBER LOGIN</h1>
        <p>SafeStay 회원 서비스 이용을 위해 로그인해 주세요.</p>
    </div>

    <!-- 로그인 폼 -->
    <div class="login-container">
        <div class="login-title">로그인</div>

        <?php if (!empty($error_msg)): ?>
            <div class="error-box"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form action="/login.php" method="POST">
            <div class="form-group">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" placeholder="아이디를 입력하세요" required>
            </div>
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" placeholder="비밀번호를 입력하세요" required>
            </div>
            <button type="submit" class="btn-login">로그인</button>
        </form>

        <div class="login-footer">
            아직 회원이 아니신가요? <a href="/register.php">회원가입</a>
        </div>
    </div>

    <!-- 푸터 -->
    <footer>
        <p>© 2026 SafeStay Hotel System. All rights reserved.</p>
    </footer>

</body>
</html>
