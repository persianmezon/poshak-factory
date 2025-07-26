<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

// مسیر کامل فایل فونت
$customFontDir = __DIR__ . '/public_html/fonts';

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'fontDir' => array_merge((new Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
        $customFontDir
    ]),
    'fontdata' => array_merge((new Mpdf\Config\FontVariables())->getDefaults()['fontdata'], [
        'vazir' => [
            'R' => 'Vazirmatn.ttf',
        ]
    ]),
    'default_font' => 'vazir'
]);

// گرفتن داده‌ها از Firebase
$firebaseData = file_get_contents('https://poshak-factory-default-rtdb.firebaseio.com/qr-scans.json');
$data = json_decode($firebaseData, true);

// فیلتر داده‌های امروز
$today = date('Y-m-d');
$filtered = array_filter($data ?? [], function ($item) use ($today) {
    return isset($item['createdAt']) && date('Y-m-d', $item['createdAt']) === $today;
});

// تیتر گزارش
$mpdf->WriteHTML("<h3 style='font-family: vazir;'>📊 گزارش آماری اسکن QRCode - تاریخ $today</h3>");

// جدول
$mpdf->WriteHTML("<table border='1' cellpadding='6' style='border-collapse: collapse; font-family: vazir; font-size: 12px;'>");
$mpdf->WriteHTML("<thead><tr>
<th>تاریخ</th><th>بخش</th><th>قسمت</th><th>کد</th><th>تعداد</th><th>کاربر</th>
</tr></thead><tbody>");

foreach ($filtered as $item) {
    $mpdf->WriteHTML("<tr>
    <td>" . date('Y-m-d H:i', $item['createdAt']) . "</td>
    <td>" . ($item['section'] ?? '-') . "</td>
    <td>" . ($item['part'] ?? '-') . "</td>
    <td>" . ($item['code'] ?? '-') . "</td>
    <td>" . ($item['count'] ?? 0) . "</td>
    <td>" . ($item['workerId'] ?? '-') . "</td>
    </tr>");
}

$mpdf->WriteHTML("</tbody></table>");

// ذخیره گزارش
$savePath = __DIR__ . "/reports/qr-report-$today.pdf";
if (!file_exists(__DIR__ . '/reports')) {
    mkdir(__DIR__ . '/reports', 0777, true);
}
$mpdf->Output($savePath, \Mpdf\Output\Destination::FILE);

echo "✅ فایل ذخیره شد: $savePath";
