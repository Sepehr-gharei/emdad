<?php
/**
 * Proxy استعلام اصالت کالا
 * این فایل رو داخل پوشه emdad/ بذار
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// فقط GET قبول میکنیم
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'کد یکتا وارد نشده']);
    exit;
}

// سایت استعلام ایران
$api_url = 'https://estelamiran.ir/api/check?code=' . urlencode($code);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0',
    ],
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error     = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(502);
    echo json_encode(['error' => 'خطا در اتصال به سرور استعلام', 'detail' => $error]);
    exit;
}

// جواب API رو مستقیم برمیگردونیم
http_response_code($http_code);
echo $response;