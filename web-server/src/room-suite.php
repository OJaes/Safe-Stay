<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프리미엄 스위트 - SafeStay</title>
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
        .sub-header { background: #2c3e50; color: white; padding: 40px 20px; text-align: center; }
        .sub-header h1 { font-size: 2rem; margin-bottom: 8px; }
        .sub-header p { color: #bdc3c7; font-size: 0.95rem; }

        /* 메인 컨테이너 */
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }

        /* 객실 대표 이미지 영역 */
        .room-hero-img { width: 100%; height: 420px; object-fit: cover; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }

        /* 레이아웃 (좌: 상세설명 / 우: 예약 요약 카드) */
        .room-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }

        /* 왼쪽 내용 */
        .section-title { font-size: 1.3rem; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; margin: 30px 0 15px 0; }
        .section-title:first-child { margin-top: 0; }

        .room-intro { color: #555; margin-bottom: 25px; line-height: 1.8; }

        /* 어메니티 아이콘 그리드 */
        .amenity-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 25px; }
        .amenity-item { background: white; padding: 12px 15px; border-radius: 6px; border: 1px solid #eee; font-size: 0.9rem; font-weight: bold; color: #34495e; display: flex; align-items: center; gap: 8px; }

        /* 규정 안내 */
        .policy-list { list-style: none; color: #666; font-size: 0.9rem; }
        .policy-list li { margin-bottom: 8px; padding-left: 15px; position: relative; }
        .policy-list li::before { content: "•"; position: absolute; left: 0; color: #f39c12; font-weight: bold; }

        /* 오른쪽 sticky 고정 예약 박스 */
        .booking-card { background: white; padding: 25px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 90px; height: fit-content; }
        .price-big { font-size: 1.8rem; font-weight: bold; color: #e74c3c; margin-bottom: 5px; }
        .price-big span { font-size: 0.9rem; color: #888; font-weight: normal; }
        .capacity-info { color: #7f8c8d; font-size: 0.85rem; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }

        .btn-book-now { display: block; width: 100%; background: #f39c12; color: white; text-align: center; padding: 14px; border-radius: 5px; font-weight: bold; text-decoration: none; transition: background 0.3s; }
        .btn-book-now:hover { background: #d35400; }

        /* 푸터 */
        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; margin-top: 60px; font-size: 0.85rem; }

        @media (max-width: 768px) {
            .room-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- 네비게이션 바 -->
    <header>
        <div class="logo"><a href="/">🛡️ SafeStay</a></div>
        <nav>
            <a href="/about.php">소개</a>
            <a href="/rooms.php" class="active">객실안내</a>
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
        <h1>EXECUTIVE SUITE</h1>
        <p>독립된 거실과 조식 혜택을 제공하는 SafeStay 최고급 프라이빗 스위트입니다.</p>
    </div>

    <div class="container">
        <!-- 대표 이미지 -->
        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1000&q=80" alt="프리미엄 스위트 이미지" class="room-hero-img">

        <div class="room-grid">
            <!-- 왼쪽: 객실 정보 -->
            <div>
                <div class="section-title">객실 소개</div>
                <p class="room-intro">
                    프리미엄 스위트는 침실과 넓은 별도 거실 공간이 분리되어 있어 최상급 프라이버시를 보장합니다. 
                    파노라마 뷰로 펼쳐지는 환상적인 전경과 함께 2인 무료 뷔페 조식 혜택 및 VIP 전용 웰컴 드링크 서비스가 함께 제공됩니다.
                </p>

                <div class="section-title">기본 비치 품목 & 특별 혜택 (Amenities & Benefits)</div>
                <div class="amenity-grid">
                    <div class="amenity-item">🍳 무료 2인 조식 뷔페</div>
                    <div class="amenity-item">🛋️ 별도 응접실 및 소파</div>
                    <div class="amenity-item">🛏️ 킹 사이즈 침대 1개</div>
                    <div class="amenity-item">🛁 월풀 스파 욕조</div>
                    <div class="amenity-item">📺 65인치 OLED TV</div>
                    <div class="amenity-item">🍷 웰컴 와인 & 와인셀러</div>
                    <div class="amenity-item">☕ 프리미엄 에스프레소 머신</div>
                    <div class="amenity-item">🧴 딥티크 고급 어메니티</div>
                </div>

                <div class="section-title">이용 안내 & 규정</div>
                <ul class="policy-list">
                    <li><strong>체크인 :</strong> 15:00 이후 / <strong>체크아웃 :</strong> 12:00 이전 (레이트 체크아웃 1시간 무료)</li>
                    <li>전 객실 금연 구역입니다. (위반 시 청소 소독비 부과)</li>
                    <li>투숙객 무료 주차 2대 제공</li>
                    <li>기준 인원 초과 시 추가 인원 요금 발생</li>
                </ul>
            </div>

            <!-- 오른쪽: 요금 및 예약 카드 -->
            <div>
                <div class="booking-card">
                    <div class="price-big">₩ 350,000 <span>/ 1박</span></div>
                    <div class="capacity-info">
                        • 기준 인원 : 2인 (최대 4인)<br>
                        • 객실 크기 : 72 m² (약 22평)<br>
                        • 전망 : 파노라마 오션 & 시티 뷰
                    </div>
                    <a href="/booking.php" class="btn-book-now">이 객실 예약하기</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 푸터 -->
    <footer>
        <p>© 2026 SafeStay Hotel System. All rights reserved.</p>
    </footer>

</body>
</html>
