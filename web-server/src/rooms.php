<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>객실 안내 - SafeStay</title>
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

        /* 서브 페이지 타이틀 */
        .sub-header { background: #2c3e50; color: white; padding: 60px 20px; text-align: center; }
        .sub-header h1 { font-size: 2.2rem; margin-bottom: 10px; }
        .sub-header p { color: #bdc3c7; }

        /* 메인 컨테이너 */
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        /* 객실 목록 (가로 세로 배치 카드) */
        .room-list-item { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.08); display: flex; flex-wrap: wrap; margin-bottom: 30px; }
        .room-img { flex: 1 1 400px; min-height: 280px; position: relative; }
        .room-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .room-details { flex: 2 1 400px; padding: 30px; display: flex; flex-direction: column; justify-content: space-between; }

        .room-title { font-size: 1.6rem; margin-bottom: 10px; color: #2c3e50; display: flex; justify-content: space-between; align-items: center; }
        .room-capacity { font-size: 0.85rem; color: #7f8c8d; font-weight: normal; }
        .room-desc { color: #666; font-size: 0.95rem; margin-bottom: 20px; }

        /* 편의시설 아이콘 태그 */
        .amenities { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .tag { background: #ecf0f1; color: #34495e; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }

        /* 가격 및 예약 버튼 */
        .room-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 20px; }
        .price-tag { font-size: 1.4rem; font-weight: bold; color: #e74c3c; }
        .price-tag span { font-size: 0.85rem; color: #999; font-weight: normal; }
        .btn-book { background: #1a252f; color: white; text-decoration: none; padding: 10px 22px; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-book:hover { background: #f39c12; }

        /* 푸터 */
        footer { background: #1a252f; color: #888; text-align: center; padding: 20px; margin-top: 60px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <!-- 네비게이션 바 -->
    <header>
        <div class="logo"><a href="/">🛡️ SafeStay</a></div>
        <nav>
            <a href="/">홈</a>
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

    <!-- 페이지 헤더 -->
    <div class="sub-header">
        <h1>ROOMS & SUITES</h1>
        <p>안전하고 편안한 공간, SafeStay의 객실을 소개합니다.</p>
    </div>

    <!-- 객실 목록 컨테이너 -->
    <div class="container">

        <!-- 객실 1: 스탠다드 트윈 -->
        <div class="room-list-item">
            <div class="room-img">
                <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80" alt="스탠다드 트윈">
            </div>
            <div class="room-details">
                <div>
                    <div class="room-title">
                        스탠다드 트윈 (Standard Twin)
                        <span class="room-capacity">기준 2인 / 최대 2인</span>
                    </div>
                    <p class="room-desc">실용적이고 아늑한 공간 구조로 비즈니스 고객 및 친구와 함께하는 여행객에게 최적인 객실입니다.</p>
                    <div class="amenities">
                        <span class="tag">싱글침대 2개</span>
                        <span class="tag">무료 Wi-Fi</span>
                        <span class="tag">43인치 TV</span>
                        <span class="tag">시티뷰</span>
                    </div>
                </div>
                <div class="room-footer">
                    <div class="price-tag">₩ 130,000 <span>/ 1박</span></div>
                    <a href="/room-standard-twin.php" class="btn-book">상세보기</a>
                </div>
            </div>
        </div>

        <!-- 객실 2: 디럭스 킹 -->
        <div class="room-list-item">
            <div class="room-img">
                <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=600&q=80" alt="디럭스 킹">
            </div>
            <div class="room-details">
                <div>
                    <div class="room-title">
                        디럭스 킹 (Deluxe King)
                        <span class="room-capacity">기준 2인 / 최대 3인</span>
                    </div>
                    <p class="room-desc">넉넉한 사이즈의 킹 베드와 고급스러운 인테리어가 어우러져 한층 더 여유로운 휴식을 제공합니다.</p>
                    <div class="amenities">
                        <span class="tag">킹 사이즈 침대</span>
                        <span class="tag">무료 Wi-Fi</span>
                        <span class="tag">욕조 보유</span>
                        <span class="tag">미니바</span>
                        <span class="tag">오션뷰</span>
                    </div>
                </div>
                <div class="room-footer">
                    <div class="price-tag">₩ 180,000 <span>/ 1박</span></div>
                    <a href="/room-deluxe-king.php" class="btn-book">상세보기</a>
                </div>
            </div>
        </div>

        <!-- 객실 3: 프리미엄 스위트 -->
        <div class="room-list-item">
            <div class="room-img">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80" alt="프리미엄 스위트">
            </div>
            <div class="room-details">
                <div>
                    <div class="room-title">
                        프리미엄 스위트 (Executive Suite)
                        <span class="room-capacity">기준 2인 / 최대 4인</span>
                    </div>
                    <p class="room-desc">독립된 침실과 거실 공간을 갖추어 최고급 프라이빗 서비스와 파노라마 뷰를 경험하실 수 있는 스위트 객실입니다.</p>
                    <div class="amenities">
                        <span class="tag">킹 침대 + 거실</span>
                        <span class="tag">무료 조식 2인</span>
                        <span class="tag">에스프레소 머신</span>
                        <span class="tag">고급 어메니티</span>
                    </div>
                </div>
                <div class="room-footer">
                    <div class="price-tag">₩ 350,000 <span>/ 1박</span></div>
                    <a href="/room-suite.php" class="btn-book">상세보기</a>
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
