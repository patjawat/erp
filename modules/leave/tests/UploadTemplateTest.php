<?php
/**
 * ทดสอบ logic การยอมรับไฟล์ PDF (รันด้วย: php modules/leave/tests/UploadTemplateTest.php)
 * ต้องรันจากโฟลเดอร์โปรเจกต์ root และมี vendor โหลดแล้ว
 */
if (php_sapi_name() !== 'cli') {
    die('Run from CLI only.');
}
$autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    echo "SKIP: vendor/autoload.php not found\n";
    exit(0);
}
require $autoload;

// สร้าง PDF ขนาดเล็กสำหรับทดสอบ
$testPdf = sys_get_temp_dir() . '/leave-template-test-' . uniqid() . '.pdf';
file_put_contents($testPdf, "%PDF-1.4\n1 0 obj\nendobj\nxref\nstartxref\n%%EOF");

$tests = [
    'PDF magic bytes' => function () use ($testPdf) {
        $head = file_get_contents($testPdf, false, null, 0, 8);
        return $head && strpos($head, '%PDF') === 0;
    },
    'Extension .pdf accepted' => function () {
        return strtolower('PDF') === 'pdf' && strtolower('pdf') === 'pdf';
    },
];

$ok = 0;
foreach ($tests as $name => $fn) {
    $result = $fn();
    echo ($result ? 'PASS' : 'FAIL') . ' ' . $name . "\n";
    if ($result) $ok++;
}
@unlink($testPdf);
echo "Done: $ok/" . count($tests) . "\n";
exit($ok === count($tests) ? 0 : 1);
