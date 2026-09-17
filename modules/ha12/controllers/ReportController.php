<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Round;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * รายงานผลสรุป/ประเมิน PCT (เฟส 4) — พิมพ์รอบ + เทียบสองรอบ + Export Excel
 * ดูได้ทุกคนที่ล็อกอิน (แสดงผลที่เผยแพร่ ; ผู้ดูแลเห็นร่างด้วย)
 */
class ReportController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ]);
    }

    private function fiscalYear(): int
    {
        return (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
    }

    private function findRound(int $id): Ha12Round
    {
        $r = Ha12Round::find()->with('scopeUnit')->where(['id' => $id])->one();
        if (!$r) {
            throw new NotFoundHttpException('ไม่พบรอบประเมิน');
        }
        return $r;
    }

    /** แผงรายงาน: เลือกรอบเพื่อพิมพ์ หรือเลือก 2 รอบเพื่อเทียบ */
    public function actionIndex()
    {
        $fiscalYear = $this->fiscalYear();
        $rounds = Ha12Round::find()->with('scopeUnit')->where(['fiscal_year' => $fiscalYear])->orderBy(['id' => SORT_DESC])->all();
        return $this->render('report/index', [
            'rounds' => $rounds,
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
        ]);
    }

    /** รายงานรอบ (พิมพ์ได้) */
    public function actionRound($id)
    {
        $round = $this->findRound((int) $id);
        return $this->render('report/round', $this->roundData($round));
    }

    /** เทียบสองรอบ */
    public function actionCompare($a = null, $b = null)
    {
        $fiscalYear = $this->fiscalYear();
        $rounds = Ha12Round::find()->with('scopeUnit')->where(['fiscal_year' => $fiscalYear])->orderBy(['id' => SORT_DESC])->all();

        $roundA = $a ? Ha12Round::findOne((int) $a) : null;
        $roundB = $b ? Ha12Round::findOne((int) $b) : null;
        $activities = Ha12Activity::activeList();

        $mapFor = static function (?Ha12Round $r): array {
            if (!$r) {
                return [];
            }
            $rows = Ha12Assessment::find()->where(['round_id' => $r->id, 'status' => Ha12Assessment::STATUS_PUBLISHED])->indexBy('activity_id')->all();
            return $rows;
        };

        return $this->render('report/compare', [
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'rounds' => $rounds,
            'roundA' => $roundA,
            'roundB' => $roundB,
            'activities' => $activities,
            'assessA' => $mapFor($roundA),
            'assessB' => $mapFor($roundB),
        ]);
    }

    /** Export Excel รายงานรอบ */
    public function actionExport($id)
    {
        $round = $this->findRound((int) $id);
        $data = $this->roundData($round);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปประเมิน PCT');

        $sheet->setCellValue('A1', 'รายงานสรุปและประเมินโดย PCT — ' . $round->displayTitle());
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'ขอบเขต: ' . ($round->scopeUnit->name ?? 'ทั้งโรงพยาบาล') . '   ช่วง: ' . $round->periodLabel());
        $sheet->mergeCells('A2:F2');

        $head = 4;
        foreach (['A' => 'กิจกรรม', 'B' => 'ระดับที่ประเมิน', 'C' => 'สถานะ', 'D' => 'หน่วยงาน', 'E' => 'เรื่อง/โรค', 'F' => 'ผลการปรับปรุง'] as $col => $label) {
            $sheet->setCellValue($col . $head, $label);
        }
        $row = $head + 1;
        foreach ($data['activities'] as $act) {
            $a = $data['assessments'][$act->id] ?? null;
            $levels = $a ? implode(', ', array_map(static fn ($l) => 'ระดับ ' . $l, $a->levelArray())) : '';
            $status = $a ? ($a->isPublished() ? 'เผยแพร่' : 'ร่าง') : 'ยังไม่ประเมิน';
            $rows = $a ? $a->rows : [];
            if (!$rows) {
                $sheet->setCellValue('A' . $row, $act->no . '. ' . $act->name);
                $sheet->setCellValue('B' . $row, $levels);
                $sheet->setCellValue('C' . $row, $status);
                $row++;
                continue;
            }
            $first = true;
            foreach ($rows as $r) {
                $sheet->setCellValue('A' . $row, $first ? $act->no . '. ' . $act->name : '');
                $sheet->setCellValue('B' . $row, $first ? $levels : '');
                $sheet->setCellValue('C' . $row, $first ? $status : '');
                $sheet->setCellValue('D' . $row, (string) $r->unit_name);
                $sheet->setCellValue('E' . $row, (string) $r->topic);
                $sheet->setCellValue('F' . $row, (string) $r->improvement);
                $row++;
                $first = false;
            }
        }

        foreach (['A' => 30, 'B' => 18, 'C' => 12, 'D' => 24, 'E' => 30, 'F' => 40] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->getStyle('A' . $head . ':F' . $head)->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $head . ':F' . ($row - 1))->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $tempPath = tempnam(sys_get_temp_dir(), 'ha12-round-');
        (new Xlsx($spreadsheet))->save($tempPath);
        $filename = 'ha12-pct-round-' . $round->id . '.xlsx';

        return Yii::$app->response
            ->sendFile($tempPath, $filename)
            ->on(Response::EVENT_AFTER_SEND, static function () use ($tempPath) {
                @unlink($tempPath);
            });
    }

    /** ข้อมูลรอบสำหรับรายงาน */
    private function roundData(Ha12Round $round): array
    {
        $activities = Ha12Activity::activeList();
        $assessments = Ha12Assessment::find()
            ->with(['rows.sources', 'activity'])
            ->where(['round_id' => $round->id])
            ->indexBy('activity_id')
            ->all();
        return [
            'round' => $round,
            'activities' => $activities,
            'assessments' => $assessments,
        ];
    }
}
