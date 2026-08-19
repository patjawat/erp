<?php

namespace app\modules\settings\controllers;

use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\WorkforcePositionMap;
use app\modules\hr\models\WorkforceProfile;
use app\modules\hr\models\WorkforceStandardLine;
use app\modules\hr\models\WorkforceStandardRule;
use app\modules\hr\helpers\OrgRollupHelper;
use app\modules\plan\components\PlanHelper;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * ทะเบียนเกณฑ์กรอบอัตรากำลัง สป.สธ. + หน้าจับคู่กับตำแหน่งของโรงพยาบาล
 *
 * ตัวเกณฑ์มาพร้อม migration แก้ไม่ได้จากหน้าจอ (เป็นเอกสารราชการ)
 * สิ่งที่แต่ละโรงพยาบาลต้องทำคือจับคู่สายงานมาตรฐานกับชื่อตำแหน่งของตัวเอง
 */
class WorkforceStandardController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'auto-match' => ['post'],
                    'save-map' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูทะเบียนเกณฑ์กรอบอัตรากำลัง');
        }

        return true;
    }

    /** ทะเบียนเกณฑ์ — กรองตามระดับโรงพยาบาลที่ตั้งไว้ */
    public function actionIndex()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $profile = WorkforceProfile::forYear($year);

        $level = (string) ($this->request->get('level') ?: $profile->level_code ?: '');
        $category = (string) $this->request->get('category', '');

        $lines = WorkforceStandardLine::currentEdition();
        if ($category !== '') {
            $lines = array_values(array_filter($lines, static fn ($l) => $l->category === $category));
        }

        $ruleMap = $level !== '' ? WorkforceStandardRule::mapForLevel($level) : [];

        $mapCounts = [];
        foreach (WorkforcePositionMap::find()->select(['line_id'])->asArray()->all() as $row) {
            if ($row['line_id'] === null) {
                continue;
            }
            $lineId = (int) $row['line_id'];
            $mapCounts[$lineId] = ($mapCounts[$lineId] ?? 0) + 1;
        }

        return $this->render('index', [
            'lines' => $lines,
            'ruleMap' => $ruleMap,
            'mapCounts' => $mapCounts,
            'level' => $level,
            'category' => $category,
            'levels' => WorkforceProfile::levelOptions(),
            'profile' => $profile,
            'year' => $year,
            'unverified' => $this->countUnverified($level),
        ]);
    }

    /** จับคู่สายงานมาตรฐานกับตำแหน่งของโรงพยาบาล */
    public function actionMap()
    {
        $filter = (string) $this->request->get('filter', 'staffed');
        $mapped = WorkforcePositionMap::positionToLine();
        $headcount = $this->headcountByPosition();

        $positions = EmployeePosition::find()
            ->where(['active' => 1])
            ->orderBy(['title' => SORT_ASC])
            ->all();

        $positions = array_values(array_filter($positions, static function ($position) use ($filter, $mapped, $headcount) {
            $id = (int) $position->id;
            $isMapped = array_key_exists($id, $mapped);
            $hasStaff = ($headcount[$id] ?? 0) > 0;

            return match ($filter) {
                'unmapped' => !$isMapped,
                'mapped' => $isMapped,
                'all' => true,
                default => $hasStaff, // staffed = เฉพาะตำแหน่งที่มีคนอยู่จริง
            };
        }));

        // ตำแหน่งที่มีคนเยอะและยังไม่จับคู่ ควรอยู่บนสุด — เป็นงานที่กระทบกรอบมากที่สุด
        usort($positions, static function ($a, $b) use ($mapped, $headcount) {
            $aMapped = array_key_exists((int) $a->id, $mapped) ? 1 : 0;
            $bMapped = array_key_exists((int) $b->id, $mapped) ? 1 : 0;
            if ($aMapped !== $bMapped) {
                return $aMapped <=> $bMapped;
            }

            return ($headcount[(int) $b->id] ?? 0) <=> ($headcount[(int) $a->id] ?? 0);
        });

        return $this->render('map', [
            'positions' => $positions,
            'mapped' => $mapped,
            'headcount' => $headcount,
            'lineOptions' => $this->lineOptions(),
            'filter' => $filter,
            'summary' => $this->mapSummary($mapped, $headcount),
        ]);
    }

    /** จับคู่อัตโนมัติเฉพาะชื่อที่ตรงกันเป๊ะ ที่เหลือให้คนเลือกเอง */
    public function actionAutoMatch()
    {
        $result = WorkforcePositionMap::autoMatch();
        $matched = count($result['matched']);

        if ($matched > 0) {
            Yii::$app->session->setFlash('success', 'จับคู่อัตโนมัติได้ ' . $matched . ' ตำแหน่ง — ที่เหลือ ' . $result['skipped'] . ' รายการต้องเลือกเอง');
        } else {
            Yii::$app->session->setFlash('warning', 'ไม่มีตำแหน่งใหม่ที่ชื่อตรงกับเกณฑ์พอให้จับคู่อัตโนมัติ');
        }

        return $this->redirect(['map']);
    }

    public function actionSaveMap()
    {
        $input = (array) $this->request->post('line', []);
        $saved = 0;
        $cleared = 0;

        foreach ($input as $positionId => $value) {
            $positionId = (int) $positionId;
            $value = (string) $value;

            $model = WorkforcePositionMap::findOne(['employee_position_id' => $positionId]);

            // '' = ยังไม่ตัดสิน → ลบการจับคู่ทิ้ง ให้กลับไปอยู่ในรายการค้าง
            if ($value === '') {
                if ($model !== null) {
                    $model->delete();
                    $cleared++;
                }
                continue;
            }

            if ($model === null) {
                $model = new WorkforcePositionMap(['employee_position_id' => $positionId]);
            }

            $model->line_id = $value === 'none' ? null : (int) $value;
            $model->matched_by = WorkforcePositionMap::MATCHED_MANUAL;

            if ($model->save()) {
                $saved++;
            }
        }

        Yii::$app->session->setFlash('success', 'บันทึกการจับคู่ ' . $saved . ' รายการ' . ($cleared > 0 ? ' · ยกเลิก ' . $cleared . ' รายการ' : ''));

        return $this->redirect(['map', 'filter' => $this->request->post('filter', 'staffed')]);
    }

    /** ตัวเลือกสายงาน จัดกลุ่มตามประเภทเพื่อให้หาง่ายใน dropdown */
    private function lineOptions(): array
    {
        $groups = [];
        foreach (WorkforceStandardLine::currentEdition() as $line) {
            $groups[$line->categoryLabel()][$line->id] = ($line->seq !== null ? $line->seq . '. ' : '') . $line->title;
        }

        return $groups;
    }

    /** จำนวนคนต่อตำแหน่ง — นับเฉพาะที่นับในกรอบตามเกณฑ์ 5 ประเภทการจ้าง */
    private function headcountByPosition(): array
    {
        $counts = [];
        foreach (OrgRollupHelper::headcountMatrix() as $row) {
            $positionId = (int) $row['position_id'];
            $counts[$positionId] = ($counts[$positionId] ?? 0) + (int) $row['count'];
        }

        return $counts;
    }

    private function mapSummary(array $mapped, array $headcount): array
    {
        $staffed = array_keys(array_filter($headcount, static fn ($c) => $c > 0));

        $done = 0;
        $noStandard = 0;
        foreach ($staffed as $positionId) {
            if (!array_key_exists($positionId, $mapped)) {
                continue;
            }
            if ($mapped[$positionId] === null) {
                $noStandard++;
            } else {
                $done++;
            }
        }

        return [
            'staffed' => count($staffed),
            'done' => $done,
            'no_standard' => $noStandard,
            'pending' => count($staffed) - $done - $noStandard,
        ];
    }

    private function countUnverified(string $level): int
    {
        if ($level === '') {
            return 0;
        }

        return (int) WorkforceStandardRule::find()
            ->where(['level_code' => $level, 'eligible' => null])
            ->count();
    }
}
