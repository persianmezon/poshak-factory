<?php
header("Access-Control-Allow-Origin: https://app.paryamezon.ir");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");


// دریافت داده از Vue
$input = json_decode(file_get_contents("php://input"), true);
$section = $input['section'] ?? '';
$part    = $input['part'] ?? null;
$code    = $input['code'] ?? '';
$count   = $input['count'] ?? 0;

// اعتبارسنجی
if (!$section || !$code || $count <= 0) {
  echo json_encode(["success"=>false, "message"=>"داده ناقص است."]);
  exit;
}

// داده برای Firebase
$data = [
  "section"   => $section,
  "part"      => $part,
  "code"      => $code,
  "count"     => $count,
  "createdAt" => ["timestamp" => ["." => "sv"]],
  "status"    => "pending"
];

// ارسال با cURL
$ch = curl_init("https://app.paryamezon.ir/api/firebase-proxy.php?path=qr_tasks.json");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$codeHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// پاسخ
if ($codeHttp === 200) {
  echo json_encode(["success"=>true]);
} else {
  echo json_encode(["success"=>false, "message"=>"خطا در ارسال به Firebase"]);
}
