<?php
// DB 서버의 IP 주소 설정 (예: 192.168.20.10)
// 공개 저장소용 변경: 비밀정보는 PHP 환경 변수에서만 읽습니다.
$db_host = getenv('SAFESTAY_DB_HOST');
$db_name = getenv('SAFESTAY_DB_NAME') ?: 'safestay_db';
$db_user = getenv('SAFESTAY_DB_USER');
$db_pass = getenv('SAFESTAY_DB_PASSWORD');

if (!$db_host || !$db_user || $db_pass === false) {
    error_log('SafeStay DB environment variables are not configured.');
    http_response_code(503);
    exit('데이터베이스 연결 설정이 완료되지 않았습니다.');
}

try {
    // PDO 객체를 이용한 원격 DB 연결
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5 // 연결 타임아웃 5초 설정
        ]
    );
} catch (PDOException $e) {
    // 웹 사용자에게 DB 접속 에러 및 IP 정보를 노출하지 않도록 공통 메시지 처리
    error_log("DB Connection Error: " . $e->getMessage());
    die("데이터베이스 연결에 실패했습니다. 잠시 후 다시 시도해주세요.");
}
?>
