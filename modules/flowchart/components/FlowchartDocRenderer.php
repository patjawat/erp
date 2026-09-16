<?php

namespace app\modules\flowchart\components;

use app\modules\flowchart\models\Flowchart;
use app\modules\flowchart\models\FlowchartStep;
use yii\helpers\Html;

/**
 * แปลงรายการขั้นตอนให้เป็น "ตารางกระบวนการ" (แบบเอกสาร SOP/HA) แบบ HTML ล้วน
 * พิมพ์ผ่านเบราว์เซอร์ได้ทันที และเผื่อฝังในเอกสารโมดูลอื่นในเฟสถัดไป
 */
class FlowchartDocRenderer
{
    /** SVG สัญลักษณ์เล็กประจำรูปทรง (ใช้ currentColor เพื่อรองรับ light/dark) */
    public static function symbolSvg(string $type): string
    {
        $shape = FlowchartStep::TYPE_INFO[$type]['shape'] ?? 'rect';
        $attr = 'width="30" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="1.4"';
        switch ($shape) {
            case 'stadium':
                $body = '<rect x="2" y="4" width="26" height="12" rx="6"/>';
                break;
            case 'diamond':
                $body = '<path d="M15 3 L27 10 L15 17 L3 10 Z"/>';
                break;
            case 'doc':
                $body = '<path d="M4 4 H26 V14 C22 17 19 11 15 14 C11 17 8 11 4 14 Z"/>';
                break;
            case 'subroutine':
                $body = '<rect x="2" y="4" width="26" height="12"/><line x1="6" y1="4" x2="6" y2="16"/><line x1="24" y1="4" x2="24" y2="16"/>';
                break;
            case 'rect':
            default:
                $body = '<rect x="2" y="4" width="26" height="12" rx="1.5"/>';
                break;
        }
        return "<svg {$attr} aria-hidden=\"true\">{$body}</svg>";
    }

    /** สร้างตารางกระบวนการทั้งชุดเป็น HTML */
    public static function procedureTableHtml(Flowchart $fc): string
    {
        /** @var FlowchartStep[] $steps */
        $steps = $fc->steps;

        $bySeq = [];
        foreach ($steps as $s) {
            $bySeq[(int) $s->seq] = $s;
        }

        $head = '<thead><tr>'
            . '<th class="text-center" style="width:70px;">สัญลักษณ์</th>'
            . '<th class="text-center" style="width:52px;">ลำดับ</th>'
            . '<th>ขั้นตอน</th>'
            . '<th style="width:22%;">ผู้รับผิดชอบ</th>'
            . '<th style="width:22%;">เอกสาร / ระยะเวลา</th>'
            . '</tr></thead>';

        if (empty($steps)) {
            return '<table class="table table-bordered align-middle fc-proc-table mb-0">' . $head
                . '<tbody><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีขั้นตอน</td></tr></tbody></table>';
        }

        $rows = '';
        foreach ($steps as $s) {
            $branch = '';
            if ($s->isDecision()) {
                $parts = [];
                if ($s->branch_yes !== null && isset($bySeq[(int) $s->branch_yes])) {
                    $parts[] = 'ใช่ → ขั้น ' . (int) $s->branch_yes;
                }
                if ($s->branch_no !== null && isset($bySeq[(int) $s->branch_no])) {
                    $parts[] = 'ไม่ → ขั้น ' . (int) $s->branch_no;
                }
                if ($parts) {
                    $branch = '<div class="small text-muted mt-1"><i class="bi bi-arrow-return-right"></i> '
                        . Html::encode(implode('  ·  ', $parts)) . '</div>';
                }
            }

            $note = trim((string) $s->note) !== ''
                ? '<div class="small text-muted mt-1">' . Html::encode($s->note) . '</div>'
                : '';

            $docDuration = array_filter([
                trim((string) $s->related_doc) !== '' ? Html::encode($s->related_doc) : '',
                trim((string) $s->duration) !== '' ? '<span class="text-muted">' . Html::encode($s->duration) . '</span>' : '',
            ]);

            $rows .= '<tr>'
                . '<td class="text-center fc-sym" title="' . Html::encode($s->typeLabel()) . '">' . self::symbolSvg($s->type) . '</td>'
                . '<td class="text-center fw-semibold">' . (int) $s->seq . '</td>'
                . '<td>' . nl2br(Html::encode($s->displayTitle())) . $branch . $note . '</td>'
                . '<td>' . (trim((string) $s->actor) !== '' ? Html::encode($s->actor) : '<span class="text-muted">—</span>') . '</td>'
                . '<td>' . ($docDuration ? implode('<br>', $docDuration) : '<span class="text-muted">—</span>') . '</td>'
                . '</tr>';
        }

        return '<table class="table table-bordered align-middle fc-proc-table mb-0">'
            . $head . '<tbody>' . $rows . '</tbody></table>';
    }
}
