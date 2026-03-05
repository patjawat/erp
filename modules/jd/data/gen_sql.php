<?php
/**
 * Generate seed_moph.sql from MophSeedData
 * Run: php modules/jd/data/gen_sql.php > modules/jd/data/seed_moph.sql
 */
require_once __DIR__ . '/MophSeedData.php';
use app\modules\jd\data\MophSeedData;

$positions = MophSeedData::getPositions();

$esc = fn($s) => str_replace(["\\","'"], ["\\\\","\\'"], (string)$s);
$nl  = fn($s) => str_replace("\n", '\n', $esc($s));

$out = "-- =============================================================\n";
$out .= "-- Seed: JD Templates กระทรวงสาธารณสุข (" . count($positions) . " ตำแหน่ง)\n";
$out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$out .= "-- นำเข้าผ่าน phpMyAdmin หรือปุ่ม \"นำเข้า Template สาธารณสุข\" ในระบบ\n";
$out .= "-- =============================================================\n\n";
$out .= "SET NAMES 'utf8mb4';\n\n";

// Delete existing seed
$out .= "-- ── ล้างข้อมูล Seed เดิม (position_code LIKE 'moph_%') ──\n";
$out .= "DELETE jts FROM jd_template_section jts\n";
$out .= "  INNER JOIN jd_template jt ON jts.template_id = jt.id\n";
$out .= "  WHERE jt.position_code LIKE 'moph_%';\n";
$out .= "DELETE FROM jd_template WHERE position_code LIKE 'moph_%';\n\n";

foreach ($positions as $i => $pos) {
    $n = $i + 1;
    $out .= "-- ── {$n}. {$pos['name']} ──\n";

    $eduReq   = $esc($pos['edu_requirement'] ?? '');
    $expYears = isset($pos['exp_years']) ? (int)$pos['exp_years'] : 'NULL';
    $coreComp = $esc($pos['core_competency'] ?? '');
    $jobPurp  = $esc($pos['job_purpose'] ?? '');

    $out .= "INSERT INTO jd_template\n";
    $out .= "  (name, position_code, job_code, job_level, department, employment_type,\n";
    $out .= "   job_purpose, edu_requirement, exp_years, core_competency,\n";
    $out .= "   is_active, created_at, updated_at)\n";
    $out .= "VALUES\n";
    $out .= "  ('{$esc($pos['name'])}', '{$esc($pos['position_code'])}',\n";
    $out .= "   '{$esc($pos['job_code'] ?? '')}', '{$esc($pos['job_level'] ?? '')}',\n";
    $out .= "   '{$esc($pos['department'] ?? '')}', '{$esc($pos['employment_type'] ?? '')}',\n";
    $out .= "   '{$jobPurp}', '{$eduReq}', {$expYears}, '{$coreComp}',\n";
    $out .= "   1, NOW(), NOW());\n\n";

    foreach ($pos['sections'] as [$sort, $title, $content]) {
        $out .= "INSERT INTO jd_template_section (template_id, title, content, sort_order)\n";
        $out .= "  SELECT id, '{$esc($title)}', '{$esc($content)}', {$sort}\n";
        $out .= "  FROM jd_template WHERE position_code = '{$esc($pos['position_code'])}'\n";
        $out .= "  ORDER BY id DESC LIMIT 1;\n";
    }
    $out .= "\n";
}

echo $out;
