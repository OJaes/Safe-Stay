<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>디럭스 킹 - SafeStay</title>
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
        <h1>DELUXE KING</h1>
        <p>탁 트인 오션뷰와 고급 욕조가 완비된 여유로운 휴식 공간입니다.</p>
    </div>

    <div class="container">
        <!-- 대표 이미지 -->
        <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1000&q=80" alt="디럭스 킹 이미지" class="room-hero-img">

        <div class="room-grid">
            <!-- 왼쪽: 객실 정보 -->
            <div>
                <div class="section-title">객실 소개</div>
                <p class="room-intro">
                    디럭스 킹 객실은 최고급 라텍스 매트리스가 적용된 킹 사이즈 침대와 럭셔리 욕실을 갖추고 있습니다. 
                    넓은 통창을 통해 들어오는 채광과 아름다운 오션뷰를 감상하며 피로를 풀 수 있는 안락한 객실입니다.
                </p>

                <div class="section-title">기본 비치 품목 & 서비스 (Amenities)</div>
                <div class="amenity-grid">
                    <div class="amenity-item">🛏️ 킹 사이즈 침대 1개</div>
                    <div class="amenity-item">🛁 전용 대리석 욕조</div>
                    <div class="amenity-item">📶 초고속 무료 Wi-Fi</div>
                    <div class="amenity-item">📺 55인치 스마트 TV</div>
                    <div class="amenity-item">🍷 프리미엄 미니바</div>
                    <div class="amenity-item">☕ 캡슐 커피 머신</div>
                    <div class="amenity-item">🧴 럭셔리 어메니티 & 가운</div>
                    <div class="amenity-item">🔒 개인 금고</div>
                </div>

                <div class="section-title">이용 안내 & 규정</div>
                <ul class="policy-list">
                    <li><strong>체크인 :</strong> 15:00 이후 / <strong>체크아웃 :</strong> 11:00 이전</li>
                    <li>전 객실 금연 구역입니다. (위반 시 청소 소독비 부과)</li>
                    <li>반려동물 동반 입실은 제한됩니다.</li>
                    <li>인원 추가 시 엑스트라 베드 요청 가능 (30,000원 추가)</li>
                </ul>
            </div>

            <!-- 오른쪽: 요금 및 예약 카드 -->
            <div>
                <div class="booking-card">
                    <div class="price-big">₩ 180,000 <span>/ 1박</span></div>
                    <div class="capacity-info">
                        • 기준 인원 : 2인 (최대 3인)<br>
                        • 객실 크기 : 38 m² (약 11평)<br>
                        • 전망 : 오션 뷰 (Ocean View)
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
