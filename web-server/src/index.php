<?php
session_start();

// (선택 사항) 로그인하지 않은 사용자를 무조건 로그인 페이지로 보내려면 아래 4줄의 주석(//)을 해제하세요.
/*
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
*/
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeStay - 가장 안전하고 편안한 휴식</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f9f9f9; color: #333; line-height: 1.6; }

        /* 네비게이션 바 */
        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 1.5rem; font-weight: bold; letter-spacing: 2px; }
        nav { display: flex; align-items: center; }
        nav a { color: white; text-decoration: none; margin-left: 20px; font-size: 0.95rem; }
        nav a:hover { color: #f39c12; }
        .user-welcome { color: #f39c12; font-weight: bold; margin-left: 20px; font-size: 0.95rem; }
        .btn-logout { border: 1px solid #e74c3c; padding: 4px 10px; border-radius: 4px; color: #e74c3c !important; }
        .btn-logout:hover { background-color: #e74c3c; color: white !important; }

        /* 메인 히어로 섹션 */
        .hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80') center/cover; height: 60vh; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 0 20px; }
        .hero h1 { font-size: 2.8rem; margin-bottom: 10px; }
        .hero p { font-size: 1.2rem; margin-bottom: 20px; }

        /* 예약 영역 */
        .booking-bar { background: white; max-width: 900px; margin: -40px auto 40px auto; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; gap: 15px; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; position: relative; z-index: 10; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-reserve { background: #f39c12; color: white; border: none; padding: 12px 25px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-reserve:hover { background: #d35400; }

        /* 객실 소개 섹션 */
        .section-title { text-align: center; font-size: 2rem; margin: 40px 0 20px 0; }
        .room-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1100px; margin: 0 auto 60px auto; padding: 0 20px; }
        .room-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .room-card img { width: 100%; height: 200px; object-fit: cover; }
        .room-info { padding: 20px; }
        .room-info h3 { margin-bottom: 10px; font-size: 1.3rem; }
        .room-info p { color: #666; font-size: 0.9rem; margin-bottom: 15px; }
        .price { font-size: 1.2rem; font-weight: bold; color: #e74c3c; margin-bottom: 10px; }

        /* 푸터 */
        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <!-- 네비게이션 바 -->
    <header>
        <div class="logo">🛡️ SafeStay</div>
        <nav>
            <a href="about.php">소개</a>
            <a href="rooms.php">객실안내</a>
            <a href="booking.php">예약하기</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- 로그인 상태일 때 표시 -->
                <span class="user-welcome">👋 <?= htmlspecialchars($_SESSION['name']) ?>님</span>
                <a href="logout.php" class="btn-logout">로그아웃</a>
            <?php else: ?>
                <!-- 비로그인 상태일 때 표시 -->
                <a href="login.php">로그인</a>
                <a href="register.php">회원가입</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- 메인 비주얼 배너 -->
    <section class="hero">
        <h1>최상의 휴식과 특별한 경험</h1>
        <p>SafeStay에서 잊지 못할 추억을 만들어 드립니다.</p>
    </section>

    <!-- 예약 바 (사용자 예약 입력) -->
    <div class="booking-bar" id="booking">
        <div class="form-group">
            <label>체크인</label>
            <input type="date">
        </div>
        <div class="form-group">
            <label>체크아웃</label>
            <input type="date">
        </div>
        <div class="form-group">
            <label>객실 선택</label>
            <select>
                <option>디럭스 킹</option>
                <option>스탠다드 트윈</option>
                <option>프리미엄 스위트</option>
            </select>
        </div>
        <a href="booking.php" class="btn-reserve" style="text-decoration: none; display: inline-block; text-align: center;">객실 예약하기</a>
    </div>

    <!-- 객실 소개 섹션 -->
    <h2 class="section-title" id="rooms">RECOMMENDED ROOMS</h2>
    <div class="room-grid">
        <div class="room-card">
            <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80" alt="스탠다드 트윈">
            <div class="room-info">
                <h3>스탠다드 트윈 (Standard Twin)</h3>
                <p>실용적이고 아늑한 공간 구조로 비즈니스 고객 및 친구와 함께하는 여행객에게 최적인 객실입니다.</p>
                <div class="price">₩ 130,000 / 박</div>
            </div>
        </div>

        <div class="room-card">
            <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=500&q=80" alt="디럭스 룸">
            <div class="room-info">
                <h3>디럭스 킹 (Deluxe King)</h3>
                <p>아늑한 침대와 현대적인 인테리어가 어우러진 편안한 객실입니다.</p>
                <div class="price">₩ 180,000 / 박</div>
            </div>
        </div>
        <div class="room-card">
            <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=500&q=80" alt="스위트 룸">
            <div class="room-info">
                <h3>프리미엄 스위트 (Suite)</h3>
                <p>탁 트인 전망과 넓은 거실 공간을 갖춘 최고급 객실입니다.</p>
                <div class="price">₩ 350,000 / 박</div>
            </div>
        </div>
    </div>

    <!-- 푸터 -->
    <footer>
        <p>© 2026 Grand Hotel System. All rights reserved.</p>
    </footer>

</body>
</html>
