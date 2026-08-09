<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use app\modules\hr\models\AppraisalRound;
use app\modules\hr\models\Competency;
use app\modules\hr\models\CompetencyAssignment;
use app\modules\hr\models\CompetencyEvaluation;
use app\modules\hr\models\CompetencyExpectation;
use app\modules\hr\models\CompetencyIndicator;
use app\modules\hr\models\CompetencyLevel;
use app\modules\hr\models\CompetencyYear;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\kpi\services\KpiService;

/**
 * สมรรถนะหลัก (Core competency)
 *
 *   index      รายชื่อบุคลากรของรอบประเมิน — HR กำหนดระดับที่คาดหวังและผู้ประเมิน
 *   employee   หน้ารายบุคคล — ปรับระดับที่คาดหวังรายสมรรถนะ
 *   setting    ทะเบียนสมรรถนะประจำปี (เทียบได้กับ Template ของ JD)
 *   round      สร้าง/แก้ไข/เปิด/ปิด รอบประเมิน
 *   copy-round คัดลอกการกำหนดจากรอบอื่น (รอบ 1 → รอบ 2 หรือข้ามปี)
 *
 * แยก "สมรรถนะแม่" ออกจาก "ชุดรายปี" เพื่อเทียบข้ามปีได้
 * ส่วนการกำหนดผู้ประเมินและระดับที่คาดหวังผูกกับ "รอบ" เพราะเปลี่ยนได้ระหว่างปี
 */
