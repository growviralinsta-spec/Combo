<?php
// MrSMM backend proxy. Keep your API key ONLY on the server.
header('Content-Type: application/json; charset=utf-8');

$API_KEY = 'PUT_YOUR_NEW_MRSMM_API_KEY_HERE';
$API_URL = 'https://mrsmm.org/api/v2';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$service = (int)($input['service'] ?? 0);
$link = trim($input['link'] ?? '');
$quantity = (int)($input['quantity'] ?? 1);

if ($service <= 0 || $link === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing service or link']);
    exit;
}

/*
 * IMPORTANT:
 * Replace the demo service IDs below with the actual MrSMM service IDs
 * from your account. The HTML currently uses 1, 2, 3 as placeholders.
 */
$serviceMap = [
    1 => 1, // Starter Combo -> MrSMM service ID
    2 => 2, // Growth Combo  -> MrSMM service ID
    3 => 3  // Pro Combo     -> MrSMM service ID
];

if (!isset($serviceMap[$service])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Service not configured']);
    exit;
}

$payload = [
    'key' => $API_KEY,
    'action' => 'add',
    'service' => $serviceMap[$service],
    'link' => $link,
    'quantity' => $quantity
];

$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['success'=>false,'error'=>'Provider connection failed']);
    exit;
}

$data = json_decode($response, true);

if ($http >= 400 || !is_array($data)) {
    http_response_code(502);
    echo json_encode(['success'=>false,'error'=>'Invalid provider response']);
    exit;
}

if (isset($data['order'])) {
    echo json_encode(['success'=>true,'order'=>$data['order']]);
} else {
    echo json_encode(['success'=>false,'error'=>$data['error'] ?? 'Order could not be placed']);
}
?>