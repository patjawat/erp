<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanInvestment;
use app\modules\plan\components\PlanHelper;

/**
 * แผนการลงทุนด้วยเงินบำรุง — ตรงแบบฟอร์มเขต (cfomoph.com/monthlycash) เมนู 1.3 แผนลงทุน 1 ปี / 1.4 แผนลงทุน 3 ปี
 *
 * ชุดแผนผูกกับปีเริ่ม (plan_year) เดียวกับหน้าแผนประจำปี: แผน 1 ปี = ปี plan_year, แผน 3 ปี = plan_year..plan_year+2
 */
class InvestmentController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'delete' => ['POST'], 'copy-prev' => ['POST']],
            ],
        ];
    }

    /** @param string $tab '1' = แผนลงทุน 1 ปี, '3' = แผนลงทุน 3 ปี */
    public function actionIndex($year = null, $tab = '1')
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $tab = $tab === '3' ? '3' : '1';

        $items = $this->loadItems($year, $tab);
        $groups = $this->groupByType($items);

        // สรุปวงเงินตามแหล่งเงิน: 1 ปี = เงินปีแรก, 3 ปี = รวม 3 ปี
        $amountOf = fn (PlanInvestment $it) => $tab === '1' ? $it->amount(1) : $it->totalAmount();
        $bySource = array_fill_keys(array_keys(PlanInvestment::sources()), 0.0);
        $total = 0.0;
        foreach ($items as $it) {
            $a = $amountOf($it);
            $bySource[$it->source] += $a;
            $total += $a;
        }

        $prevCount = (int) PlanInvestment::find()
            ->where(['plan_year' => $year - 1])
            ->andWhere(['or', ['>', 'qty_y2', 0], ['>', 'qty_y3', 0]])
            ->count();

        return $this->render('index', [
            'year' => $year,
            'tab' => $tab,
            'groups' => $groups,
            'count' => count($items),
            'total' => $total,
            'bySource' => $bySource,
            'hasAny' => PlanInvestment::find()->where(['plan_year' => $year])->exists(),
            'prevCount' => $prevCount,
        ]);
    }

    /** เพิ่ม/แก้ไขรายการ — แท็บ 1 ปี แก้เฉพาะจำนวนปีแรก (ไม่ทับจำนวนปีที่ 2-3 ที่กรอกไว้ในแท็บ 3 ปี) */
    public function actionSave()
    {
        $post = Yii::$app->request->post('PlanInvestment', []);
        $tab = Yii::$app->request->post('tab') === '3' ? '3' : '1';
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? $this->findModel($id) : new PlanInvestment();

        $num = fn ($v) => (float) str_replace([',', ' '], '', (string) $v);
        $model->plan_year = (int) ($post['plan_year'] ?? $model->plan_year ?? PlanHelper::currentPlanYear());
        $model->budget_type = (string) ($post['budget_type'] ?? '');
        $model->name = trim((string) ($post['name'] ?? ''));
        $model->unit = trim((string) ($post['unit'] ?? '')) ?: null;
        $model->unit_price = $num($post['unit_price'] ?? 0);
        $model->qty_y1 = $num($post['qty_y1'] ?? 0);
        if ($tab === '3') {
            $model->qty_y2 = $num($post['qty_y2'] ?? 0);
            $model->qty_y3 = $num($post['qty_y3'] ?? 0);
        }
        $model->source = (string) ($post['source'] ?? PlanInvestment::SOURCE_MAINTENANCE);
        $model->policy = (int) ($post['policy'] ?? 8);
        $model->note = trim((string) ($post['note'] ?? '')) ?: null;
        if ($model->isNewRecord) {
            $model->sort_order = (int) PlanInvestment::find()->where(['plan_year' => $model->plan_year])->max('sort_order') + 1;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกรายการลงทุน "' . $model->name . '" แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getErrorSummary(true)));
        }
        return $this->redirect(['index', 'year' => $model->plan_year, 'tab' => $tab]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $tab = Yii::$app->request->post('tab') === '3' ? '3' : '1';
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการ "' . $model->name . '" แล้ว');
        return $this->redirect(['index', 'year' => $model->plan_year, 'tab' => $tab]);
    }

    /**
     * ตั้งต้นแผนปีนี้จากแผน 3 ปีของปีก่อน (แผนต่อเนื่อง): ปีที่ 2 เดิม → ปีที่ 1, ปีที่ 3 เดิม → ปีที่ 2
     * ทำได้เฉพาะเมื่อชุดแผนปีนี้ยังว่าง กันรายการซ้ำ
     */
    public function actionCopyPrev($year)
    {
        $year = (int) $year;
        if (PlanInvestment::find()->where(['plan_year' => $year])->exists()) {
            Yii::$app->session->setFlash('warning', "แผนลงทุนปี $year มีรายการอยู่แล้ว — คัดลอกได้เฉพาะเมื่อยังว่าง");
            return $this->redirect(['index', 'year' => $year, 'tab' => '3']);
        }
        $n = 0;
        $prev = PlanInvestment::find()->where(['plan_year' => $year - 1])
            ->andWhere(['or', ['>', 'qty_y2', 0], ['>', 'qty_y3', 0]])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($prev as $p) {
            $copy = new PlanInvestment($p->getAttributes(['budget_type', 'name', 'unit', 'unit_price', 'source', 'policy', 'note']));
            $copy->plan_year = $year;
            $copy->qty_y1 = $p->qty_y2;
            $copy->qty_y2 = $p->qty_y3;
            $copy->qty_y3 = 0;
            $copy->sort_order = ++$n;
            $copy->save(false);
        }
        Yii::$app->session->setFlash('success', "คัดลอกจากแผนลงทุนปี " . ($year - 1) . " แล้ว $n รายการ — ตรวจราคาและจำนวนปีที่ 3 อีกครั้ง");
        return $this->redirect(['index', 'year' => $year, 'tab' => '3']);
    }

    /**
     * ส่งออก Excel ตามแบบฟอร์มเขต — แท็บ 1 ปี / 3 ปี (หัวตาราง กลุ่มประเภทงบ รวมย่อย รวมทั้งสิ้น ช่องลงนาม)
     * คอลัมน์ "เป็นเงิน" และแถวรวมเป็นสูตร แก้จำนวน/ราคาในไฟล์แล้วยอดตามทันที
     */
    public function actionExcel($year = null, $tab = '3')
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $tab = $tab === '1' ? '1' : '3';
        $is3 = $tab === '3';
        $groups = $this->groupByType($this->loadItems($year, $tab));
        $types = PlanInvestment::types();
        $sources = PlanInvestment::sources();
        $policies = PlanInvestment::policies();
        $hospital = (string) (\app\components\SiteHelper::getInfo()['company_name'] ?? '');

        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $book->getDefaultStyle()->getFont()->setName('TH SarabunPSK')->setSize(14);
        $s = $book->getActiveSheet();
        $s->setTitle($is3 ? 'แผนลงทุน 3 ปี' : 'แผนลงทุน 1 ปี');

        // คอลัมน์ 3 ปี: A ลำดับ B รายการ C หน่วย D ราคา E-L (จำนวน/เงิน x 4) M แหล่งเงิน N นโยบาย
        // คอลัมน์ 1 ปี: A ลำดับ B รายการ C ราคา D จำนวน E หน่วย F รวมเงิน G แหล่งเงิน H ประเภทงบ I นโยบาย
        $last = $is3 ? 'N' : 'I';
        $yearText = $is3 ? "{$year}–" . ($year + 2) : (string) $year;
        $s->setCellValue('A1', 'แบบฟอร์ม แผนการลงทุนด้วยเงินบำรุง ' . ($is3 ? '3' : '1') . " ปี ปีงบประมาณ {$yearText}");
        $s->setCellValue('A2', 'ตามนโยบายการลงทุน Environment, Modernization And Smart Service : EMS');
        foreach ([1, 2, 3] as $r) {
            $s->mergeCells("A{$r}:{$last}{$r}");
            $s->getStyle("A{$r}")->getAlignment()->setHorizontal('center');
        }
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // หัวตาราง
        $h1 = 5;
        if ($is3) {
            $h2 = 6;
            foreach (['A' => 'ลำดับ', 'B' => 'รายการ', 'C' => 'หน่วยนับ', 'D' => 'ราคาต่อหน่วย', 'M' => 'แหล่งเงิน', 'N' => 'สอดคล้องนโยบายด้านใด'] as $c => $label) {
                $s->setCellValue("{$c}{$h1}", $label);
                $s->mergeCells("{$c}{$h1}:{$c}{$h2}");
            }
            $yearLabels = ["ปีงบประมาณ {$year}", 'ปีงบประมาณ ' . ($year + 1), 'ปีงบประมาณ ' . ($year + 2), "รวม {$yearText}"];
            foreach ([['E', 'F'], ['G', 'H'], ['I', 'J'], ['K', 'L']] as $k => [$qc, $ac]) {
                $s->setCellValue("{$qc}{$h1}", $yearLabels[$k]);
                $s->mergeCells("{$qc}{$h1}:{$ac}{$h1}");
                $s->setCellValue("{$qc}{$h2}", 'จำนวน');
                $s->setCellValue("{$ac}{$h2}", 'เป็นเงิน');
            }
        } else {
            $h2 = $h1;
            foreach (['A' => 'ลำดับ', 'B' => 'รายการ', 'C' => 'ราคาต่อหน่วย (บาท)', 'D' => 'จำนวนหน่วย', 'E' => 'หน่วยนับ', 'F' => 'รวมเป็นเงิน (บาท)', 'G' => 'แหล่งเงิน', 'H' => 'ประเภทงบ', 'I' => 'สอดคล้องนโยบายด้านใด'] as $c => $label) {
                $s->setCellValue("{$c}{$h1}", $label);
            }
        }
        $s->getStyle("A{$h1}:{$last}{$h2}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9ECEF']],
        ]);

        // เนื้อหา
        $r = $h2 + 1;
        $first = $r;
        $no = 0;
        $subRows = [];
        $sumCols = $is3 ? ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] : ['F'];
        foreach ($groups as $type => $items) {
            if (!$items) {
                continue;
            }
            $s->setCellValue("B{$r}", $types[$type] ?? $type);
            $s->getStyle("A{$r}:{$last}{$r}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F1F3F5']],
            ]);
            $r++;
            $gFirst = $r;
            foreach ($items as $it) {
                $no++;
                $s->setCellValue("A{$r}", $no);
                $s->setCellValue("B{$r}", $it->name . ($it->note ? "\n" . $it->note : ''));
                if ($is3) {
                    $s->setCellValue("C{$r}", (string) $it->unit);
                    $s->setCellValue("D{$r}", (float) $it->unit_price);
                    foreach ([1 => ['E', 'F'], 2 => ['G', 'H'], 3 => ['I', 'J']] as $i => [$qc, $ac]) {
                        if ($it->qty($i)) {
                            $s->setCellValue("{$qc}{$r}", $it->qty($i));
                            $s->setCellValue("{$ac}{$r}", "={$qc}{$r}*\$D{$r}");
                        }
                    }
                    $s->setCellValue("K{$r}", "=SUM(E{$r},G{$r},I{$r})");
                    $s->setCellValue("L{$r}", "=SUM(F{$r},H{$r},J{$r})");
                    $s->setCellValue("M{$r}", $sources[$it->source] ?? $it->source);
                    $s->setCellValue("N{$r}", $policies[$it->policy] ?? '');
                } else {
                    $s->setCellValue("C{$r}", (float) $it->unit_price);
                    $s->setCellValue("D{$r}", $it->qty(1));
                    $s->setCellValue("E{$r}", (string) $it->unit);
                    $s->setCellValue("F{$r}", "=C{$r}*D{$r}");
                    $s->setCellValue("G{$r}", $sources[$it->source] ?? $it->source);
                    $s->setCellValue("H{$r}", $types[$it->budget_type] ?? '');
                    $s->setCellValue("I{$r}", $policies[$it->policy] ?? '');
                }
                $r++;
            }
            $gLast = $r - 1;
            $s->setCellValue("B{$r}", 'รวม' . ($types[$type] ?? $type));
            foreach ($sumCols as $c) {
                $s->setCellValue("{$c}{$r}", "=SUM({$c}{$gFirst}:{$c}{$gLast})");
            }
            $s->getStyle("A{$r}:{$last}{$r}")->getFont()->setBold(true);
            $s->getStyle("B{$r}")->getAlignment()->setHorizontal('right');
            $subRows[] = $r;
            $r++;
        }
        if ($no === 0) {
            $s->setCellValue("B{$r}", 'ยังไม่มีรายการแผนลงทุน');
            $r++;
        }

        // รวมทั้งสิ้น = ผลรวมแถวรวมย่อย
        $s->setCellValue("B{$r}", 'รวมทั้งสิ้น');
        foreach ($sumCols as $c) {
            $s->setCellValue("{$c}{$r}", $subRows ? '=' . implode('+', array_map(fn ($sr) => $c . $sr, $subRows)) : 0);
        }
        $s->getStyle("A{$r}:{$last}{$r}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CFE2FF']],
        ]);
        $s->getStyle("B{$r}")->getAlignment()->setHorizontal('right');
        $tableLast = $r;

        // หัวกระดาษบรรทัด 3 อ้างเซลล์รวมทั้งสิ้น
        $totalCell = ($is3 ? 'L' : 'F') . $tableLast;
        $s->setCellValue('A3', '="หน่วยบริการ: ' . str_replace('"', '""', $hospital ?: '...................')
            . " • วงเงินลงทุนปีงบประมาณ {$yearText}: \"&TEXT({$totalCell},\"#,##0.00\")&\" บาท\"");

        // รูปแบบตาราง
        $s->getStyle("A{$h1}:{$last}{$tableLast}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        $s->getStyle("A{$first}:{$last}{$tableLast}")->getAlignment()->setVertical('top');
        $s->getStyle("B{$first}:B{$tableLast}")->getAlignment()->setWrapText(true);
        $s->getStyle("{$last}{$first}:{$last}{$tableLast}")->getAlignment()->setWrapText(true);
        $s->getStyle("A{$first}:A{$tableLast}")->getAlignment()->setHorizontal('center');
        foreach ($is3 ? ['D', 'F', 'H', 'J', 'L'] : ['C', 'F'] as $c) {
            $s->getStyle("{$c}{$first}:{$c}{$tableLast}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        foreach ($is3 ? ['E', 'G', 'I', 'K'] : ['D'] as $c) {
            $s->getStyle("{$c}{$first}:{$c}{$tableLast}")->getNumberFormat()->setFormatCode('#,##0');
        }
        $widths = $is3
            ? ['A' => 7, 'B' => 40, 'C' => 10, 'D' => 14, 'E' => 8, 'F' => 14, 'G' => 8, 'H' => 14, 'I' => 8, 'J' => 14, 'K' => 8, 'L' => 15, 'M' => 12, 'N' => 36]
            : ['A' => 7, 'B' => 42, 'C' => 15, 'D' => 10, 'E' => 10, 'F' => 16, 'G' => 12, 'H' => 12, 'I' => 38];
        foreach ($widths as $c => $w) {
            $s->getColumnDimension($c)->setWidth($w);
        }

        // หมายเหตุนโยบาย + ช่องลงนาม
        $r = $tableLast + 2;
        $s->setCellValue("B{$r}", '** ให้เลือกระบุนโยบายดังนี้:');
        $s->getStyle("B{$r}")->getFont()->setBold(true);
        foreach (array_values($policies) as $k => $pl) {
            $s->setCellValue('B' . ($r + 1 + $k), $pl);
        }
        $signCol = $is3 ? 'J' : 'F';
        foreach (['ผู้จัดทำ', 'ผู้อำนวยการ', 'นายแพทย์สาธารณสุขจังหวัด'] as $k => $role) {
            $s->setCellValue($signCol . ($r + 1 + $k * 2), $role . '.......................................................');
        }

        // A4 แนวนอน พอดีความกว้าง หัวตารางซ้ำทุกหน้า
        $ps = $s->getPageSetup();
        $ps->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $ps->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $ps->setFitToWidth(1)->setFitToHeight(0);
        $ps->setRowsToRepeatAtTopByStartAndEnd($h1, $h2);
        $s->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.5)->setBottom(0.5);
        $s->freezePane('C' . ($h2 + 1));

        $name = ($is3 ? "แผนลงทุน3ปี_{$year}-" . ($year + 2) : "แผนลงทุน1ปี_{$year}") . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'planinv');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($tmp);

        $response = Yii::$app->response;
        $response->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
        return $response->sendFile($tmp, $name);
    }

    /** รายการในชุดแผนที่เริ่ม $year — แท็บ 1 ปี เอาเฉพาะที่มีจำนวนปีแรก */
    protected function loadItems(int $year, string $tab): array
    {
        $query = PlanInvestment::find()->where(['plan_year' => $year]);
        if ($tab === '1') {
            $query->andWhere(['>', 'qty_y1', 0]);
        }
        return $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
    }

    /** แยกกลุ่มตามประเภทงบ (ครุภัณฑ์ก่อน สิ่งก่อสร้างทีหลัง เหมือนแม่แบบเขต) */
    private function groupByType(array $items): array
    {
        $groups = array_fill_keys(array_keys(PlanInvestment::types()), []);
        foreach ($items as $it) {
            $groups[$it->budget_type][] = $it;
        }
        return $groups;
    }

    private function findModel($id): PlanInvestment
    {
        $m = PlanInvestment::findOne((int) $id);
        if (!$m) {
            throw new NotFoundHttpException('ไม่พบรายการลงทุน');
        }
        return $m;
    }
}
