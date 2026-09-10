<?php

declare(strict_types=1);

namespace app\modules\swot\services;

use app\modules\ai\services\AiProviderFactory;
use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use RuntimeException;
use Throwable;

/**
 * วิเคราะห์กระดาน SWOT/SOAR ด้วย AI (ผ่าน OpenRouter ของโมดูล ai)
 * คืนผลเป็นโครงสร้างพร้อมเก็บลงคอลัมน์ ai_analysis
 */
class SwotAiAnalyzer
{
    public function analyze(SwotBoard $board): array
    {
        $notesByQuadrant = $board->notesByQuadrant();
        $total = 0;
        foreach ($notesByQuadrant as $arr) {
            $total += count($arr);
        }
        if ($total === 0) {
            throw new RuntimeException('ยังไม่มีประเด็นให้วิเคราะห์ — เพิ่มโพสต์อิทก่อน');
        }

        $factory = new AiProviderFactory();
        $provider = $factory->create(); // openrouter (ค่า default)

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($board)],
            ['role' => 'user', 'content' => $this->userPrompt($board, $notesByQuadrant)],
        ];

        try {
            $response = $provider->chat($messages, [], ['temperature' => 0.4]);
        } catch (Throwable $e) {
            throw new RuntimeException('เรียก AI ไม่สำเร็จ: ' . $e->getMessage());
        }

        $content = trim($response->getContent());
        $parsed = $this->extractJson($content);
        if ($parsed === null) {
            throw new RuntimeException('AI ตอบกลับในรูปแบบที่อ่านไม่ได้ ลองใหม่อีกครั้ง');
        }

        return $this->normalize($parsed, $board);
    }

    private function systemPrompt(SwotBoard $board): string
    {
        if ($board->isSoar()) {
            return "คุณเป็นที่ปรึกษาการวางแผนกลยุทธ์องค์กร (โรงพยาบาล) ที่เชี่ยวชาญกรอบ SOAR (Strengths, Opportunities, Aspirations, Results) ซึ่งเน้นพลังบวกและการเติบโต (Appreciative Inquiry). "
                . "SOAR ไม่จับคู่ข้ามช่องแบบ TOWS แต่ไหลเป็น roadmap: นำจุดแข็งปัจจุบัน (S) บวกโอกาสภายนอก (O) ขับเคลื่อนสู่ความมุ่งมั่น (A) และวัดผลด้วยผลลัพธ์ (R). "
                . "วิเคราะห์ข้อมูลและตอบกลับเป็นภาษาไทยเท่านั้น เป็น JSON ที่ถูกต้องเพียงอย่างเดียว ห้ามมีข้อความอื่นนอก JSON ห้ามใส่ ```. "
                . "ใช้โครงสร้างนี้พอดี: "
                . '{"summary":"บทสรุปภาพรวม 2-3 ประโยค","healthScore":ตัวเลข 0-100 สะท้อนความพร้อม/พลังบวกเชิงกลยุทธ์,'
                . '"highlights":{"keyStrength":"","majorOpportunity":"","coreAspiration":"","criticalResult":""},'
                . '"recommendations":["ข้อเสนอแนะเชิงปฏิบัติ 3-5 ข้อ"],'
                . '"suggestedStrategies":[{"title":"ชื่อริเริ่มเชิงกลยุทธ์","description":"ใช้จุดแข็ง+โอกาสอย่างไร (S+O)","aspiration":"มุ่งสู่ความมุ่งมั่นใด","metric":"ตัวชี้วัดผลลัพธ์/KPI"}]}. '
                . 'suggestedStrategies คือ "แผนริเริ่ม" ที่แปลง S+O→A→R เป็นการปฏิบัติ (ไม่ใช่การจับคู่ช่อง).';
        }

        return "คุณเป็นที่ปรึกษาการวางแผนกลยุทธ์องค์กร (โรงพยาบาล) ที่เชี่ยวชาญกรอบ SWOT/TOWS (Strengths, Weaknesses, Opportunities, Threats). "
            . "วิเคราะห์ข้อมูลที่ได้รับและตอบกลับเป็นภาษาไทยเท่านั้น. "
            . "ตอบกลับเป็น JSON ที่ถูกต้องเพียงอย่างเดียว ห้ามมีข้อความอื่นนอก JSON ห้ามใส่ ```. "
            . "ใช้โครงสร้างนี้พอดี: "
            . '{"summary":"บทสรุปภาพรวม 2-3 ประโยค","healthScore":ตัวเลข 0-100 สะท้อนความสมดุล/ความพร้อมเชิงกลยุทธ์,'
            . '"highlights":{"keyStrength":"","criticalWeakness":"","majorOpportunity":"","greatestThreat":""},'
            . '"recommendations":["ข้อเสนอแนะเชิงปฏิบัติ 3-5 ข้อ"],'
            . '"suggestedStrategies":[{"cell":"so|wo|st|wt","title":"ชื่อกลยุทธ์ (จับคู่ปัจจัยแบบ TOWS)","description":"อธิบายสั้น"}]}. '
            . 'so=จุดแข็ง×โอกาส, wo=จุดอ่อน×โอกาส, st=จุดแข็ง×อุปสรรค, wt=จุดอ่อน×อุปสรรค.';
    }

    private function userPrompt(SwotBoard $board, array $notesByQuadrant): string
    {
        $info = SwotNote::QUADRANT_INFO;
        $lines = [];
        $lines[] = 'เรื่องที่วิเคราะห์: ' . $board->title;
        if ($board->objective) {
            $lines[] = 'วัตถุประสงค์: ' . $board->objective;
        }
        $lines[] = 'กรอบ: ' . ($board->isSoar() ? 'SOAR' : 'SWOT');
        $lines[] = '';
        $lines[] = 'ประเด็นแต่ละด้าน (ตัวเลขในวงเล็บคือค่าน้ำหนักความสำคัญ 1-5):';
        foreach ($board->quadrants() as $q) {
            $lines[] = "[{$info[$q]['code']}] {$info[$q]['short']}:";
            $notes = $notesByQuadrant[$q] ?? [];
            if (!$notes) {
                $lines[] = '  - (ไม่มี)';
                continue;
            }
            foreach ($notes as $n) {
                $cat = $n->category ? " (หมวด: {$n->category})" : '';
                $lines[] = "  - {$n->content} [{$n->weight}]{$cat}";
            }
        }
        return implode("\n", $lines);
    }

    /** ดึงบล็อก JSON ก้อนแรกจากข้อความ (เผื่อ AI ใส่ข้อความล้อมรอบ) */
    private function extractJson(string $content): ?array
    {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $json = substr($content, $start, $end - $start + 1);
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function normalize(array $p, SwotBoard $board): array
    {
        $h = is_array($p['highlights'] ?? null) ? $p['highlights'] : [];
        $recs = [];
        foreach ((array) ($p['recommendations'] ?? []) as $r) {
            $r = trim((string) $r);
            if ($r !== '') {
                $recs[] = mb_substr($r, 0, 500);
            }
        }
        $sug = [];
        if ($board->isSoar()) {
            // SOAR: แผนริเริ่ม (ไม่มี cell) — เก็บ aspiration/metric
            foreach ((array) ($p['suggestedStrategies'] ?? []) as $s) {
                $title = trim((string) ($s['title'] ?? ''));
                if ($title === '') {
                    continue;
                }
                $sug[] = [
                    'title' => mb_substr($title, 0, 300),
                    'description' => mb_substr(trim((string) ($s['description'] ?? '')), 0, 600),
                    'aspiration' => mb_substr(trim((string) ($s['aspiration'] ?? '')), 0, 300),
                    'metric' => mb_substr(trim((string) ($s['metric'] ?? '')), 0, 300),
                ];
            }
        } else {
            // SWOT/TOWS: ต้องมี cell จับคู่
            $cells = ['so', 'wo', 'st', 'wt'];
            foreach ((array) ($p['suggestedStrategies'] ?? []) as $s) {
                $cell = strtolower((string) ($s['cell'] ?? ''));
                $title = trim((string) ($s['title'] ?? ''));
                if ($title === '' || !in_array($cell, $cells, true)) {
                    continue;
                }
                $sug[] = [
                    'cell' => $cell,
                    'title' => mb_substr($title, 0, 300),
                    'description' => mb_substr(trim((string) ($s['description'] ?? '')), 0, 600),
                ];
            }
        }

        $score = (int) round((float) ($p['healthScore'] ?? 0));
        $score = max(0, min(100, $score));

        return [
            'summary' => mb_substr(trim((string) ($p['summary'] ?? '')), 0, 2000),
            'healthScore' => $score,
            'highlights' => [
                'keyStrength' => (string) ($h['keyStrength'] ?? ''),
                'criticalWeakness' => (string) ($h['criticalWeakness'] ?? ''),
                'majorOpportunity' => (string) ($h['majorOpportunity'] ?? ''),
                'greatestThreat' => (string) ($h['greatestThreat'] ?? ''),
                'coreAspiration' => (string) ($h['coreAspiration'] ?? ''),
                'criticalResult' => (string) ($h['criticalResult'] ?? ''),
            ],
            'recommendations' => $recs,
            'suggestedStrategies' => $sug,
            'analyzedAt' => date('Y-m-d H:i:s'),
        ];
    }
}
