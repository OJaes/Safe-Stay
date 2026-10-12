<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>호텔 소개 - SafeStay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f9f9f9; color: #333; line-height: 1.6; }

        /* 네비게이션 바 */
        header { background: #1a252f; color: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 1.5rem; font-weight: bold; letter-spacing: 2px; }
        .logo a { color: white; text-decoration: none; }
        nav { display: flex; align-items: center; }
        nav a { color: white; text-decoration: none; margin-left: 20px; font-size: 0.95rem; }
        nav a:hover, nav a.active { color: #f39c12; }
        .user-welcome { color: #f39c12; font-weight: bold; margin-left: 20px; font-size: 0.95rem; }
        .btn-logout { border: 1px solid #e74c3c; padding: 4px 10px; border-radius: 4px; color: #e74c3c !important; }
        .btn-logout:hover { background-color: #e74c3c; color: white !important; }

        /* 서브 페이지 헤더 */
        .sub-header { background: #2c3e50; color: white; padding: 50px 20px; text-align: center; }
        .sub-header h1 { font-size: 2rem; margin-bottom: 8px; }
        .sub-header p { color: #bdc3c7; font-size: 0.95rem; }

        /* 메인 컨테이너 */
        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }

        /* 브랜드 소개 히어로 영역 */
        .about-hero { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; margin-bottom: 60px; }
        .about-hero-img { width: 100%; height: 350px; object-fit: cover; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .about-hero-text h2 { font-size: 1.8rem; color: #2c3e50; margin-bottom: 15px; }
        .about-hero-text p { color: #555; margin-bottom: 15px; line-height: 1.8; }

        /* 핵심 가치 섹션 */
        .section-title { text-align: center; font-size: 1.6rem; color: #2c3e50; margin-bottom: 30px; font-weight: bold; }
        .values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 60px; }
        .value-card { background: white; padding: 30px 20px; text-align: center; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
        .value-icon { font-size: 2.5rem; margin-bottom: 15px; }
        .value-card h3 { font-size: 1.2rem; color: #2c3e50; margin-bottom: 10px; }
        .value-card p { font-size: 0.9rem; color: #666; }

        /* 오시는 길 / 위치 정보 */
        .location-box { background: white; border-radius: 10px; padding: 30px; border: 1px solid #eee; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .location-info h3 { font-size: 1.3rem; color: #2c3e50; margin-bottom: 15px; }
        .location-list { list-style: none; }
        .location-list li { margin-bottom: 12px; font-size: 0.95rem; color: #555; display: flex; gap: 10px; }
        .location-map-placeholder { background: #eef2f5; border-radius: 8px; display: flex; justify-content: center; align-items: center; color: #7f8c8d; font-weight: bold; min-height: 200px; text-align: center; padding: 20px; }

        /* 푸터 */
        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; margin-top: 60px; font-size: 0.85rem; }

        @media (max-width: 768px) {
            .about-hero, .values-grid, .location-box { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- 네비게이션 바 -->
    <header>
        <div class="logo"><a href="/">🛡️ SafeStay</a></div>
        <nav>
            <a href="/">홈</a>
            <a href="/about.php" class="active">호텔소개</a>
            <a href="/rooms.php">객실안내</a>
            <a href="/booking.php">예약하기</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- 로그인 상태일 때 표시 -->
                <span class="user-welcome">👋 <?= htmlspecialchars($_SESSION['name']) ?>님</span>
                <a href="/logout.php" class="btn-logout">로그아웃</a>
            <?php else: ?>
                <!-- 비로그인 상태일 때 표시 -->
                <a href="/login.php">로그인</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- 서브 헤더 -->
    <div class="sub-header">
        <h1>ABOUT SAFESTAY</h1>
        <p>안전하고 여유로운 휴식을 선사하는 부티크 호텔 SafeStay입니다.</p>
    </div>

    <div class="container">
        <!-- 브랜드 소개 -->
        <div class="about-hero">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80" alt="SafeStay 로비" class="about-hero-img">
            <div class="about-hero-text">
                <h2>완벽한 안식처를 향한 여정</h2>
                <p>
                    <strong>SafeStay Hotel</strong>은 도심 속에서 완벽한 피안과 편안함을 선사하기 위해 설립되었습니다. 최첨단 보안과 철저한 위생 관리를 바탕으로 고객 여러분이 오롯이 휴식에만 집중할 수 있는 환경을 제공합니다.
                </p>
                <p>
                    아름다운 전경과 정성 어린 서비스, 프리미엄 객실 시설을 통해 잊지 못할 고품격 호캉스 경험을 약속드립니다.
                </p>
            </div>
        </div>

        <!-- 핵심 가치 (3대 수칙) -->
        <div class="section-title">SafeStay의 핵심 가치</div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🛡️</div>
                <h3>SAFETY</h3>
                <p>24시간 첨단 보안 시스템과 세심한 개인정보 보호로 안전한 투숙 환경을 보장합니다.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">✨</div>
                <h3>CLEANLINESS</h3>
                <p>매일 진행되는 5단계 방역 소독 및 최고급 천연 라텍스 베딩 관리로 청결함을 유지합니다.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h3>HOSPITALITY</h3>
                <p>고객 한 분 한 분의 니즈에 맞춘 맞춤형 컨시어지 서비스로 최상의 만족을 전합니다.</p>
            </div>
        </div>

        <!-- 오시는 길 및 안내 -->
        <div class="section-title">오시는 길 & 안내</div>
        <div class="location-box">
            <div class="location-info">
                <h3>SafeStay 찾아오시는 길</h3>
                <ul class="location-list">
                    <li>📍 <strong>주소 :</strong> 부산광역시 해운대구 해운대해변로 123 SafeStay 타워</li>
                    <li>📞 <strong>대표 전화 :</strong> 051-123-4567</li>
                    <li>✉️ <strong>이메일 :</strong> info@safestay.com</li>
                    <li>🚗 <strong>주차 안내 :</strong> 투숙객 전용 지하 주차장 무료 이용 (객실당 1~2대)</li>
                </ul>
            </div>
            <div class="location-map-placeholder">
                🗺️ [오시는 길 지도 영역]<br>
                해운대역 3번 출구에서 도보 5분 거리
            </div>
        </div>
    </div>

    <!-- 푸터 -->
    <footer>
        <p>© 2026 SafeStay Hotel System. All rights reserved.</p>
    </footer>

</body>
</html>
