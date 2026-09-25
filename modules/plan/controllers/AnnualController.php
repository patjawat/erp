<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanAnnualLedger;
use app\modules\plan\models\PlanAnnualAttachment;
use app\modules\plan\components\PlanHelper;
use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashPlan;
use app\modules\finance\models\FinanceCashTxn;

/**
 * แผนประจำปี (รับ-จ่าย) — ย้ายมาจาก finance/cash
 *
 * แนวคิด (ตามที่ตกลงกับผู้ใช้):
 *  - รายรับ: คีย์เองรายปี เก็บใน finance_cash_plan (IN); ผลจริงย้อนหลังมาจาก finance_cash_txn (IN)
 *  - รายจ่าย: read-only ดึงจากแผนรายจ่าย (plan_order via overviewByType) ทุกปี — ปีก่อนที่ยังว่าง
 *             ผู้ใช้กรอกผ่านเมนูแผนรายจ่ายเดิม ไม่คีย์ซ้ำที่นี่
 *  - แผน-ผล: รายรับเทียบรายหมวด (ผังเดียวกัน); รายจ่ายเทียบระดับยอดรวม (ผังคนละชุด)
 *  - ไม่มี migration — ใช้ตารางเดิมทั้งหมด
 */
class AnnualController extends Controller
{
    private const EXPENSE_TYPE_ORDER = ['PER', 'OPS', 'INV', 'OTH'];

    /** แผนประจำปี — ตารางรวมย้อนหลัง 3 ปี + แผน 3 ปี + บล็อกสภาพคล่อง + เทียบแผน-ผลปีปัจจุบัน */
    public function actionIndex($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $matrix = $this->matrix($year);
        $cmpYear = (int) FinanceCashTxn::currentFiscalYear();
        $allYears = array_merge($matrix['actualYears'], $matrix['planYears']);

        return $this->render('index', $matrix + [
            'year' => $year,
            'cmpYear' => $cmpYear,
            'compare' => $this->compare($cmpYear),
            'liquidity' => $this->liquidity($allYears, $matrix['incomeTot'], $matrix['expenseTot']),
        ]);
    }

    /** หน้ากรอกข้อมูลสภาพคล่อง: เงินคงเหลือยกมา/แยกประเภท — แนบ 1/2 ย้ายไปหน้า commitment (บรรทัดตามแบบฟอร์มเขต) */
    public function actionLiquidity($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());

