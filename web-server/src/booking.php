<?php
session_start();

// 1. 비로그인 사용자 접속 시 경고창 없이 즉시 로그인 페이지로 리다이렉트
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

require_once 'db.php'; // DB 연결 파일

$alert_msg = '';
$alert_type = ''; // 'success' 또는 'error'

// 2. 예약 데이터 POST 전송 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id          = $_SESSION['user_id'];
    $check_in         = trim($_POST['checkin'] ?? '');
    $check_out        = trim($_POST['checkout'] ?? '');
    $room_price_night = (int)($_POST['room_type'] ?? 0);
    $room_name        = trim($_POST['room_name'] ?? '');
    $guests           = (int)($_POST['guests'] ?? 1);
    $guest_name       = trim($_POST['guest_name'] ?? '');
    $guest_phone      = trim($_POST['guest_phone'] ?? '');
    $guest_email      = trim($_POST['guest_email'] ?? '');
    $special_requests = trim($_POST['requests'] ?? '');

    // 유효성 검사
    if (empty($check_in) || empty($check_out) || empty($room_name) || empty($guest_name) || empty($guest_phone) || empty($guest_email)) {
        $alert_msg = '필수 항목을 모두 입력해 주세요.';
        $alert_type = 'error';
    } elseif ($check_in >= $check_out) {
        $alert_msg = '체크아웃 날짜는 체크인 날짜 이후여야 합니다.';
        $alert_type = 'error';
    } else {
        try {
            // 박수 및 총 금액 계산
            $startDate  = new DateTime($check_in);
            $endDate    = new DateTime($check_out);
            $nights     = $startDate->diff($endDate)->days;
            $total_price = $nights * $room_price_night;

            // DB 저장 (Prepared Statement 사용으로 SQL Injection 방지)
            $sql = "INSERT INTO bookings 
                    (user_id, check_in, check_out, room_name, guests, guest_name, guest_phone, guest_email, special_requests, total_price) 
                    VALUES 
                    (:user_id, :check_in, :check_out, :room_name, :guests, :guest_name, :guest_phone, :guest_email, :special_requests, :total_price)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'          => $user_id,
                ':check_in'         => $check_in,
                ':check_out'        => $check_out,
                ':room_name'        => $room_name,
                ':guests'           => $guests,
                ':guest_name'       => $guest_name,
                ':guest_phone'      => $guest_phone,
                ':guest_email'      => $guest_email,
                ':special_requests' => $special_requests,
                ':total_price'      => $total_price
            ]);

            $alert_msg = '예약이 정상적으로 완료되었습니다!';
            $alert_type = 'success';
        } catch (PDOException $e) {
            error_log("Booking DB Error: " . $e->getMessage());
            $alert_msg = '예약 처리 중 DB 오류가 발생했습니다.';
            $alert_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>객실 예약 - SafeStay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Noto Sans KR', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }

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

        /* 메인 폼 컨테이너 */
        .booking-container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

        .form-section-title { font-size: 1.2rem; font-weight: bold; color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }

        label { font-size: 0.9rem; font-weight: bold; color: #555; margin-bottom: 8px; }
        input, select, textarea { padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        input:focus, select:focus, textarea:focus { border-color: #f39c12; outline: none; }

        /* 알림 메시지 상자 */
        .alert-box { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert-box.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-box.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* 요금 안내 박스 */
        .summary-box { background: #f8f9fa; border: 1px dashed #34495e; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem; }
        .summary-row.total { font-size: 1.2rem; font-weight: bold; color: #e74c3c; border-top: 1px solid #ddd; padding-top: 10px; margin-bottom: 0; }

        /* 예약 제출 버튼 */
        .btn-submit { width: 100%; background: #f39c12; color: white; border: none; padding: 15px; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background: #d35400; }

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
            <a href="/booking.php" class="active">예약하기</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-welcome">👋 <?= htmlspecialchars($_SESSION['name']) ?>님</span>
                <a href="/logout.php" class="btn-logout">로그아웃</a>
            <?php else: ?>
                <a href="/login.php">로그인</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- 서브 헤더 -->
    <div class="sub-header">
        <h1>RESERVATION</h1>
        <p>SafeStay 온라인 예약 페이지입니다.</p>
    </div>

    <!-- 예약 신청 폼 -->
    <div class="booking-container">

        <!-- 성공/실패 메시지 표시 -->
        <?php if (!empty($alert_msg)): ?>
            <div class="alert-box <?= $alert_type ?>">
                <?= htmlspecialchars($alert_msg) ?>
            </div>
        <?php endif; ?>

        <form action="/booking.php" method="POST">
            <!-- 숨겨진 Input: 객실 명칭 전달용 -->
            <input type="hidden" id="room_name" name="room_name" value="">

            <!-- 1. 일정 및 객실 선택 -->
            <div class="form-section-title">1. 일정 및 객실 선택</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="checkin">체크인 날짜</label>
                    <input type="date" id="checkin" name="checkin" required>
                </div>
                <div class="form-group">
                    <label for="checkout">체크아웃 날짜</label>
                    <input type="date" id="checkout" name="checkout" required>
                </div>
                <div class="form-group">
                    <label for="room_type">객실 선택</label>
                    <select id="room_type" name="room_type" required>
                        <option value="">-- 객실을 선택하세요 --</option>
                        <option value="130000" data-name="스탠다드 트윈" data-maxguests="2">스탠다드 트윈 (₩130,000 / 최대 2인)</option>
                        <option value="180000" data-name="디럭스 킹" data-maxguests="3">디럭스 킹 (₩180,000 / 최대 3인)</option>
                        <option value="350000" data-name="프리미엄 스위트" data-maxguests="4">프리미엄 스위트 (₩350,000 / 최대 4인)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="guests">투숙 인원</label>
                    <select id="guests" name="guests">
                        <option value="1">1명</option>
                        <option value="2" selected>2명</option>
                    </select>
                </div>
            </div>

            <!-- 2. 예약자 정보 입력 -->
            <div class="form-section-title">2. 예약자 정보</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="guest_name">성함</label>
                    <input type="text" id="guest_name" name="guest_name" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" placeholder="예: 홍길동" required>
                </div>
                <div class="form-group">
                    <label for="guest_phone">연락처</label>
                    <input type="tel" id="guest_phone" name="guest_phone" placeholder="010-0000-0000" required>
                </div>
                <div class="form-group full-width">
                    <label for="guest_email">이메일 주소</label>
                    <input type="email" id="guest_email" name="guest_email" placeholder="example@email.com" required>
                </div>
                <div class="form-group full-width">
                    <label for="requests">특별 요청사항 (선택)</label>
                    <textarea id="requests" name="requests" rows="4" placeholder="고층 요청, 레이트 체크인 등 요청사항을 남겨주세요."></textarea>
                </div>
            </div>

            <!-- 3. 실시간 요금 계산 요약 박스 -->
            <div class="summary-box">
                <div class="summary-row">
                    <span>선택 객실</span>
                    <span id="summary_room">미선택</span>
                </div>
                <div class="summary-row">
                    <span>투숙 인원</span>
                    <span id="summary_guests">2 명</span>
                </div>
                <div class="summary-row">
                    <span>숙박 기간</span>
                    <span id="summary_nights">0 박</span>
                </div>
                <div class="summary-row total">
                    <span>총 결제 예정 금액</span>
                    <span id="summary_total">₩ 0</span>
                </div>
            </div>

            <!-- 제출 버튼 -->
            <button type="submit" class="btn-submit">예약 신청하기</button>
        </form>
    </div>

    <!-- 푸터 -->
    <footer>
        <p>© 2026 SafeStay Hotel System. All rights reserved.</p>
    </footer>

    <!-- 실시간 요금 & 투숙 인원 동적 변경 JavaScript -->
    <script>
        const checkinInput = document.getElementById('checkin');
        const checkoutInput = document.getElementById('checkout');
        const roomSelect = document.getElementById('room_type');
        const roomNameHidden = document.getElementById('room_name');
        const guestsSelect = document.getElementById('guests');

        const summaryRoom = document.getElementById('summary_room');
        const summaryGuests = document.getElementById('summary_guests');
        const summaryNights = document.getElementById('summary_nights');
        const summaryTotal = document.getElementById('summary_total');

        // 오늘 날짜 기본값 세팅
        const today = new Date().toISOString().split('T')[0];
        checkinInput.min = today;

        // 객실 변경 시 인원수 드롭다운 동적 생성
        function updateGuestOptions() {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const maxGuests = parseInt(selectedOption.dataset.maxguests) || 2;

            // 히든 필드에 객실명 설정
            roomNameHidden.value = selectedOption.dataset.name || '';

            // 기존 옵션 초기화
            guestsSelect.innerHTML = '';

            for (let i = 1; i <= maxGuests; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = i + '명';
                if (i === 2 && maxGuests >= 2) {
                    opt.selected = true; // 기본 2인 선택
                }
                guestsSelect.appendChild(opt);
            }
            summaryGuests.innerText = guestsSelect.value + ' 명';
        }

        function calculatePrice() {
            const checkinVal = checkinInput.value;
            const checkoutVal = checkoutInput.value;
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];

            const pricePerNight = parseInt(roomSelect.value) || 0;
            const roomName = selectedOption.dataset.name || '미선택';

            // 요약 정보 업데이트
            summaryRoom.innerText = roomName;
            summaryGuests.innerText = (guestsSelect.value || 0) + ' 명';

            // 날짜 검증 및 박수 계산
            if (checkinVal && checkoutVal) {
                const startDate = new Date(checkinVal);
                const endDate = new Date(checkoutVal);

                if (endDate <= startDate) {
                    alert('체크아웃 날짜는 체크인 날짜 이후여야 합니다.');
                    checkoutInput.value = '';
                    summaryNights.innerText = '0 박';
                    summaryTotal.innerText = '₩ 0';
                    return;
                }

                const diffTime = Math.abs(endDate - startDate);
                const nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                summaryNights.innerText = nights + ' 박';

                if (pricePerNight > 0) {
                    const totalPrice = nights * pricePerNight;
                    summaryTotal.innerText = '₩ ' + totalPrice.toLocaleString();
                } else {
                    summaryTotal.innerText = '₩ 0';
                }
            } else {
                summaryNights.innerText = '0 박';
                summaryTotal.innerText = '₩ 0';
            }
        }

        // 이벤트 리스너 설정
        roomSelect.addEventListener('change', function() {
            updateGuestOptions();
            calculatePrice();
        });

        checkinInput.addEventListener('change', function() {
            if (checkinInput.value) {
                checkoutInput.min = checkinInput.value;
            }
            calculatePrice();
        });

        checkoutInput.addEventListener('change', calculatePrice);
        guestsSelect.addEventListener('change', calculatePrice);
    </script>

</body>
</html>
