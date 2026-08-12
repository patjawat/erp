<?php

namespace app\modules\me\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\Competency;
use app\modules\hr\models\CompetencyAssignment;
use app\modules\hr\models\CompetencyEvaluation;
use app\modules\hr\models\CompetencyExpectation;
use app\modules\hr\models\CompetencyIndicator;
use app\modules\hr\models\CompetencyLevel;
use app\modules\hr\models\CompetencyScore;
use app\modules\hr\models\CompetencyYear;
use app\modules\hr\models\Employees;

/**
 * หน้าของผู้ประเมิน (/me/competency)
 * แสดงเฉพาะรายชื่อที่ HR มอบหมายให้คนที่ล็อกอินอยู่ประเมิน ในรอบที่เปิดอยู่
 */
class CompetencyController extends Controller
{
    /** @var Employees|null */
    private $me;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'submit' => ['POST']],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->me = UserHelper::GetEmployee();
        if (!$this->me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลบุคลากรของผู้ใช้นี้');
        }
        return true;
    }

    /** รายชื่อที่ต้องประเมิน จัดกลุ่มตามหน่วยงาน */
    public function actionIndex($rd = null)
    {
        $round = $this->resolveRound((int) $rd);
        if (!$round) {
            return $this->render('index', [
                'round' => null, 'rounds' => $this->myRounds(),
                'groups' => [], 'summary' => $this->emptySummary(),
            ]);
        }

        $assignments = $this->myAssignments($round);
        $evaluations = $this->evaluationsFor($assignments);
        $competencies = $this->competenciesFor((int) $round->fiscal_year);
        $indicatorMap = $this->indicatorMap($competencies);

        // จัดกลุ่มตามหน่วยงานของผู้ถูกประเมิน ตามที่ผังองค์กรจัดไว้
        $groups = [];
        $summary = $this->emptySummary();
        foreach ($assignments as $assignment) {
            $employee = $assignment->employee;
            if (!$employee) {
                continue;
            }
            $evaluation = $evaluations[(int) $assignment->id] ?? null;
            $progress = $this->progressOf($assignment, $evaluation, $competencies, $indicatorMap);

            $key = (int) ($employee->department ?: 0);
            $groups[$key]['name'] ??= $employee->departmentName() ?: 'ไม่ระบุหน่วยงาน';
            $groups[$key]['rows'][] = [
                'assignment' => $assignment,
                'employee' => $employee,
                'evaluation' => $evaluation,
                'progress' => $progress,
            ];

            $summary['total']++;
            if ($evaluation && $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED) {
                $summary['submitted']++;
            } elseif ($progress['expected'] > 0 && $progress['rated'] >= $progress['expected']) {
                $summary['completed']++;
            } elseif ($progress['rated'] > 0) {
                $summary['in_progress']++;
            } else {
                $summary['not_started']++;
            }
        }

        return $this->render('index', [
            'round' => $round,
            'rounds' => $this->myRounds(),
            'groups' => $groups,
            'summary' => $summary,
        ]);
    }

    /** ฟอร์มให้คะแนนรายบุคคล */
    public function actionEvaluate($id)
    {
        $assignment = $this->findMyAssignment((int) $id);
        $round = $assignment->round;
        $employee = $assignment->employee;

        $competencies = $this->competenciesFor((int) $round->fiscal_year);
        $indicatorMap = $this->indicatorMap($competencies);
        $expected = $this->expectedLevels($assignment);
        $evaluation = $this->ensureEvaluation($assignment);

        $scores = [];
        foreach (CompetencyScore::find()->where(['evaluation_id' => $evaluation->id])->all() as $row) {
            $scores[(int) $row->indicator_id] = ['score' => (int) $row->score, 'by' => $row->scored_by];
        }

        return $this->render('evaluate', [
            'assignment' => $assignment,
            'employee' => $employee,
            'round' => $round,
            'evaluation' => $evaluation,
            'competencies' => $competencies,
            'indicatorMap' => $indicatorMap,
            'expected' => $expected,
            'scores' => $scores,
            'levelDescriptions' => $this->levelDescriptions($competencies),
            'next' => $this->nextAssignmentId($assignment),
        ]);
    }

    /** บันทึกคะแนนของคน 1 คน */
    public function actionSave($id)
    {
        $assignment = $this->findMyAssignment((int) $id);
        $evaluation = $this->ensureEvaluation($assignment);
        $redirect = $this->redirect(['evaluate', 'id' => $assignment->id]);

        if ($evaluation->isLocked()) {
            Yii::$app->session->setFlash('error', 'ส่งผลประเมินไปแล้ว แก้ไขไม่ได้');
            return $redirect;
        }
        if ($assignment->round->status !== AppraisalRound::STATUS_OPEN) {
            Yii::$app->session->setFlash('error', 'รอบนี้ยังไม่เปิดหรือปิดไปแล้ว บันทึกไม่ได้');
            return $redirect;
        }

        $competencies = $this->competenciesFor((int) $assignment->round->fiscal_year);
        $indicatorMap = $this->indicatorMap($competencies);
        $expected = $this->expectedLevels($assignment);

        $itemScores = (array) Yii::$app->request->post('item_score', []); // [indicator_id] => 1-5

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $saved = 0;
            foreach ($expected as $competencyYearId => $expectedLevel) {
                foreach (($indicatorMap[$competencyYearId] ?? []) as $levelNo => $indicators) {
                    if ((int) $levelNo > (int) $expectedLevel) {
                        continue;
                    }
                    foreach ($indicators as $indicator) {
                        $indicatorId = (int) $indicator->id;
                        $value = (int) ($itemScores[$indicatorId] ?? 0);

                        $model = CompetencyScore::findOne(['evaluation_id' => $evaluation->id, 'indicator_id' => $indicatorId]);
                        if ($value < 1 || $value > 5) {
                            $model?->delete();
                            continue;
                        }
                        $model ??= new CompetencyScore([
                            'evaluation_id' => $evaluation->id,
                            'indicator_id' => $indicatorId,
                        ]);
                        $model->score = $value;
                        $model->scored_by = CompetencyScore::BY_ITEM;
                        if ($model->save()) {
                            $saved++;
                        }
                    }
                }
            }

            $evaluation->comment = trim((string) Yii::$app->request->post('comment')) ?: null;
            $this->refreshEvaluation($evaluation, $assignment, $competencies, $indicatorMap);
            $transaction->commit();

            Yii::$app->session->setFlash('success', "บันทึกคะแนนแล้ว {$saved} ข้อ");
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return $redirect;
        }

        $next = (int) Yii::$app->request->post('go_next');
        if ($next > 0) {
            return $this->redirect(['evaluate', 'id' => $next]);
        }
        return $this->redirect(['index', 'rd' => $assignment->round->round_no]);
    }

    /** ส่งผลประเมินทั้งชุดของผู้ประเมินคนนี้ */
    public function actionSubmit($rd)
    {
        $round = $this->resolveRound((int) $rd);
        if (!$round) {
            throw new NotFoundHttpException('ไม่พบรอบประเมิน');
        }
        $back = $this->redirect(['index', 'rd' => $round->round_no]);

        if ($round->status !== AppraisalRound::STATUS_OPEN) {
            Yii::$app->session->setFlash('error', 'รอบนี้ยังไม่เปิดหรือปิดไปแล้ว');
            return $back;
        }

        $assignments = $this->myAssignments($round);
        $evaluations = $this->evaluationsFor($assignments);
        $competencies = $this->competenciesFor((int) $round->fiscal_year);
        $indicatorMap = $this->indicatorMap($competencies);

        $pending = [];
        foreach ($assignments as $assignment) {
            $evaluation = $evaluations[(int) $assignment->id] ?? null;
            if ($evaluation && $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED) {
                continue;
            }
            $progress = $this->progressOf($assignment, $evaluation, $competencies, $indicatorMap);
            if ($progress['expected'] === 0 || $progress['rated'] < $progress['expected']) {
                $pending[] = $assignment->employee?->fullname() ?? ('รหัส ' . $assignment->emp_id);
            }
        }

        if ($pending !== []) {
            Yii::$app->session->setFlash('warning', 'ยังประเมินไม่ครบ ' . count($pending) . ' คน: '
                . implode(', ', array_slice($pending, 0, 5))
                . (count($pending) > 5 ? ' และอีก ' . (count($pending) - 5) . ' คน' : ''));
            return $back;
        }

        $submitted = 0;
        $now = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                $evaluation = $evaluations[(int) $assignment->id] ?? null;
                if (!$evaluation || $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED) {
                    continue;
                }
                $evaluation->status = CompetencyEvaluation::STATUS_SUBMITTED;
                $evaluation->submitted_at = $now;
                $evaluation->save(false);
                $submitted++;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ส่งผลไม่สำเร็จ: ' . $e->getMessage());
            return $back;
        }

        Yii::$app->session->setFlash('success', "ส่งผลประเมินแล้ว {$submitted} คน — แก้ไขไม่ได้แล้ว");
        return $back;
    }

    // ---------- ตัวช่วยภายใน ----------

    /** รอบที่คนนี้มีงานประเมินอยู่ */
    private function myRounds(): array
    {
        $roundIds = CompetencyAssignment::find()
            ->select('round_id')
            ->distinct()
            ->where(['evaluator_id' => $this->me->id])
            ->column();
        if ($roundIds === []) {
            return [];
        }
        return AppraisalRound::find()
            ->where(['id' => $roundIds])
            ->andWhere(['<>', 'status', AppraisalRound::STATUS_DRAFT])
            ->orderBy(['fiscal_year' => SORT_DESC, 'round_no' => SORT_DESC])
            ->all();
    }

    private function resolveRound(int $roundNo): ?AppraisalRound
    {
        $rounds = $this->myRounds();
        if ($rounds === []) {
            return null;
        }
        if ($roundNo > 0) {
            foreach ($rounds as $round) {
                if ((int) $round->round_no === $roundNo) {
                    return $round;
                }
            }
        }
        foreach ($rounds as $round) {
            if ($round->status === AppraisalRound::STATUS_OPEN) {
                return $round;
            }
        }
        return $rounds[0];
    }

    /** @return CompetencyAssignment[] */
    private function myAssignments(AppraisalRound $round): array
    {
        return CompetencyAssignment::find()
            ->with(['employee.empDepartment'])
            ->where(['evaluator_id' => $this->me->id, 'round_id' => $round->id])
            ->andWhere(['not', ['evaluator_id' => null]])
            ->all();
    }

    /**
     * @param CompetencyAssignment[] $assignments
     * @return array<int, CompetencyEvaluation> assignment_id => ใบประเมิน
     */
    private function evaluationsFor(array $assignments): array
    {
        $ids = array_map(static fn (CompetencyAssignment $a): int => (int) $a->id, $assignments);
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach (CompetencyEvaluation::find()->where(['assignment_id' => $ids])->all() as $row) {
            $out[(int) $row->assignment_id] = $row;
        }
        return $out;
    }

    private function findMyAssignment(int $id): CompetencyAssignment
    {
        $model = CompetencyAssignment::find()
            ->with(['employee', 'round'])
            ->where(['id' => $id, 'evaluator_id' => $this->me->id])
            ->one();
        if (!$model) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ประเมินบุคลากรรายนี้ หรือไม่พบรายการที่มอบหมาย');
        }
        if ($model->round && $model->round->status === AppraisalRound::STATUS_DRAFT) {
            throw new ForbiddenHttpException('รอบประเมินนี้ยังไม่เปิด');
        }
        return $model;
    }

    private function ensureEvaluation(CompetencyAssignment $assignment): CompetencyEvaluation
    {
        $evaluation = CompetencyEvaluation::findOne(['assignment_id' => $assignment->id]);
        if (!$evaluation) {
            $evaluation = new CompetencyEvaluation(['assignment_id' => $assignment->id]);
            $evaluation->save(false);
        }
        return $evaluation;
    }

    /** @return CompetencyYear[] */
    private function competenciesFor(int $fiscalYear): array
    {
        return CompetencyYear::find()
            ->alias('cy')
            ->innerJoin(['c' => Competency::tableName()], 'c.id = cy.competency_id')
            ->where([
                'cy.fiscal_year' => $fiscalYear,
                'c.type' => Competency::TYPE_CORE,
                'cy.status' => CompetencyYear::STATUS_ACTIVE,
            ])
            ->orderBy(['cy.sort_order' => SORT_ASC, 'cy.id' => SORT_ASC])
            ->all();
    }

    /**
     * @param CompetencyYear[] $competencies
     * @return array<int, array<int, CompetencyIndicator[]>> competency_year_id => level_no => ข้อ
     */
    private function indicatorMap(array $competencies): array
    {
        $ids = array_map(static fn (CompetencyYear $c): int => (int) $c->id, $competencies);
        if ($ids === []) {
            return [];
        }
        $map = [];
        foreach (CompetencyLevel::find()
            ->with(['indicators.scale.options'])
            ->where(['competency_year_id' => $ids])
            ->orderBy(['level_no' => SORT_ASC])
            ->all() as $level) {
            $map[(int) $level->competency_year_id][(int) $level->level_no] = $level->indicators;
        }
        return $map;
    }

    /** @param CompetencyYear[] $competencies */
    private function levelDescriptions(array $competencies): array
    {
        $ids = array_map(static fn (CompetencyYear $c): int => (int) $c->id, $competencies);
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach (CompetencyLevel::find()->where(['competency_year_id' => $ids])->all() as $level) {
            $out[(int) $level->competency_year_id][(int) $level->level_no] = (string) $level->description;
        }
        return $out;
    }

    /** @return array<int, int> competency_year_id => ระดับที่คาดหวัง */
    private function expectedLevels(CompetencyAssignment $assignment): array
    {
        $out = [];
        foreach (CompetencyExpectation::find()
            ->where(['emp_id' => $assignment->emp_id, 'round_id' => $assignment->round_id])
            ->all() as $row) {
            $out[(int) $row->competency_year_id] = (int) $row->expected_level;
        }
        return $out;
    }

    /** ความคืบหน้าของใบประเมิน 1 ใบ */
    private function progressOf(
        CompetencyAssignment $assignment,
        ?CompetencyEvaluation $evaluation,
        array $competencies,
        array $indicatorMap
    ): array {
        $expected = $this->expectedLevels($assignment);
        $expectedCount = 0;
        foreach ($expected as $competencyYearId => $expectedLevel) {
            foreach (($indicatorMap[$competencyYearId] ?? []) as $levelNo => $indicators) {
                if ((int) $levelNo <= (int) $expectedLevel) {
                    $expectedCount += count($indicators);
                }
            }
        }
        $rated = $evaluation
            ? (int) CompetencyScore::find()->where(['evaluation_id' => $evaluation->id])->count()
            : 0;

        return [
            'rated' => $rated,
            'expected' => $expectedCount,
            'percent' => $expectedCount > 0 ? (int) round($rated / $expectedCount * 100) : 0,
        ];
    }

    /** คิดคะแนนใหม่และปรับสถานะใบประเมิน */
    private function refreshEvaluation(
        CompetencyEvaluation $evaluation,
        CompetencyAssignment $assignment,
        array $competencies,
        array $indicatorMap
    ): void {
        $scores = [];
        foreach (CompetencyScore::find()->where(['evaluation_id' => $evaluation->id])->all() as $row) {
            $scores[(int) $row->indicator_id] = (int) $row->score;
        }
        $expected = $this->expectedLevels($assignment);
        $result = CompetencyEvaluation::calculate($expected, $indicatorMap, $scores);

        $evaluation->score_percent = $result['total'];
        $complete = $result['expected'] > 0 && $result['rated'] >= $result['expected'];
        if ($evaluation->status !== CompetencyEvaluation::STATUS_SUBMITTED) {
            $evaluation->status = $complete
                ? CompetencyEvaluation::STATUS_COMPLETED
                : CompetencyEvaluation::STATUS_DRAFT;
            $evaluation->completed_at = $complete ? date('Y-m-d H:i:s') : null;
        }
        $evaluation->save(false);
    }

    /** คนถัดไปในคิวที่ยังประเมินไม่เสร็จ — ใช้กับปุ่ม "บันทึกและไปคนถัดไป" */
    private function nextAssignmentId(CompetencyAssignment $current): ?int
    {
        $assignments = $this->myAssignments($current->round);
        $evaluations = $this->evaluationsFor($assignments);
        $competencies = $this->competenciesFor((int) $current->round->fiscal_year);
        $indicatorMap = $this->indicatorMap($competencies);

        foreach ($assignments as $assignment) {
            if ((int) $assignment->id === (int) $current->id) {
                continue;
            }
            $evaluation = $evaluations[(int) $assignment->id] ?? null;
            if ($evaluation && $evaluation->status === CompetencyEvaluation::STATUS_SUBMITTED) {
                continue;
            }
            $progress = $this->progressOf($assignment, $evaluation, $competencies, $indicatorMap);
            if ($progress['expected'] === 0 || $progress['rated'] < $progress['expected']) {
                return (int) $assignment->id;
            }
        }
        return null;
    }

    private function emptySummary(): array
    {
        return ['total' => 0, 'submitted' => 0, 'completed' => 0, 'in_progress' => 0, 'not_started' => 0];
    }
}