class CompetencyController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'copy' => ['POST'],
                    'save-expectation' => ['POST'],
                    'assign' => ['POST'],
                    'copy-round' => ['POST'],
                    'round-status' => ['POST'],
                ],
            ],
        ];
    }

    /** รายชื่อบุคลากรของรอบประเมิน — จุดเข้าหลักของเมนู Core */
    public function actionIndex()
    {
        $this->assertCanManage();

        $fiscalYear = (int) Yii::$app->request->get('fy') ?: KpiService::currentFiscalYear();
        $rounds = AppraisalRound::forYear($fiscalYear);
        $round = $this->resolveRound($fiscalYear, (int) Yii::$app->request->get('rd'), $rounds);

        $showAll = (bool) Yii::$app->request->get('show_all');
        $keyword = trim((string) Yii::$app->request->get('q', ''));
        $depId = (int) Yii::$app->request->get('dep');
        $statusFilter = (string) Yii::$app->request->get('st', '');

        $query = Employees::find()
            ->with(['empDepartment', 'employeePosition'])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC, 'id' => SORT_ASC]);
        if (!$showAll) {
            $query->andWhere(['status' => Employees::STATUS_WORKING]);
        }
        if ($keyword !== '') {
            $query->andWhere(['or',
                ['like', 'fname', $keyword],
                ['like', 'lname', $keyword],
                ['like', 'position_name', $keyword],
            ]);
        }
        if ($depId > 0) {
            $query->andWhere(['department' => $this->departmentSubtreeIds($depId)]);
        }
        if ($statusFilter !== '' && $round) {
            $assignedIds = array_map('intval', CompetencyAssignment::find()
                ->select('emp_id')
                ->where(['round_id' => $round->id])
                ->andWhere(['not', ['evaluator_id' => null]])
                ->column()) ?: [0];
            $query->andWhere($statusFilter === 'assigned'
                ? ['id' => $assignedIds]
                : ['not in', 'id', $assignedIds]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => false,
        ]);

        $competencies = $this->competenciesFor($fiscalYear);
        $competencyIds = array_map(static fn (CompetencyYear $c): int => (int) $c->id, $competencies);

        /** @var Employees[] $employees */
        $employees = $dataProvider->getModels();
        $employeeIds = array_map(static fn (Employees $e): int => (int) $e->id, $employees);

        $expectations = [];
        $assignments = [];
        if ($round && $employeeIds !== []) {
            if ($competencyIds !== []) {
                foreach (CompetencyExpectation::find()
                    ->where(['round_id' => $round->id, 'emp_id' => $employeeIds, 'competency_year_id' => $competencyIds])
                    ->all() as $row) {
                    $expectations[(int) $row->emp_id][(int) $row->competency_year_id] = (int) $row->expected_level;
                }
            }
            foreach (CompetencyAssignment::find()
                ->with('evaluator')
                ->where(['round_id' => $round->id, 'emp_id' => $employeeIds])
                ->all() as $row) {
                $assignments[(int) $row->emp_id] = $row;
            }
        }

        // ผลประเมินของรอบนี้ ผูกกลับไปที่ emp_id เพื่อแสดงในคอลัมน์คะแนน
        $evaluations = [];
        if ($assignments !== []) {
            $assignmentIds = array_map(static fn (CompetencyAssignment $a): int => (int) $a->id, $assignments);
            $byAssignment = [];
            foreach (CompetencyEvaluation::find()->where(['assignment_id' => $assignmentIds])->all() as $row) {
                $byAssignment[(int) $row->assignment_id] = $row;
            }
            foreach ($assignments as $empId => $assignment) {
                if (isset($byAssignment[(int) $assignment->id])) {
                    $evaluations[$empId] = $byAssignment[(int) $assignment->id];
                }
            }
        }

        return $this->render('index', [
            'fiscalYear' => $fiscalYear,
            'years' => $this->yearOptions(),
            'rounds' => $rounds,
            'round' => $round,
            'copySourceRounds' => $round ? $this->copySourceRounds($round) : [],
            'dataProvider' => $dataProvider,
            'employees' => $employees,
            'competencies' => $competencies,
            'expectations' => $expectations,
            'assignments' => $assignments,
            'evaluations' => $evaluations,
            'suggestedEvaluators' => CompetencyAssignment::suggestEvaluators($employees),
            'evaluatorItems' => $this->evaluatorItems(),
            'departments' => Organization::find()
                ->where(['>=', 'lvl', 1])
                ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
                ->all(),
            'keyword' => $keyword,
            'depId' => $depId,
            'statusFilter' => $statusFilter,
            'showAll' => $showAll,
            'maxLevelOverall' => $competencyIds === [] ? 0 : max($this->levelCounts($competencyIds)),
            'metrics' => $this->indexMetrics($round, $competencyIds),
            'overview' => $round ? $this->roundOverview($round) : [],
        ]);
    }

    /**
     * ภาพรวมความคืบหน้ารายหน่วยงานของรอบ — HR ใช้ดูว่าหน่วยไหนยังไม่ขยับ
     * @return array<int, array{name:string, evaluator:string, total:int, submitted:int, completed:int, doing:int, todo:int}>
     */
    private function roundOverview(AppraisalRound $round): array
    {
        $rows = Yii::$app->db->createCommand("
            SELECT t.id AS dep_id, t.name AS dep_name,
                   COUNT(*) AS total,
                   SUM(CASE WHEN ev.status = 'submitted' THEN 1 ELSE 0 END) AS submitted,
                   SUM(CASE WHEN ev.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                   SUM(CASE WHEN ev.status = 'draft' THEN 1 ELSE 0 END) AS doing,
                   SUM(CASE WHEN ev.id IS NULL THEN 1 ELSE 0 END) AS todo,
                   GROUP_CONCAT(DISTINCT CONCAT(v.fname, ' ', v.lname) SEPARATOR ', ') AS evaluators
            FROM {{%hr_competency_assignment}} a
            JOIN {{%employees}} e ON e.id = a.emp_id
            LEFT JOIN {{%tree}} t ON t.id = e.department
            LEFT JOIN {{%employees}} v ON v.id = a.evaluator_id
            LEFT JOIN {{%hr_competency_evaluation}} ev ON ev.assignment_id = a.id
            WHERE a.round_id = :round AND a.evaluator_id IS NOT NULL
            GROUP BY t.id, t.name
            ORDER BY todo DESC, total DESC
        ", [':round' => $round->id])->queryAll();

        return array_map(static fn (array $row): array => [
            'dep_id' => (int) $row['dep_id'],
            'name' => (string) ($row['dep_name'] ?: 'ไม่ระบุหน่วยงาน'),
            'evaluator' => (string) ($row['evaluators'] ?: '—'),
            'total' => (int) $row['total'],
            'submitted' => (int) $row['submitted'],
            'completed' => (int) $row['completed'],
            'doing' => (int) $row['doing'],
            'todo' => (int) $row['todo'],
        ], $rows);
    }

    /** สร้าง/แก้ไขรอบประเมิน (เปิดใน modal) */
    public function actionRound($id = null, $fy = null, $no = null)
    {
        $this->assertCanManage();

        if ($id) {
            $model = $this->findRound((int) $id);
        } else {
            $fiscalYear = (int) $fy ?: KpiService::currentFiscalYear();
            $roundNo = (int) $no ?: (AppraisalRound::find()->where(['fiscal_year' => $fiscalYear, 'round_no' => 1])->exists() ? 2 : 1);
            $dates = AppraisalRound::defaultDates($fiscalYear, $roundNo);
            $model = new AppraisalRound([
                'fiscal_year' => $fiscalYear,
                'round_no' => $roundNo,
                'start_date' => $dates['start'],
                'end_date' => $dates['end'],
                'due_date' => $dates['due'],
                'status' => AppraisalRound::STATUS_DRAFT,
            ]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax && Yii::$app->request->post('ajax') !== null) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            if ($model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'บันทึกรอบประเมินเรียบร้อยแล้ว'];
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->modalOrPage('_round_form', ['model' => $model],
            $model->isNewRecord ? 'สร้างรอบประเมิน' : 'แก้ไขรอบประเมิน');
    }

    /** เปิด/ปิดรอบประเมิน */
    public function actionRoundStatus($id)
    {
        $this->assertCanManage();

        $round = $this->findRound((int) $id);
        $status = (string) Yii::$app->request->post('status');
        if (!in_array($status, [AppraisalRound::STATUS_DRAFT, AppraisalRound::STATUS_OPEN, AppraisalRound::STATUS_CLOSED], true)) {
            Yii::$app->session->setFlash('error', 'สถานะไม่ถูกต้อง');
            return $this->redirect(['index', 'fy' => $round->fiscal_year, 'rd' => $round->round_no]);
        }

        // เปิดรอบไม่ได้ถ้ายังไม่มีใครถูกกำหนดผู้ประเมิน — เปิดไปก็ไม่มีรายชื่อไปโผล่ที่หน้าผู้ประเมิน
        if ($status === AppraisalRound::STATUS_OPEN) {
            $ready = (int) CompetencyAssignment::find()
                ->where(['round_id' => $round->id])
                ->andWhere(['not', ['evaluator_id' => null]])
                ->count();
            if ($ready === 0) {
                Yii::$app->session->setFlash('warning', 'ยังไม่ได้กำหนดผู้ประเมินให้ใครเลย เปิดรอบแล้วจะไม่มีรายชื่อไปแสดงให้ผู้ประเมิน');
                return $this->redirect(['index', 'fy' => $round->fiscal_year, 'rd' => $round->round_no]);
            }
        }

        $round->status = $status;
        $round->save(false);

        Yii::$app->session->setFlash('success', $round->getTitle() . ' — ' . $round->getStatusLabel());
        return $this->redirect(['index', 'fy' => $round->fiscal_year, 'rd' => $round->round_no]);
    }

    /**
     * คัดลอกผู้ประเมินและระดับที่คาดหวังจากรอบอื่นมาที่รอบนี้
     * ใช้ได้ทั้งรอบ 1 → รอบ 2 ในปีเดียวกัน และข้ามปี (2569 → 2570)
     * ข้ามคนที่กำหนดไว้แล้วในรอบปลายทาง เพื่อไม่ทับของที่ HR แก้ไปแล้ว
     */
    public function actionCopyRound()
    {
        $this->assertCanManage();

        $target = $this->findRound((int) Yii::$app->request->post('to'));
        $source = $this->findRound((int) Yii::$app->request->post('from'));
        $back = $this->redirect(['index', 'fy' => $target->fiscal_year, 'rd' => $target->round_no]);

        if ($source->id === $target->id) {
            Yii::$app->session->setFlash('warning', 'รอบต้นทางและปลายทางต้องไม่ใช่รอบเดียวกัน');
            return $back;
        }
        if (!$target->isEditable()) {
            Yii::$app->session->setFlash('error', 'รอบปลายทางปิดแล้ว แก้ไขไม่ได้');
            return $back;
        }

        // สมรรถนะของสองปีเป็นคนละแถว จับคู่ผ่าน "สมรรถนะแม่" เพื่อให้คัดลอกข้ามปีได้
        $sourceByCompetency = ArrayHelper::map($this->competenciesFor($source->fiscal_year), 'competency_id', 'id');
        $targetCompetencies = $this->competenciesFor($target->fiscal_year);
        $targetByCompetency = ArrayHelper::map($targetCompetencies, 'competency_id', 'id');
        $levelCounts = $this->levelCounts(array_map(static fn (CompetencyYear $c): int => (int) $c->id, $targetCompetencies));

        $existingAssign = array_map('intval', CompetencyAssignment::find()
            ->select('emp_id')->where(['round_id' => $target->id])->column());

        $copiedEvaluator = 0;
        $copiedLevel = 0;
        $skippedCompetency = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach (CompetencyAssignment::find()->where(['round_id' => $source->id])->all() as $row) {
                if (in_array((int) $row->emp_id, $existingAssign, true) || !$row->evaluator_id) {
                    continue;
                }
                $copy = new CompetencyAssignment([
                    'emp_id' => $row->emp_id,
                    'round_id' => $target->id,
                    'evaluator_id' => $row->evaluator_id,
                    'source' => $row->source,
                    'status' => CompetencyAssignment::STATUS_DRAFT,
                    'note' => $row->note,
                ]);
                if ($copy->save()) {
                    $copiedEvaluator++;
                }
            }

            foreach (CompetencyExpectation::find()->where(['round_id' => $source->id])->all() as $row) {
                // หา "สมรรถนะแม่" ของแถวต้นทาง แล้วหาแถวของปีปลายทางที่ตรงกัน
                $competencyId = array_search((int) $row->competency_year_id, $sourceByCompetency, true);
                $targetYearId = $competencyId !== false ? ($targetByCompetency[$competencyId] ?? null) : null;
                if (!$targetYearId) {
                    $skippedCompetency++;
                    continue;
                }
                $exists = CompetencyExpectation::find()
                    ->where(['round_id' => $target->id, 'emp_id' => $row->emp_id, 'competency_year_id' => $targetYearId])
                    ->exists();
                if ($exists) {
                    continue;
                }
                $maxLevel = $levelCounts[$targetYearId] ?? 1;
                $copy = new CompetencyExpectation([
                    'emp_id' => $row->emp_id,
                    'round_id' => $target->id,
                    'competency_year_id' => $targetYearId,
                    'expected_level' => min((int) $row->expected_level, max(1, $maxLevel)),
                    'source' => $row->source,
                    'note' => $row->note,
                ]);
                if ($copy->save()) {
                    $copiedLevel++;
                }
            }

            $this->refreshReadyStatus($target, $levelCounts);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'คัดลอกไม่สำเร็จ: ' . $e->getMessage());
            return $back;
        }

        $message = "คัดลอกจาก{$source->getTitle()} — ผู้ประเมิน {$copiedEvaluator} คน · ระดับที่คาดหวัง {$copiedLevel} รายการ";
        if ($skippedCompetency > 0) {
            $message .= " · ข้าม {$skippedCompetency} รายการ เพราะปีปลายทางไม่มีสมรรถนะตัวนั้น";
        }
        Yii::$app->session->setFlash($copiedEvaluator + $copiedLevel > 0 ? 'success' : 'warning',
            $copiedEvaluator + $copiedLevel > 0 ? $message : 'ไม่มีรายการให้คัดลอก — รอบปลายทางกำหนดไว้ครบแล้ว');
        return $back;
    }

    /**
     * HR กำหนดผู้ประเมินและระดับที่คาดหวังให้บุคลากรหลายคนพร้อมกันในรอบที่เลือก
     * ระดับที่เลือกใช้กับทุกสมรรถนะของปีนั้น โดยตัดเพดานตามจำนวนระดับจริงของแต่ละสมรรถนะ
     */
    public function actionAssign()
    {
        $this->assertCanManage();

        $round = $this->findRound((int) Yii::$app->request->post('round_id'));
        $back = $this->backToIndex($round);

        if (!$round->isEditable()) {
            Yii::$app->session->setFlash('error', 'รอบนี้ปิดแล้ว แก้ไขไม่ได้');
            return $back;
        }

        $empIds = array_values(array_unique(array_map('intval', (array) Yii::$app->request->post('emp_ids', []))));
        $evaluatorId = (int) Yii::$app->request->post('evaluator_id');
        $useSuggested = (bool) Yii::$app->request->post('use_suggested');
        $level = (int) Yii::$app->request->post('overall_level');

        if ($empIds === []) {
            Yii::$app->session->setFlash('warning', 'ยังไม่ได้เลือกบุคลากร');
            return $back;
        }
        if (!$useSuggested && $evaluatorId <= 0 && $level <= 0) {
            Yii::$app->session->setFlash('warning', 'เลือกผู้ประเมินหรือระดับที่คาดหวังอย่างน้อยหนึ่งอย่าง');
            return $back;
        }

        $competencies = $this->competenciesFor((int) $round->fiscal_year);
        if ($level > 0 && $competencies === []) {
            Yii::$app->session->setFlash('error', "ปีงบประมาณ {$round->fiscal_year} ยังไม่มีสมรรถนะหลักที่ประกาศใช้");
            return $back;
        }
        $levelCounts = $this->levelCounts(array_map(static fn (CompetencyYear $c): int => (int) $c->id, $competencies));

        $employees = Employees::find()->with('empDepartment')->where(['id' => $empIds])->all();
        $assigned = 0;
        $levelled = 0;
        $skipped = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($employees as $employee) {
                $empId = (int) $employee->id;
                $target = $useSuggested ? CompetencyAssignment::suggestFor($employee) : ($evaluatorId ?: null);

                if ($level > 0) {
                    $levelled += $this->applyOverallLevel($empId, (int) $round->id, $levelCounts, $level);
                }

                if ($target !== null && $target !== $empId) {
                    $model = CompetencyAssignment::findOne(['emp_id' => $empId, 'round_id' => $round->id])
                        ?? new CompetencyAssignment(['emp_id' => $empId, 'round_id' => $round->id]);
                    $model->evaluator_id = $target;
                    $model->source = $useSuggested
                        ? CompetencyAssignment::SOURCE_SUGGESTED
                        : CompetencyAssignment::SOURCE_MANUAL;
                    if ($model->save()) {
                        $assigned++;
                    } else {
                        $skipped[] = $employee->fullname() . ' (' . implode(' ', $model->getFirstErrors()) . ')';
                    }
                } elseif ($target === $empId) {
                    // ผังชี้กลับมาที่ตัวเอง — ปล่อยให้ HR ระบุเป็นรายคน
                    $skipped[] = $employee->fullname();
                } elseif ($useSuggested) {
                    $skipped[] = $employee->fullname();
                }
            }

            $this->refreshReadyStatus($round, $levelCounts);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return $back;
        }

        $parts = [];
        if ($assigned > 0) {
            $parts[] = "กำหนดผู้ประเมิน {$assigned} คน";
        }
        if ($levelled > 0) {
            $parts[] = "ตั้งระดับที่คาดหวัง {$levelled} รายการ";
        }
        $message = $parts === [] ? 'ไม่มีรายการที่เปลี่ยนแปลง' : implode(' · ', $parts);
        if ($skipped !== []) {
            $message .= ' · ระบุผู้ประเมินให้ไม่ได้ ' . count($skipped) . ' คน: ' . implode(', ', array_slice($skipped, 0, 5));
            if (count($skipped) > 5) {
                $message .= ' และอีก ' . (count($skipped) - 5) . ' คน';
            }
        }
        Yii::$app->session->setFlash($skipped === [] ? 'success' : 'warning', $message);
        return $back;
    }

    /** หน้ารายบุคคล — ปรับระดับที่คาดหวังรายสมรรถนะในรอบที่เลือก */
    public function actionEmployee($emp_id, $fy = null, $rd = null)
    {
        $this->assertCanManage();

        $employee = Employees::findOne((int) $emp_id);
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบบุคลากรที่ต้องการ');
        }

        $fiscalYear = (int) $fy ?: KpiService::currentFiscalYear();
        $rounds = AppraisalRound::forYear($fiscalYear);
        $round = $this->resolveRound($fiscalYear, (int) $rd, $rounds);
        $competencies = $this->competenciesFor($fiscalYear);
        $levelCounts = $this->levelCounts(array_map(static fn (CompetencyYear $c): int => (int) $c->id, $competencies));

        $current = [];
        if ($round) {
            foreach (CompetencyExpectation::find()
                ->where(['round_id' => $round->id, 'emp_id' => $employee->id])
                ->all() as $row) {
                $current[(int) $row->competency_year_id] = $row;
            }
        }

        $suggestions = [];
        foreach ($competencies as $competency) {
            $suggestions[(int) $competency->id] = CompetencyExpectation::suggestFor(
                $employee,
                $levelCounts[(int) $competency->id] ?? 1
            );
        }

        return $this->render('employee', [
            'employee' => $employee,
            'fiscalYear' => $fiscalYear,
            'years' => $this->yearOptions(),
            'rounds' => $rounds,
            'round' => $round,
            'assignment' => $round
                ? CompetencyAssignment::find()->with('evaluator')
                    ->where(['round_id' => $round->id, 'emp_id' => $employee->id])->one()
                : null,
            'competencies' => $competencies,
            'levelCounts' => $levelCounts,
            'current' => $current,
            'suggestions' => $suggestions,
        ]);
    }

    /** บันทึกระดับที่คาดหวังทั้งหน้าในครั้งเดียว */
    public function actionSaveExpectation()
    {
        $this->assertCanManage();

        $empId = (int) Yii::$app->request->post('emp_id');
        $round = $this->findRound((int) Yii::$app->request->post('round_id'));
        $levels = (array) Yii::$app->request->post('level', []);
        $notes = (array) Yii::$app->request->post('note', []);

        $employee = Employees::findOne($empId);
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบบุคลากรที่ต้องการ');
        }
        $redirect = $this->redirect(['employee', 'emp_id' => $empId, 'fy' => $round->fiscal_year, 'rd' => $round->round_no]);
        if (!$round->isEditable()) {
            Yii::$app->session->setFlash('error', 'รอบนี้ปิดแล้ว แก้ไขไม่ได้');
            return $redirect;
        }

        // รับเฉพาะสมรรถนะของปีนั้นจริง ๆ กันการยิงค่าข้ามปีเข้ามา
        $allowed = ArrayHelper::index($this->competenciesFor((int) $round->fiscal_year), 'id');
        $levelCounts = $this->levelCounts(array_keys($allowed));

        $saved = 0;
        $cleared = 0;
        $errors = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($allowed as $competencyYearId => $competency) {
                $value = trim((string) ($levels[$competencyYearId] ?? ''));
                $model = CompetencyExpectation::findOne([
                    'emp_id' => $empId,
                    'round_id' => $round->id,
                    'competency_year_id' => $competencyYearId,
                ]);

                if ($value === '') {
                    if ($model) {
                        $model->delete();
                        $cleared++;
                    }
                    continue;
                }

                $level = (int) $value;
                $max = $levelCounts[$competencyYearId] ?? 1;
                if ($level < 1 || $level > $max) {
                    $errors[] = $competency->name . ': ระดับต้องอยู่ระหว่าง 1–' . $max;
                    continue;
                }

                $model ??= new CompetencyExpectation([
                    'emp_id' => $empId,
                    'round_id' => $round->id,
                    'competency_year_id' => $competencyYearId,
                ]);
                $model->expected_level = $level;
                $model->source = CompetencyExpectation::SOURCE_MANUAL;
                $model->note = trim((string) ($notes[$competencyYearId] ?? '')) ?: null;

                if (!$model->save()) {
                    $errors[] = $competency->name . ': ' . implode(' ', $model->getFirstErrors());
                    continue;
                }
                $saved++;
            }

            if ($errors !== []) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ — ' . implode(' · ', $errors));
                return $redirect;
            }
            $this->refreshReadyStatus($round, $levelCounts);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return $redirect;
        }

        Yii::$app->session->setFlash('success', $cleared > 0
            ? "บันทึกระดับที่คาดหวัง {$saved} รายการ และล้างออก {$cleared} รายการแล้ว"
            : "บันทึกระดับที่คาดหวัง {$saved} รายการแล้ว");
        return $redirect;
    }

    /** ทะเบียนสมรรถนะประจำปี (ปุ่มตั้งค่า Core) */
    public function actionSetting()
    {
        $this->assertCanManage();

        $fiscalYear = (int) Yii::$app->request->get('fy') ?: KpiService::currentFiscalYear();
        $items = $this->competenciesFor($fiscalYear, false);

        return $this->render('setting', [
            'fiscalYear' => $fiscalYear,
            'years' => $this->yearOptions(),
            'items' => $items,
            'counts' => $this->countsFor($items),
            'copySourceYears' => $this->copySourceYears($fiscalYear),
        ]);
    }

    /** ฟอร์มเพิ่ม/แก้ไขสมรรถนะหลักของปีหนึ่ง (เปิดใน modal) */
    public function actionForm($id = null)
    {
        $this->assertCanManage();

        $fiscalYear = (int) Yii::$app->request->get('fy') ?: KpiService::currentFiscalYear();

        if ($id) {
            $model = $this->findYear((int) $id);
        } else {
            $model = new CompetencyYear([
                'fiscal_year' => $fiscalYear,
                'status' => CompetencyYear::STATUS_ACTIVE,
                'sort_order' => $this->nextSortOrder($fiscalYear),
            ]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax && Yii::$app->request->post('ajax') !== null) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            if ($this->saveYear($model)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'บันทึกสมรรถนะหลักเรียบร้อยแล้ว'];
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->modalOrPage('_form', [
            'model' => $model,
            'competencyItems' => $this->competencyItems((int) $model->fiscal_year, (int) $model->competency_id),
        ], $model->isNewRecord ? 'เพิ่มสมรรถนะหลักประจำปี' : 'แก้ไขสมรรถนะหลัก');
    }

    /** ดูรายละเอียดระดับและข้อพฤติกรรมบ่งชี้ (อ่านอย่างเดียวในขั้นนี้) */
    public function actionView($id)
    {
        $this->assertCanManage();
        $model = $this->findYear((int) $id);

        return $this->modalOrPage('view', [
            'model' => $model,
            'levels' => CompetencyLevel::find()
                ->with(['indicators.scale.options'])
                ->where(['competency_year_id' => $model->id])
                ->orderBy(['level_no' => SORT_ASC])
                ->all(),
        ], $model->name);
    }

    public function actionDelete($id)
    {
        $this->assertCanManage();
        $model = $this->findYear((int) $id);
        $fiscalYear = (int) $model->fiscal_year;

        // ระดับ ข้อพฤติกรรม และระดับที่คาดหวังของปีนั้นถูกลบตาม FK cascade
        // สมรรถนะแม่ยังคงอยู่เพื่อให้ปีอื่นใช้ต่อ
        $model->delete();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => 'ลบสมรรถนะออกจากปีงบประมาณนี้แล้ว'];
        }

        Yii::$app->session->setFlash('success', 'ลบสมรรถนะออกจากปีงบประมาณนี้แล้ว');
        return $this->redirect(['setting', 'fy' => $fiscalYear]);
    }

    /** คัดลอกชุดสมรรถนะทั้งชุดจากปีก่อน รวมระดับและข้อพฤติกรรม */
    public function actionCopy()
    {
        $this->assertCanManage();

        $from = (int) Yii::$app->request->post('from');
        $to = (int) Yii::$app->request->post('to');
        if ($from <= 0 || $to <= 0 || $from === $to) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกปีต้นทางและปีปลายทางให้ถูกต้อง');
            return $this->redirect(['setting', 'fy' => $to ?: KpiService::currentFiscalYear()]);
        }

        $sources = $this->competenciesFor($from, false);
        $existing = array_map('intval', CompetencyYear::find()
            ->select('competency_id')
            ->where(['fiscal_year' => $to])
            ->column());

        $copied = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($sources as $source) {
                if (in_array((int) $source->competency_id, $existing, true)) {
                    continue;
                }
                $this->cloneYear($source, $to);
                $copied++;
            }
            $this->ensureRounds($to);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'คัดลอกไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['setting', 'fy' => $to]);
        }

        Yii::$app->session->setFlash(
            $copied > 0 ? 'success' : 'warning',
            $copied > 0
                ? "คัดลอกสมรรถนะจากปี {$from} มาปี {$to} จำนวน {$copied} รายการแล้ว"
                : "ปี {$to} มีสมรรถนะจากปี {$from} ครบอยู่แล้ว ไม่มีรายการให้คัดลอก"
        );
        return $this->redirect(['setting', 'fy' => $to]);
    }

    // ---------- ตัวช่วยภายใน ----------

    /** รอบที่กำลังดูอยู่ — ตามพารามิเตอร์ ถ้าไม่ระบุใช้รอบที่เปิดอยู่หรือรอบตามวันที่ปัจจุบัน */
    private function resolveRound(int $fiscalYear, int $roundNo, array $rounds): ?AppraisalRound
    {
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
        return AppraisalRound::currentFor($fiscalYear);
    }

    /** สร้างรอบ 1 และ 2 ให้ปีงบประมาณที่ยังไม่มี */
    private function ensureRounds(int $fiscalYear): void
    {
        foreach ([1, 2] as $roundNo) {
            if (AppraisalRound::find()->where(['fiscal_year' => $fiscalYear, 'round_no' => $roundNo])->exists()) {
                continue;
            }
            $dates = AppraisalRound::defaultDates($fiscalYear, $roundNo);
            (new AppraisalRound([
                'fiscal_year' => $fiscalYear,
                'round_no' => $roundNo,
                'start_date' => $dates['start'],
                'end_date' => $dates['end'],
                'due_date' => $dates['due'],
                'status' => AppraisalRound::STATUS_DRAFT,
            ]))->save(false);
        }
    }

    /** รอบอื่นที่คัดลอกมาได้ — รอบก่อนหน้าในปีเดียวกัน และรอบของปีก่อน ๆ */
    private function copySourceRounds(AppraisalRound $target): array
    {
        return AppraisalRound::find()
            ->where(['<>', 'id', $target->id])
            ->andWhere(['<=', 'fiscal_year', (int) $target->fiscal_year])
            ->andWhere(['exists', CompetencyAssignment::find()
                ->where('{{%hr_competency_assignment}}.round_id = {{%hr_appraisal_round}}.id')])
            ->orderBy(['fiscal_year' => SORT_DESC, 'round_no' => SORT_DESC])
            ->all();
    }

    /**
     * ตั้งระดับที่คาดหวังเดียวกันให้ทุกสมรรถนะของคนนั้น ตัดเพดานตามจำนวนระดับจริงของแต่ละตัว
     * @param array<int, int> $levelCounts competency_year_id => จำนวนระดับ
     * @return int จำนวนสมรรถนะที่บันทึกสำเร็จ
     */
    private function applyOverallLevel(int $empId, int $roundId, array $levelCounts, int $level): int
    {
        $saved = 0;
        foreach ($levelCounts as $competencyYearId => $maxLevel) {
            if ($maxLevel < 1) {
                continue;
            }
            $model = CompetencyExpectation::findOne([
                'emp_id' => $empId,
                'round_id' => $roundId,
                'competency_year_id' => $competencyYearId,
            ]) ?? new CompetencyExpectation([
                'emp_id' => $empId,
                'round_id' => $roundId,
                'competency_year_id' => $competencyYearId,
            ]);
            $model->expected_level = min($level, $maxLevel);
            $model->source = CompetencyExpectation::SOURCE_MANUAL;
            if ($model->save()) {
                $saved++;
            }
        }
        return $saved;
    }

    /**
     * ปรับสถานะ "พร้อมประเมิน" ของทั้งรอบ — พร้อมเมื่อมีทั้งผู้ประเมินและระดับที่คาดหวัง
     * @param array<int, int> $levelCounts
     */
    private function refreshReadyStatus(AppraisalRound $round, array $levelCounts): void
    {
        if ($levelCounts === []) {
            return;
        }
        $withLevel = array_map('intval', CompetencyExpectation::find()
            ->select('emp_id')
            ->distinct()
            ->where(['round_id' => $round->id, 'competency_year_id' => array_keys($levelCounts)])
            ->column());

        foreach (CompetencyAssignment::find()->where(['round_id' => $round->id])->all() as $assignment) {
            $ready = $assignment->evaluator_id && in_array((int) $assignment->emp_id, $withLevel, true);
            $status = $ready ? CompetencyAssignment::STATUS_READY : CompetencyAssignment::STATUS_DRAFT;
            if ($assignment->status !== $status) {
                $assignment->status = $status;
                $assignment->save(false);
            }
        }
    }

    /** รายชื่อผู้ประเมินให้เลือก — บุคลากรที่ยังปฏิบัติงานทั้งหมด พร้อมหน่วยงานกำกับ */
    private function evaluatorItems(): array
    {
        $items = [];
        foreach (Employees::find()
            ->with('empDepartment')
            ->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->all() as $employee) {
            $department = $employee->empDepartment->name ?? '';
            $items[(int) $employee->id] = trim($employee->fullname())
                . ($department !== '' ? ' — ' . $department : '');
        }
        return $items;
    }

    private function backToIndex(AppraisalRound $round)
    {
        $params = ['index', 'fy' => $round->fiscal_year, 'rd' => $round->round_no];
        foreach (['q', 'dep', 'st', 'show_all', 'page'] as $key) {
            $value = Yii::$app->request->post($key, '');
            if ($value !== '' && $value !== null) {
                $params[$key] = $value;
            }
        }
        return $this->redirect($params);
    }

    /** @param int[] $competencyIds */
    private function indexMetrics(?AppraisalRound $round, array $competencyIds): array
    {
        $activeEmpIds = Employees::find()->select('id')->where(['status' => Employees::STATUS_WORKING])->column();
        $employeeCount = count($activeEmpIds);
        $competencyCount = count($competencyIds);

        $complete = 0;
        $partial = 0;
        $assigned = 0;

        if ($round) {
            if ($competencyCount > 0 && $employeeCount > 0) {
                foreach (CompetencyExpectation::find()
                    ->select(['emp_id', 'cnt' => 'COUNT(*)'])
                    ->where(['round_id' => $round->id, 'emp_id' => $activeEmpIds, 'competency_year_id' => $competencyIds])
                    ->groupBy('emp_id')
                    ->asArray()
                    ->all() as $row) {
                    if ((int) $row['cnt'] >= $competencyCount) {
                        $complete++;
                    } else {
                        $partial++;
                    }
                }
            }
            $assigned = (int) CompetencyAssignment::find()
                ->where(['round_id' => $round->id, 'emp_id' => $activeEmpIds ?: [0]])
                ->andWhere(['not', ['evaluator_id' => null]])
                ->count();
        }

        return [
            'employees' => $employeeCount,
            'competencies' => $competencyCount,
            'complete' => $complete,
            'partial' => $partial,
            'missing' => max(0, $employeeCount - $complete - $partial),
            'assigned' => $assigned,
            'unassigned' => max(0, $employeeCount - $assigned),
        ];
    }

    /** สร้างสมรรถนะแม่ให้อัตโนมัติเมื่อ HR ไม่ได้เลือกจากทะเบียนกลาง */
    private function saveYear(CompetencyYear $model): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->competency_id) {
                $competency = new Competency([
                    'code' => Competency::makeCode(Competency::TYPE_CORE),
                    'name' => $model->name,
                    'type' => Competency::TYPE_CORE,
                    'sort_order' => (int) $model->sort_order,
                ]);
                if (!$competency->save()) {
                    $model->addError('name', 'สร้างสมรรถนะในทะเบียนกลางไม่สำเร็จ');
                    $transaction->rollBack();
                    return false;
                }
                $model->competency_id = (int) $competency->id;
            }

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }
            // ปีใหม่ที่เพิ่งมีสมรรถนะ ต้องมีรอบให้ผูกการกำหนดผู้ประเมิน
            $this->ensureRounds((int) $model->fiscal_year);
            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $model->addError('name', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /** คัดลอกสมรรถนะ 1 ตัวไปยังปีปลายทาง พร้อมระดับและข้อพฤติกรรมทั้งหมด */
    private function cloneYear(CompetencyYear $source, int $toYear): void
    {
        $target = new CompetencyYear([
            'competency_id' => $source->competency_id,
            'fiscal_year' => $toYear,
            'name' => $source->name,
            'definition' => $source->definition,
            'sort_order' => $source->sort_order,
            'status' => CompetencyYear::STATUS_DRAFT,
            'note' => $source->note,
        ]);
        if (!$target->save()) {
            throw new \RuntimeException($source->name . ': ' . implode(' ', $target->getFirstErrors()));
        }

        foreach ($source->levels as $level) {
            $newLevel = new CompetencyLevel([
                'competency_year_id' => $target->id,
                'level_no' => $level->level_no,
                'description' => $level->description,
                'sort_order' => $level->sort_order,
            ]);
            if (!$newLevel->save()) {
                throw new \RuntimeException($source->name . ' ระดับ ' . $level->level_no);
            }

            foreach ($level->indicators as $indicator) {
                $newIndicator = new CompetencyIndicator([
                    'level_id' => $newLevel->id,
                    'indicator_no' => $indicator->indicator_no,
                    'text' => $indicator->text,
                    'scale_id' => $indicator->scale_id,
                    'sort_order' => $indicator->sort_order,
                ]);
                if (!$newIndicator->save()) {
                    throw new \RuntimeException($source->name . ' ข้อ ' . $indicator->indicator_no);
                }
            }
        }
    }

    /**
     * สมรรถนะหลักของปีงบประมาณ เรียงตามลำดับที่ HR กำหนด
     * @param bool $activeOnly true = เฉพาะที่ประกาศใช้, false = ทุกสถานะ (สำหรับหน้าตั้งค่า)
     * @return CompetencyYear[]
     */
    private function competenciesFor(int $fiscalYear, bool $activeOnly = true): array
    {
        $query = CompetencyYear::find()
            ->alias('cy')
            ->with('competency')
            ->innerJoin(['c' => Competency::tableName()], 'c.id = cy.competency_id')
            ->where(['cy.fiscal_year' => $fiscalYear, 'c.type' => Competency::TYPE_CORE])
            ->orderBy(['cy.sort_order' => SORT_ASC, 'cy.id' => SORT_ASC]);
        if ($activeOnly) {
            $query->andWhere(['cy.status' => CompetencyYear::STATUS_ACTIVE]);
        }
        return $query->all();
    }

    /**
     * จำนวนระดับสูงสุดของแต่ละสมรรถนะ — ใช้เป็นเพดานของระดับที่คาดหวัง
     * @param int[] $competencyYearIds
     * @return array<int, int>
     */
    private function levelCounts(array $competencyYearIds): array
    {
        if ($competencyYearIds === []) {
            return [];
        }
        $rows = CompetencyLevel::find()
            ->select(['competency_year_id', 'cnt' => 'COUNT(*)'])
            ->where(['competency_year_id' => $competencyYearIds])
            ->groupBy('competency_year_id')
            ->asArray()
            ->all();

        $counts = array_fill_keys(array_map('intval', $competencyYearIds), 0);
        foreach ($rows as $row) {
            $counts[(int) $row['competency_year_id']] = (int) $row['cnt'];
        }
        return $counts;
    }

    /** @param CompetencyYear[] $items */
    private function countsFor(array $items): array
    {
        $counts = [];
        foreach ($items as $item) {
            $counts[$item->id] = [
                'levels' => $item->getLevelCount(),
                'indicators' => $item->getIndicatorCount(),
            ];
        }
        return $counts;
    }

    /** สมรรถนะในทะเบียนกลางที่ยังไม่ถูกใช้ในปีนั้น (ตัวที่กำลังแก้ไขต้องคงไว้ให้เลือกได้) */
    private function competencyItems(int $fiscalYear, int $keepId = 0): array
    {
        $usedIds = array_diff(array_map('intval', CompetencyYear::find()
            ->select('competency_id')
            ->where(['fiscal_year' => $fiscalYear])
            ->column()), [$keepId]);

        $query = Competency::find()
            ->where(['type' => Competency::TYPE_CORE, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
        if ($usedIds !== []) {
            $query->andWhere(['not in', 'id', $usedIds]);
        }

        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    /** @return int[] */
    private function departmentSubtreeIds(int $depId): array
    {
        $node = Organization::findOne($depId);
        if (!$node) {
            return [$depId];
        }
        $ids = Organization::find()
            ->select('id')
            ->where(['root' => $node->root])
            ->andWhere(['>=', 'lft', $node->lft])
            ->andWhere(['<=', 'rgt', $node->rgt])
            ->column();
        return $ids ?: [$depId];
    }

    private function nextSortOrder(int $fiscalYear): int
    {
        $max = (int) CompetencyYear::find()->where(['fiscal_year' => $fiscalYear])->max('sort_order');
        return $max + 1;
    }

    /** ปีที่เลือกได้: ปีที่มีข้อมูลแล้ว + ปีปัจจุบัน + ปีถัดไป (เผื่อ HR ตั้งล่วงหน้า) */
    private function yearOptions(): array
    {
        $current = KpiService::currentFiscalYear();
        $years = CompetencyYear::definedYears();
        $years[] = $current;
        $years[] = $current + 1;
        $years = array_values(array_unique(array_map('intval', $years)));
        rsort($years);
        return $years;
    }

    private function copySourceYears(int $exceptYear): array
    {
        return array_values(array_diff(CompetencyYear::definedYears(), [$exceptYear]));
    }

    private function findYear(int $id): CompetencyYear
    {
        $model = CompetencyYear::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบสมรรถนะที่ต้องการ');
        }
        return $model;
    }

    private function findRound(int $id): AppraisalRound
    {
        $model = AppraisalRound::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรอบประเมินที่ต้องการ');
        }
        return $model;
    }

    protected function assertCanManage(): void
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์จัดการทะเบียนสมรรถนะหลัก');
        }
    }

    protected function modalOrPage($view, $params, $title)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => $title, 'content' => $this->renderAjax($view, $params)];
        }
        return $this->render($view, $params);
    }
}
