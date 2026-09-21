<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use app\modules\plan\models\PlanOrder;
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

    /** แผนประจำปี — ตารางรวมย้อนหลัง 3 ปี + แผน 3 ปี + เทียบแผน-ผลปีปัจจุบัน */
    public function actionIndex($year = null)
    {
        $year = (int) ($year ?: PlanHelper::currentPlanYear());
        $matrix = $this->matrix($year);
        $cmpYear = (int) FinanceCashTxn::currentFiscalYear();

        return $this->render('index', $matrix + [
            'year' => $year,
            'cmpYear' => $cmpYear,
            'compare' => $this->compare($cmpYear),
        ]);
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

        // รายจ่าย
        $s->setCellValue("A$r", 'รายจ่าย (จากแผนรายจ่าย)');
        $r++;
        foreach ($m['expenseRows'] as $row) {
            $s->setCellValue("A$r", '   ' . $row['name']);
            $col = 'B';
            foreach ($allYears as $y) {
                $s->setCellValue($col . $r, $row['vals'][$y] ?? 0);
                $col++;
            }
            $r++;
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
                foreach ($actualYears as $ay) {
                    $vals[$ay] = $row['actual'][$ay] ?? 0.0;
                }
                foreach ($planYears as $py) {
                    $vals[$py] = $row['plan'][$py] ?? 0.0;
                }
                $rows[] = ['name' => $row['name'], 'vals' => $vals, 'planYears' => $planYears, 'plan' => $row['plan']];
            }
            $sub = [];
            foreach ($allYears as $y) {
                $sub[$y] = ($g['subA'][$y] ?? 0.0) + ($g['subP'][$y] ?? 0.0);
            }
            $incomeGroups[] = ['name' => $g['name'], 'rows' => $rows, 'sub' => $sub];
        }

        // รายจ่ายจาก plan_order (read-only) ทุกปี
        $expenseRows = [];
        $expenseTot = array_fill_keys($allYears, 0.0);
        $byType = [];
        foreach ($allYears as $y) {
            $ov = PlanOrder::overviewByType($y, 'all');
            foreach ((array) ($ov['types'] ?? []) as $tc => $t) {
                if (!isset($byType[$tc])) {
                    $byType[$tc] = ['name' => $t['title'] ?: $tc, 'vals' => array_fill_keys($allYears, 0.0)];
                }
                $byType[$tc]['vals'][$y] = (float) ($t['sub']['total'] ?? 0);
            }
            $expenseTot[$y] = (float) ($ov['grand']['total'] ?? 0);
        }
        // เรียงตามลำดับมาตรฐาน PER/OPS/INV/OTH
        foreach (self::EXPENSE_TYPE_ORDER as $tc) {
            if (isset($byType[$tc])) {
                $expenseRows[] = $byType[$tc];
            }
        }
        foreach ($byType as $tc => $row) {
            if (!in_array($tc, self::EXPENSE_TYPE_ORDER, true)) {
                $expenseRows[] = $row;
            }
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
            'expenseRows' => $expenseRows,
            'expenseTot' => $expenseTot,
            'net' => $net,
        ];
    }

    /** ฝั่งรายรับ (TYPE_IN): actual จาก txn, plan จาก finance_cash_plan */
    private function incomeMatrix(int $year): array
    {
        $actualYears = [$year - 3, $year - 2, $year - 1];
        $planYears = [$year, $year + 1, $year + 2];

        $actual = [];
        foreach (
            FinanceCashTxn::find()->select(['fiscal_year', 'category_id', 's' => 'SUM(amount)'])
                ->where(['fiscal_year' => $actualYears, 'txn_type' => FinanceCashTxn::TYPE_IN])
                ->groupBy(['fiscal_year', 'category_id'])->asArray()->all() as $r
        ) {
            $actual[(int) $r['category_id']][(int) $r['fiscal_year']] = (float) $r['s'];
        }
        $plan = [];
        foreach (
            FinanceCashPlan::find()->where(['fiscal_year' => $planYears, 'txn_type' => FinanceCashCategory::TYPE_IN])
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
                foreach ($actualYears as $ay) {
                    $a[$ay] = $actual[$cid][$ay] ?? 0.0;
                    $subA[$ay] += $a[$ay];
                }
                $p = [];
                foreach ($planYears as $py) {
                    $p[$py] = $plan[$cid][$py] ?? 0.0;
                    $subP[$py] += $p[$py];
                }
                $rows[] = ['id' => $cid, 'name' => $leaf['name'], 'actual' => $a, 'plan' => $p];
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

        // รายจ่าย: แผน (plan_order รวม) vs จ่ายจริง (finance_cash_txn OUT รวม)
        $ov = PlanOrder::overviewByType($fy, 'all');
        $expPlan = (float) ($ov['grand']['total'] ?? 0);
        $expActual = (float) FinanceCashTxn::find()
            ->where(['fiscal_year' => $fy, 'txn_type' => FinanceCashTxn::TYPE_OUT])
            ->sum('amount');

        return [
            'incomeRows' => $incomeRows,
            'incPlan' => $incPlan,
            'incActual' => $incActual,
            'expPlan' => $expPlan,
            'expActual' => $expActual,
        ];
    }
}
