<?php
session_start();
require_once __DIR__ . '/db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // 1. 유효성 검사
    if (empty($username) || empty($password) || empty($name) || empty($email)) {
        $error_msg = '필수 입력 항목을 모두 입력해 주세요.';
    } elseif ($password !== $password_confirm) {
        $error_msg = '비밀번호가 일치하지 않습니다.';
    } else {
        try {
            // 2. 아이디 중복 확인 (Prepared Statement 사용)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);

            if ($stmt->fetch()) {
                $error_msg = '이미 사용 중인 아이디입니다.';
            } else {
                // 3. 비밀번호 안전하게 단방향 암호화 (Bcrypt)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 4. 원격 DB 서버로 회원 정보 INSERT
                $insert_stmt = $pdo->prepare("
                    INSERT INTO users (username, password, name, email, phone) 
                    VALUES (:username, :password, :name, :email, :phone)
                ");

                $insert_stmt->execute([
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':name'     => $name,
                    ':email'    => $email,
                    ':phone'    => $phone
                ]);

                $success_msg = '회원가입이 정상적으로 완료되었습니다! 로그인 페이지로 이동합니다.';
                header("refresh:2;url=/login.php");
            }
        } catch (PDOException $e) {
            error_log("Register Error: " . $e->getMessage());
            $error_msg = '회원가입 처리 중 오류가 발생했습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - SafeStay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }

        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
        .logo a { color: white; text-decoration: none; font-size: 1.5rem; font-weight: bold; }
        nav a { color: white; text-decoration: none; margin-left: 20px; font-size: 0.95rem; }
        nav a:hover, nav a.active { color: #f39c12; }

        .sub-header { background: #2c3e50; color: white; padding: 40px 20px; text-align: center; }
        .sub-header h1 { font-size: 1.8rem; margin-bottom: 8px; }

        .register-container { max-width: 480px; margin: 40px auto; background: white; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .register-title { font-size: 1.4rem; font-weight: bold; color: #2c3e50; text-align: center; margin-bottom: 25px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; font-weight: bold; color: #555; margin-bottom: 6px; }
        .required { color: #e74c3c; }
        input { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        input:focus { border-color: #f39c12; outline: none; }

        .error-box { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; font-size: 0.85rem; margin-bottom: 20px; text-align: center; }
        .success-box { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; font-size: 0.85rem; margin-bottom: 20px; text-align: center; }

        .btn-register { width: 100%; background: #f39c12; color: white; border: none; padding: 13px; border-radius: 5px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .btn-register:hover { background: #d35400; }
        .register-footer { margin-top: 20px; text-align: center; font-size: 0.85rem; color: #7f8c8d; }
        .register-footer a { color: #f39c12; text-decoration: none; font-weight: bold; }

        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; margin-top: 60px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <header>
        <div class="logo"><a href="/">🛡️ SafeStay</a></div>
        <nav>
            <a href="/about.php">소개</a>
            <a href="/rooms.php">객실안내</a>
            <a href="/booking.php">예약하기</a>
            <a href="/login.php" class="active">로그인</a>
        </nav>
    </header>

    <div class="sub-header">
        <h1>MEMBER JOIN</h1>
        <p>SafeStay의 신규 회원이 되시고 다양한 혜택을 받아보세요.</p>
    </div>

    <div class="register-container">
        <div class="register-title">회원가입</div>

        <?php if (!empty($error_msg)): ?>
            <div class="error-box"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="success-box"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>

        <form action="/register.php" method="POST">
            <div class="form-group">
                <label for="username">아이디 <span class="required">*</span></label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">비밀번호 <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <div class="form-group">
                <label for="name">이름 <span class="required">*</span></label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">이메일 <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">연락처</label>
                <input type="tel" id="phone" name="phone" placeholder="010-0000-0000">
            </div>
            <button type="submit" class="btn-register">가입하기</button>
        </form>

        <div class="register-footer">
            이미 계정이 있으신가요? <a href="/login.php">로그인하기</a>
        </div>
    </div>

    <footer>
        <p>© 2026 SafeStay Hotel System. All rights reserved.</p>
    </footer>

</body>
</html>
