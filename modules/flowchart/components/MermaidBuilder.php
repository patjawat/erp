<?php

namespace app\modules\flowchart\components;

use app\modules\flowchart\models\Flowchart;
use app\modules\flowchart\models\FlowchartStep;

/**
 * แปลง "รายการขั้นตอน" ของผังกระบวนการ ให้เป็นไวยากรณ์ Mermaid (flowchart)
 * โดยจัดวาง/ลากเส้นให้อัตโนมัติ ผู้ใช้ไม่ต้องวาดเอง
 *
 * การไหลของเส้น:
 *  - step ทั่วไป   -> ลงขั้นถัดไปตามลำดับ
 *  - step decision -> 2 เส้นตาม branch_yes / branch_no (อ้าง seq ปลายทาง) พร้อม label ใช่/ไม่
 * สีของกล่องแยกตามผู้รับผิดชอบ (actor) เพื่อสื่อ "ใครทำอะไร" โดยไม่ต้องเป็น swimlane
 */
class MermaidBuilder
{
    /** จานสีสำหรับแยกตามผู้รับผิดชอบ (พาสเทล ตัวอักษรเข้ม) */
    public const ACTOR_PALETTE = [
        ['fill' => '#E0F2FE', 'stroke' => '#0284C7', 'text' => '#0C4A6E'],
        ['fill' => '#DCFCE7', 'stroke' => '#16A34A', 'text' => '#14532D'],
        ['fill' => '#FEF3C7', 'stroke' => '#D97706', 'text' => '#78350F'],
        ['fill' => '#FCE7F3', 'stroke' => '#DB2777', 'text' => '#831843'],
        ['fill' => '#F3E8FF', 'stroke' => '#9333EA', 'text' => '#581C87'],
        ['fill' => '#CFFAFE', 'stroke' => '#0891B2', 'text' => '#164E63'],
        ['fill' => '#FFEDD5', 'stroke' => '#EA580C', 'text' => '#7C2D12'],
        ['fill' => '#E2E8F0', 'stroke' => '#475569', 'text' => '#1E293B'],
    ];

    /**
     * แม็ปผู้รับผิดชอบ -> สีในจาน (ไล่ตามลำดับที่พบครั้งแรก)
     * @param FlowchartStep[] $steps
     * @return array<string,array{fill:string,stroke:string,text:string}>
     */
    public static function actorColorMap(array $steps): array
    {
        $map = [];
        $i = 0;
        foreach ($steps as $s) {
            $actor = trim((string) $s->actor);
            if ($actor === '' || isset($map[$actor])) {
                continue;
            }
            $map[$actor] = self::ACTOR_PALETTE[$i % count(self::ACTOR_PALETTE)];
            $i++;
        }
        return $map;
    }

    /** สร้างข้อความ Mermaid ทั้งบล็อกจากผัง */
    public static function build(Flowchart $fc): string
    {
        /** @var FlowchartStep[] $steps */
        $steps = $fc->steps;
        $dir = $fc->diagram_dir === Flowchart::DIR_LR ? 'LR' : 'TD';

        if (empty($steps)) {
            return "flowchart {$dir}\n  empty[\"ยังไม่มีขั้นตอน\"]";
        }

        $lines = ["flowchart {$dir}"];

        // 1) นิยามกล่องทั้งหมด (รูปทรงตามประเภท)
        foreach ($steps as $s) {
            $info = $s->info();
            $label = self::escapeLabel($s->displayTitle());
            $lines[] = '  ' . $s->nodeKey() . $info['open'] . '"' . $label . '"' . $info['close'];
        }

        // 2) เส้นเชื่อม
        $bySeq = [];
        foreach ($steps as $s) {
            $bySeq[(int) $s->seq] = $s;
        }
        $count = count($steps);
        foreach ($steps as $idx => $s) {
            if ($s->isTerminal()) {
                continue;
            }
            if ($s->isDecision()) {
                if ($s->branch_yes !== null && isset($bySeq[(int) $s->branch_yes])) {
                    $lines[] = '  ' . $s->nodeKey() . ' -->|ใช่| ' . $bySeq[(int) $s->branch_yes]->nodeKey();
                }
                if ($s->branch_no !== null && isset($bySeq[(int) $s->branch_no])) {
                    $lines[] = '  ' . $s->nodeKey() . ' -->|ไม่| ' . $bySeq[(int) $s->branch_no]->nodeKey();
                }
                continue;
            }
            // step ทั่วไป -> ขั้นถัดไปตามลำดับ
            if ($idx + 1 < $count) {
                $lines[] = '  ' . $s->nodeKey() . ' --> ' . $steps[$idx + 1]->nodeKey();
            }
        }

        // 3) แยกสีตามผู้รับผิดชอบ
        $colorMap = self::actorColorMap($steps);
        if ($colorMap) {
            $actorIndex = array_flip(array_keys($colorMap)); // actor => ลำดับ
            $classKeys = [];                                  // idx => [nodeKey,...]
            foreach ($steps as $s) {
                $actor = trim((string) $s->actor);
                if ($actor === '' || !isset($actorIndex[$actor])) {
                    continue;
                }
                $classKeys[$actorIndex[$actor]][] = $s->nodeKey();
            }
            foreach ($colorMap as $actor => $c) {
                $i = $actorIndex[$actor];
                $lines[] = "  classDef actor{$i} fill:{$c['fill']},stroke:{$c['stroke']},color:{$c['text']},stroke-width:1px;";
            }
            foreach ($classKeys as $i => $keys) {
                $lines[] = '  class ' . implode(',', $keys) . " actor{$i};";
            }
        }

        return implode("\n", $lines);
    }

    /** ทำ label ให้ปลอดภัยในไวยากรณ์ Mermaid (อยู่ในเครื่องหมายคำพูด) */
    private static function escapeLabel(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\n"], '<br/>', $text);
        $text = str_replace('"', '#quot;', $text);
        return trim($text);
    }
}