        return $this->render('liquidity', [
            'year' => $year,
            'ledger' => PlanAnnualLedger::forYear($year),
            'reserveSum' => PlanAnnualAttachment::sumByYear(PlanAnnualAttachment::KIND_RESERVE, [$year])[$year],
            'commitmentSum' => PlanAnnualAttachment::sumByYear(PlanAnnualAttachment::KIND_COMMITMENT, [$year])[$year],
        ]);
    }

    /**
     * ภาระผูกพัน & รอจัดสรร (เมนู 1.5 ของเขต): แนบ 1 กองทุนรอจัดสรร (4) + แนบ 2 ภาระผูกพัน (5)
     * บรรทัดตายตัวตามแบบฟอร์มเขต กรอก 3 ปีคู่กัน ($year..$year+2)
     */
    public function actionCommitment($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $years = [$year, $year + 1, $year + 2];

        $amounts = [];   // [kind][code][fy] => amount
        $legacy = [];    // [kind] => PlanAnnualAttachment[] (รายการเดิมนอกแบบฟอร์ม)
        foreach (PlanAnnualAttachment::find()->where(['fiscal_year' => $years])
            ->orderBy(['fiscal_year' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])->all() as $r) {
            if ($r->line_code === null || $r->line_code === '') {
                $legacy[$r->kind][] = $r;
            } else {
                $amounts[$r->kind][$r->line_code][(int) $r->fiscal_year] = ($amounts[$r->kind][$r->line_code][(int) $r->fiscal_year] ?? 0) + (float) $r->amount;
            }
        }

        return $this->render('commitment', [
            'year' => $year,
            'years' => $years,
            'amounts' => $amounts,
            'legacy' => $legacy,
        ]);
    }

    /** บันทึกแนบ 1/2 — เขียนทับเฉพาะแถวที่มีรหัสบรรทัด; รายการเดิมย้ายเข้าบรรทัด/ลบ ตามที่ผู้ใช้เลือก */
    public function actionCommitmentSave()
    {
        $post = Yii::$app->request->post();
        $year = (int) ($post['year'] ?? PlanHelper::currentPlanYear());
        $years = [$year, $year + 1, $year + 2];
        $num = fn ($v) => (float) str_replace([',', ' '], '', (string) $v);
        $kinds = [PlanAnnualAttachment::KIND_RESERVE, PlanAnnualAttachment::KIND_COMMITMENT];

        $tx = Yii::$app->db->beginTransaction();
        try {
            // ยอดที่กรอก [kind][code][fy]
            $in = [];
            foreach ($kinds as $kind) {
                foreach (PlanAnnualAttachment::lines($kind) as $code => $_) {
                    foreach ($years as $fy) {
                        $in[$kind][$code][$fy] = $num($post['line'][$kind][$code][$fy] ?? 0);
                    }
                }
            }

            // รายการเดิม: move = รหัสบรรทัด (บวกยอดเข้าบรรทัดนั้น), 'delete' = ลบทิ้ง, ว่าง = คงไว้
            $moved = 0;
            foreach ((array) ($post['legacy'] ?? []) as $id => $action) {
                $action = (string) $action;
                $row = PlanAnnualAttachment::findOne((int) $id);
                if (!$row || $row->line_code || !in_array((int) $row->fiscal_year, $years, true) || $action === '') {
                    continue;
                }
                if ($action !== 'delete') {
                    if (!isset($in[$row->kind][$action])) {
                        continue;
                    }
                    $in[$row->kind][$action][(int) $row->fiscal_year] += (float) $row->amount;
                }
                $row->delete();
                $moved++;
            }

            foreach ($kinds as $kind) {
                PlanAnnualAttachment::deleteAll(['and', ['fiscal_year' => $years, 'kind' => $kind], ['not', ['line_code' => null]]]);
                $i = 0;
                foreach (PlanAnnualAttachment::lines($kind) as $code => [$title]) {
                    $i++;
                    foreach ($years as $fy) {
                        $amt = $in[$kind][$code][$fy];
                        if ($amt == 0) {
                            continue;
                        }
                        $m = new PlanAnnualAttachment([
                            'fiscal_year' => $fy,
                            'kind' => $kind,
                            'line_code' => $code,
                            'name' => $title,
                            'amount' => $amt,
                            'sort_order' => $i,
                        ]);
                        if (!$m->save()) {
                            throw new \RuntimeException(implode(' ', $m->getErrorSummary(true)));
                        }
                    }
                }
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', "บันทึกภาระผูกพัน & รอจัดสรร ปี {$year}–" .($year + 2) . ' แล้ว' . ($moved ? " (จัดการรายการเดิม $moved รายการ)" : ''));
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
        }
        return $this->redirect(['commitment', 'year' => $year]);
    }

    /** บันทึกข้อมูลสภาพคล่อง (ledger) */
    public function actionLiquiditySave()
    {
        $post = Yii::$app->request->post();
        $year = (int) ($post['year'] ?? PlanHelper::currentPlanYear());

        $ledger = PlanAnnualLedger::forYear($year);
        foreach (['carry_forward', 'cash', 'deposit_treasury', 'deposit_fixed', 'deposit_saving', 'deposit_current'] as $f) {
            $ledger->$f = (float) str_replace([',', ' '], '', (string) ($post['ledger'][$f] ?? 0));
        }
        $ledger->note = (string) ($post['ledger']['note'] ?? '') ?: null;
        $ledger->save();

        Yii::$app->session->setFlash('success', "บันทึกข้อมูลสภาพคล่องปี $year แล้ว");
        return $this->redirect(['liquidity', 'year' => $year]);
    }

    /** แผนรายรับ — ฟอร์มคีย์รายรับล่วงหน้า (finance_cash_plan IN) */
    public function actionIncome($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $m = $this->incomeMatrix($year);

        return $this->render('income', [
            'year' => $year,
            'actualYears' => $m['actualYears'],
            'planYears' => $m['planYears'],
            'groups' => $m['groups'],
            'totA' => $m['totA'],
            'totP' => $m['totP'],
        ]);
    }

    /** บันทึกแผนรายรับ -> finance_cash_plan (IN) */
    public function actionIncomeSave()
    {
        $post = Yii::$app->request->post();
        $year = (int) ($post['year'] ?? PlanHelper::currentPlanYear());

        $typeByCat = [];
        foreach (FinanceCashCategory::find()->select(['id', 'txn_type'])->asArray()->all() as $c) {
            $typeByCat[(int) $c['id']] = $c['txn_type'];
        }

        $count = 0;
        foreach ((array) ($post['plan'] ?? []) as $catId => $years) {
            $catId = (int) $catId;
            // รับเฉพาะหมวดฝั่งรายรับ (กันเขียนข้ามฝั่ง)
            if (($typeByCat[$catId] ?? null) !== FinanceCashCategory::TYPE_IN) {
                continue;
            }
            foreach ((array) $years as $fy => $amt) {
                $fy = (int) $fy;
                $amt = (float) str_replace([',', ' '], '', (string) $amt);
                $row = FinanceCashPlan::findOne(['category_id' => $catId, 'fiscal_year' => $fy]);
                if (!$row && $amt <= 0) {
                    continue;
                }
                if (!$row) {
                    $row = new FinanceCashPlan(['category_id' => $catId, 'fiscal_year' => $fy]);
                }
                $row->txn_type = FinanceCashCategory::TYPE_IN;
                $row->amount = $amt;
                if ($row->save()) {
                    $count++;
                }
            }
        }
        Yii::$app->session->setFlash('success', "บันทึกแผนรายรับแล้ว $count รายการ");
        return $this->redirect(['income', 'year' => $year]);
    }

    /** ส่งออก Excel ตารางแผนประจำปี */
    public function actionExcel($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $m = $this->matrix($year);
        $allYears = array_merge($m['actualYears'], $m['planYears']);

        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('แผนรับ-จ่ายประจำปี');
        $s->setCellValue('A1', 'แผนรายรับ-รายจ่ายประจำปี ' . $m['planYears'][0] . '-' . end($m['planYears']));

        $r = 3;
        $s->setCellValue('A' . $r, 'รายการ');
        $col = 'B';
        foreach ($m['actualYears'] as $ay) {
            $s->setCellValue($col . $r, 'ผล ' . $ay);
            $col++;
        }
        foreach ($m['planYears'] as $py) {
            $s->setCellValue($col . $r, 'แผน ' . $py);
            $col++;
        }
        $r++;

        // รายรับ
        $s->setCellValue("A$r", 'รายรับ');
        $r++;
        foreach ($m['groups'] as $g) {
            $s->setCellValue("A$r", $g['name']);
            $r++;
            foreach ($g['rows'] as $row) {
                $s->setCellValue("A$r", '   ' . $row['name']);
                $col = 'B';
                foreach ($allYears as $y) {
                    $s->setCellValue($col . $r, $row['vals'][$y] ?? 0);
                    $col++;
                }
                $r++;
            }
        }
        $s->setCellValue("A$r", 'รวมรายรับ');
        $col = 'B';
        foreach ($allYears as $y) {
            $s->setCellValue($col . $r, $m['incomeTot'][$y] ?? 0);
            $col++;
        }
        $r += 2;

        // รายจ่าย (แตกถึงระดับหมวด)
        $s->setCellValue("A$r", 'รายจ่าย (จากแผนรายจ่าย)');
        $r++;
        foreach ($m['expenseTypes'] as $type) {
            $s->setCellValue("A$r", $type['title']);
            $col = 'B';
            foreach ($allYears as $y) {
                $s->setCellValue($col . $r, $type['vals'][$y] ?? 0);
                $col++;
            }
            $r++;
            foreach ($type['cats'] as $cat) {
                $s->setCellValue("A$r", '   ' . $cat['title']);
                $col = 'B';
                foreach ($allYears as $y) {
                    $s->setCellValue($col . $r, $cat['vals'][$y] ?? 0);
                    $col++;
                }
                $r++;
            }
        }
        $s->setCellValue("A$r", 'รวมรายจ่าย');
        $col = 'B';
        foreach ($allYears as $y) {
            $s->setCellValue($col . $r, $m['expenseTot'][$y] ?? 0);
            $col++;
        }
        $r++;
        $s->setCellValue("A$r", 'รับสูง(ต่ำ)กว่าจ่ายสุทธิ');
        $col = 'B';
        foreach ($allYears as $y) {
            $s->setCellValue($col . $r, $m['net'][$y] ?? 0);
            $col++;
        }

        foreach (range('A', 'G') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }

        $name = 'แผนรับจ่ายประจำปี_' . $year . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'planannual');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($tmp);

        $response = Yii::$app->response;
        $response->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
        return $response->sendFile($tmp, $name);
    }

    // ---- ตัวช่วย ----------------------------------------------------------

    /** โครงตารางรวม: รายรับ (finance) + รายจ่าย (plan_order) + net ต่อปี */
    private function matrix(int $year): array
    {
        $income = $this->incomeMatrix($year);
        $actualYears = $income['actualYears'];
        $planYears = $income['planYears'];
        $allYears = array_merge($actualYears, $planYears);

        // ยอดรายรับต่อปี (รวม actual + plan)
        $incomeTot = [];
        foreach ($actualYears as $ay) {
            $incomeTot[$ay] = $income['totA'][$ay] ?? 0.0;
        }
        foreach ($planYears as $py) {
            $incomeTot[$py] = $income['totP'][$py] ?? 0.0;
        }
        // แปลง groups ของรายรับให้ vals รวม actual+plan ต่อปี (ใช้แสดงตารางเดียว)
        $incomeGroups = [];
        foreach ($income['groups'] as $g) {
            $rows = [];
            foreach ($g['rows'] as $row) {
                $vals = [];
                $valsIsPlan = [];
                foreach ($actualYears as $ay) {
                    $vals[$ay] = $row['actual'][$ay] ?? 0.0;
                    $valsIsPlan[$ay] = !empty($row['actualIsPlan'][$ay]);
                }
                foreach ($planYears as $py) {
                    $vals[$py] = $row['plan'][$py] ?? 0.0;
                }
                $rows[] = ['name' => $row['name'], 'vals' => $vals, 'valsIsPlan' => $valsIsPlan, 'planYears' => $planYears, 'plan' => $row['plan']];
            }
            $sub = [];
            foreach ($allYears as $y) {
                $sub[$y] = ($g['subA'][$y] ?? 0.0) + ($g['subP'][$y] ?? 0.0);
            }
            $incomeGroups[] = ['name' => $g['name'], 'rows' => $rows, 'sub' => $sub];
        }

        // รายจ่ายจาก plan_order (read-only) ทุกปี — แตกถึงระดับหมวด (เช่น 1.1) เหมือนหน้าภาพรวมแผนรายจ่าย
        $expenseTot = array_fill_keys($allYears, 0.0);
        $acc = []; // [tc => ['title','vals'=>[y=>..],'cats'=>[cc=>['title','vals'=>[y=>..]]]]]
        foreach ($allYears as $y) {
            $ov = PlanOrder::overviewByType($y, 'all');
            foreach ((array) ($ov['types'] ?? []) as $tc => $t) {
                if (!isset($acc[$tc])) {
                    $acc[$tc] = ['title' => $t['title'] ?: $tc, 'vals' => array_fill_keys($allYears, 0.0), 'cats' => []];
                }
                $acc[$tc]['vals'][$y] = (float) ($t['sub']['total'] ?? 0);
                foreach ((array) ($t['categories'] ?? []) as $cat) {
                    $cc = (string) $cat['code'];
                    if (!isset($acc[$tc]['cats'][$cc])) {
                        $acc[$tc]['cats'][$cc] = ['title' => $cat['title'], 'vals' => array_fill_keys($allYears, 0.0)];
                    }
                    $acc[$tc]['cats'][$cc]['vals'][$y] = (float) ($cat['total'] ?? 0);
                }
            }
            $expenseTot[$y] = (float) ($ov['grand']['total'] ?? 0);
        }
        // จัดลำดับประเภทตามมาตรฐาน PER/OPS/INV/OTH แล้วตามด้วยที่เหลือ
        $expenseTypes = [];
        $ordered = array_merge(self::EXPENSE_TYPE_ORDER, array_diff(array_keys($acc), self::EXPENSE_TYPE_ORDER));
        foreach ($ordered as $tc) {
            if (!isset($acc[$tc])) {
                continue;
            }
            $expenseTypes[] = [
                'code' => $tc,
                'title' => $acc[$tc]['title'],
                'vals' => $acc[$tc]['vals'],
                'cats' => array_values($acc[$tc]['cats']),
            ];
        }

        // net ต่อปี
        $net = [];
        foreach ($allYears as $y) {
            $net[$y] = ($incomeTot[$y] ?? 0.0) - ($expenseTot[$y] ?? 0.0);
        }

        return [
            'actualYears' => $actualYears,
            'planYears' => $planYears,
            'groups' => $incomeGroups,
            'incomeTot' => $incomeTot,
            'expenseTypes' => $expenseTypes,
            'expenseTot' => $expenseTot,
            'net' => $net,
        ];
    }

    /** ฝั่งรายรับ (TYPE_IN): actual จาก txn, plan จาก finance_cash_plan */
    private function incomeMatrix(int $year): array
    {
        $actualYears = [$year - 3, $year - 2, $year - 1];
        $planYears = [$year, $year + 1, $year + 2];
        $allYears = array_merge($actualYears, $planYears);

        $actual = [];
        foreach (
            FinanceCashTxn::find()->select(['fiscal_year', 'category_id', 's' => 'SUM(amount)'])
                ->where(['fiscal_year' => $actualYears, 'txn_type' => FinanceCashTxn::TYPE_IN])
                ->groupBy(['fiscal_year', 'category_id'])->asArray()->all() as $r
        ) {
            $actual[(int) $r['category_id']][(int) $r['fiscal_year']] = (float) $r['s'];
        }
        // ดึงแผนทุกปี (ย้อนหลัง + ล่วงหน้า) เพื่อใช้เป็น fallback ในคอลัมน์ซ้าย
        // เมื่อปีที่เคยตั้งแผนไว้เลื่อนพ้นหน้าต่างแผน 3 ปี ตัวเลขจะไม่หายไปจากจอ
        $plan = [];
        foreach (
            FinanceCashPlan::find()->where(['fiscal_year' => $allYears, 'txn_type' => FinanceCashCategory::TYPE_IN])
                ->asArray()->all() as $r
        ) {
            $plan[(int) $r['category_id']][(int) $r['fiscal_year']] = (float) $r['amount'];
        }

        $groups = [];
        $totA = array_fill_keys($actualYears, 0.0);
        $totP = array_fill_keys($planYears, 0.0);
        foreach ($this->inTree(FinanceCashCategory::TYPE_IN) as $group) {
            $rows = [];
            $subA = array_fill_keys($actualYears, 0.0);
            $subP = array_fill_keys($planYears, 0.0);
            foreach ($group['rows'] as $leaf) {
                $cid = $leaf['id'];
                $a = [];
                $aIsPlan = [];
                foreach ($actualYears as $ay) {
                    $tx = $actual[$cid][$ay] ?? 0.0;
                    // มีรับจริง = ใช้รับจริง; ไม่มี = ถอยมาแสดงแผนที่เคยตั้งไว้ (ทำเครื่องหมายว่าเป็นแผน)
                    if ($tx > 0) {
                        $a[$ay] = $tx;
                        $aIsPlan[$ay] = false;
                    } else {
                        $pl = $plan[$cid][$ay] ?? 0.0;
                        $a[$ay] = $pl;
                        $aIsPlan[$ay] = $pl > 0;
                    }
                    $subA[$ay] += $a[$ay];
                }
                $p = [];
                foreach ($planYears as $py) {
                    $p[$py] = $plan[$cid][$py] ?? 0.0;
                    $subP[$py] += $p[$py];
                }
                $rows[] = ['id' => $cid, 'name' => $leaf['name'], 'actual' => $a, 'actualIsPlan' => $aIsPlan, 'plan' => $p];
            }
            foreach ($actualYears as $ay) {
                $totA[$ay] += $subA[$ay];
            }
            foreach ($planYears as $py) {
                $totP[$py] += $subP[$py];
            }
            $groups[] = ['name' => $group['name'], 'rows' => $rows, 'subA' => $subA, 'subP' => $subP];
        }

        return compact('actualYears', 'planYears', 'groups', 'totA', 'totP');
    }

    /** โครงหมวด (group -> leaf) ของ type ที่กำหนด */
    private function inTree(string $type): array
    {
        $tree = FinanceCashCategory::treeArray($type);
        $childrenOf = [];
        foreach ($tree as $n) {
            $childrenOf[(int) ($n['parent_id'] ?? 0)][] = $n;
        }
        $leaves = function ($nodeId) use (&$leaves, $childrenOf) {
            $kids = $childrenOf[(int) $nodeId] ?? [];
            if (!$kids) {
                return [];
            }
            $out = [];
            foreach ($kids as $k) {
                $sub = $leaves($k['id']);
                $out = $sub ? array_merge($out, $sub) : array_merge($out, [$k]);
            }
            return $out;
        };
        $groups = [];
        foreach ($childrenOf[0] ?? [] as $group) {
            $rows = [];
            foreach ($leaves($group['id']) as $leaf) {
                $rows[] = ['id' => (int) $leaf['id'], 'name' => $leaf['name']];
            }
            $groups[] = ['name' => $group['name'], 'rows' => $rows];
        }
        return $groups;
    }

    /** เทียบแผน-ผลปีปัจจุบัน: รายรับรายหมวด + รายจ่ายระดับยอดรวม */
    private function compare(int $fy): array
    {
        // รายรับ: แผน (finance_cash_plan IN) vs รับจริง (finance_cash_txn IN) รายหมวด
        $planIn = [];
        foreach (
            FinanceCashPlan::find()->where(['fiscal_year' => $fy, 'txn_type' => FinanceCashCategory::TYPE_IN])
                ->asArray()->all() as $r
        ) {
            $planIn[(int) $r['category_id']] = (float) $r['amount'];
        }
        $actualIn = [];
        foreach (
            FinanceCashTxn::find()->select(['category_id', 's' => 'SUM(amount)'])
                ->where(['fiscal_year' => $fy, 'txn_type' => FinanceCashTxn::TYPE_IN])
                ->groupBy(['category_id'])->asArray()->all() as $r
        ) {
            $actualIn[(int) $r['category_id']] = (float) $r['s'];
        }
        $incomeRows = [];
        $incPlan = $incActual = 0.0;
        foreach ($this->inTree(FinanceCashCategory::TYPE_IN) as $group) {
            foreach ($group['rows'] as $leaf) {
                $cid = $leaf['id'];
                $p = $planIn[$cid] ?? 0.0;
                $a = $actualIn[$cid] ?? 0.0;
                if ($p == 0.0 && $a == 0.0) {
                    continue; // ซ่อนหมวดที่ยังไม่มีทั้งแผนและผล
                }
                $incomeRows[] = ['name' => $leaf['name'], 'plan' => $p, 'actual' => $a, 'diff' => $a - $p];
                $incPlan += $p;
                $incActual += $a;
            }
        }

        // รายจ่าย: แผน (plan_order รายหมวด) vs ผลจัดซื้อจริงตามแผน (orders ตรวจรับ รายหมวด)
        $ov = PlanOrder::overviewByType($fy, 'all');
        $actualByCat = $this->expenseActualByCategory($fy);
        $expTypes = [];
        $expPlan = 0.0;
        $expActual = 0.0;
        foreach ((array) ($ov['types'] ?? []) as $tc => $t) {
            $cats = [];
            $tpPlan = 0.0;
            $tpActual = 0.0;
            foreach ((array) ($t['categories'] ?? []) as $cat) {
                $p = (float) ($cat['total'] ?? 0);
                $a = (float) ($actualByCat[(string) $cat['code']] ?? 0);
                if ($p == 0.0 && $a == 0.0) {
                    continue;
                }
                $cats[] = ['title' => $cat['title'], 'plan' => $p, 'actual' => $a, 'diff' => $a - $p];
                $tpPlan += $p;
                $tpActual += $a;
            }
            if (!$cats) {
                continue;
            }
            $expTypes[] = ['title' => $t['title'] ?: $tc, 'cats' => $cats, 'plan' => $tpPlan, 'actual' => $tpActual];
            $expPlan += $tpPlan;
            $expActual += $tpActual;
        }

        return [
            'incomeRows' => $incomeRows,
            'incPlan' => $incPlan,
            'incActual' => $incActual,
            'expTypes' => $expTypes,
            'expPlan' => $expPlan,
            'expActual' => $expActual,
        ];
    }

    /**
     * ผลจัดซื้อจริงตามแผน รายหมวด (plan_category) — orders ที่ตรวจรับแล้ว (status >= 5) ผูกกับแผน
     * เชื่อมผ่าน orders.plan_order_id -> plan_order.plan_item_id -> plan_item.category_id -> plan_category
     * @return array [plan_category_code => ยอดจริง]
     */
    private function expenseActualByCategory(int $fy): array
    {
        $sql = "
            SELECT c.code AS cat_code, COALESCE(SUM(oi.price * oi.qty), 0) AS actual
            FROM orders o
            JOIN orders oi ON oi.category_id = o.id AND oi.name = 'order_item'
            JOIN plan_order po ON po.id = o.plan_order_id AND po.deleted_at IS NULL
            JOIN categorise i ON i.code = po.plan_item_id AND i.name = 'plan_item'
            JOIN categorise c ON c.code = i.category_id AND c.name = 'plan_category'
            WHERE o.name = 'order' AND o.thai_year = :yr AND o.status >= 5 AND o.status <> 8
            GROUP BY c.code
        ";
        $out = [];
        try {
            foreach (Yii::$app->db->createCommand($sql, [':yr' => $fy])->queryAll() as $r) {
                $out[(string) $r['cat_code']] = (float) $r['actual'];
            }
        } catch (\Throwable $e) {
            // ถ้าโครง orders/plan ไม่พร้อม ให้คืนว่าง (comparison แสดงเฉพาะแผน)
        }
        return $out;
    }

    /**
     * บล็อกสภาพคล่องต่อปี (ตามแบบฟอร์มแผนเงินบำรุง สป.สธ.)
     *   net = รับ - จ่าย ; opening ยกมา (ปีที่มี ledger ใช้ค่ากรอก, ปีอื่น roll จากปีก่อน)
     *   closing(1) = opening + net ; after = closing - reserve(4) - commitment(5)
     *   ie = รับ/จ่าย ; position(2) = รวมเงินคงเหลือแยกประเภท (ไว้ validate กับ (1))
     */
    private function liquidity(array $years, array $incomeTot, array $expenseTot): array
    {
        $reserve = PlanAnnualAttachment::sumByYear(PlanAnnualAttachment::KIND_RESERVE, $years);
        $commit = PlanAnnualAttachment::sumByYear(PlanAnnualAttachment::KIND_COMMITMENT, $years);

        $ledgers = [];
        foreach (PlanAnnualLedger::find()->where(['fiscal_year' => $years])->all() as $l) {
            $ledgers[(int) $l->fiscal_year] = $l;
        }

        $rows = [];
        $prevClosing = null;
        foreach ($years as $y) {
            $net = (float) ($incomeTot[$y] ?? 0) - (float) ($expenseTot[$y] ?? 0);
            $ledger = $ledgers[$y] ?? null;
            // opening: ถ้ามี ledger ของปีนี้ = ค่ากรอกมือ (authoritative); ไม่มี = roll จาก closing ปีก่อน
            if ($ledger !== null) {
                $opening = (float) $ledger->carry_forward;
            } else {
                $opening = $prevClosing ?? 0.0;
            }
            $closing = $opening + $net;
            $res = (float) ($reserve[$y] ?? 0);
            $com = (float) ($commit[$y] ?? 0);
            $rows[$y] = [
                'net' => $net,
                'opening' => $opening,
                'closing' => $closing,
                'reserve' => $res,
                'commitment' => $com,
                'after' => $closing - $res - $com,
                'ie' => (float) ($expenseTot[$y] ?? 0) > 0 ? (float) ($incomeTot[$y] ?? 0) / (float) $expenseTot[$y] : null,
                'position' => $ledger ? $ledger->positionTotal() : null,
                'hasLedger' => $ledger !== null,
            ];
            $prevClosing = $closing;
        }
        return $rows;
    }
}
