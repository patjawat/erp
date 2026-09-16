<?php

namespace app\modules\flowchart\controllers;

use app\components\AppHelper;
use app\modules\flowchart\models\Flowchart;
use app\modules\flowchart\models\FlowchartSearch;
use app\modules\flowchart\models\FlowchartStep;
use app\modules\hr\models\Employees;
use app\modules\settings\models\OrgUnit;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * เครื่องมือสร้างผังกระบวนการ (ป้อนขั้นตอน -> ผัง Mermaid + ตารางเอกสาร อัตโนมัติ)
 *
 * สิทธิ์: เปิดให้ผู้ล็อกอินทุกคน (roles => ['@']) — โมดูล standalone ในโซน "เครื่องมือ"
 */
class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'update' => ['GET', 'POST'],
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /** คลังผังกระบวนการ */
    public function actionIndex()
    {
        $searchModel = new FlowchartSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** สร้างผังใหม่ (จาก modal ในหน้าคลัง) แล้วพาเข้าหน้าป้อนขั้นตอน */
    public function actionCreate()
    {
        $model = new Flowchart();
        $model->load(Yii::$app->request->post());

        $model->owner_id = Yii::$app->user->id;
        if (empty($model->budget_year)) {
            $model->budget_year = (int) AppHelper::YearBudget();
        }
        // หน่วยงานเจ้าของมาจากฟอร์ม (ทะเบียน org_unit); ถ้าไม่เลือกลองเดาจากหน่วยของผู้ใช้
        if (empty($model->org_unit_id)) {
            $model->org_unit_id = $this->defaultUnitId((int) $model->budget_year);
        }
        $model->status = Flowchart::STATUS_DRAFT;
        $model->code = Flowchart::nextCode($this->unitPrefix($model->org_unit_id), (int) $model->budget_year);

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างผังใหม่แล้ว เริ่มป้อนขั้นตอนได้เลย');
            return $this->redirect(['update', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'สร้างไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        return $this->redirect(['index']);
    }

    /** ป้อน/แก้ไขขั้นตอน + ข้อมูลผัง */
    public function actionUpdate($id)
    {
        $model = $this->findModel((int) $id);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $stepsRaw = (string) Yii::$app->request->post('steps', '[]');

            try {
                $steps = Json::decode($stepsRaw) ?: [];
            } catch (\Throwable $e) {
                $steps = [];
            }

            // ถ้าเปลี่ยนหน่วยงาน แล้วรหัสนำไม่ตรงอักษรย่อใหม่ ให้ออกรหัสใหม่ตามอักษรย่อ
            $newPrefix = $this->unitPrefix($model->org_unit_id);
            if (strpos((string) $model->code, $newPrefix . '-') !== 0) {
                $model->code = Flowchart::nextCode($newPrefix, (int) ($model->budget_year ?: AppHelper::YearBudget()));
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                $model->revision_no = (int) $model->revision_no + 1;
                if (!$model->save()) {
                    throw new \RuntimeException(implode(' ', $model->getFirstErrors()));
                }

                FlowchartStep::deleteAll(['flowchart_id' => $model->id]);

                $seq = 0;
                foreach ($steps as $row) {
                    $type = (string) ($row['type'] ?? FlowchartStep::TYPE_PROCESS);
                    $title = trim((string) ($row['title'] ?? ''));
                    // ข้ามแถวว่างที่ไม่มีความหมาย (ยกเว้นจุดเริ่ม/จบ)
                    if ($title === '' && !in_array($type, [FlowchartStep::TYPE_START, FlowchartStep::TYPE_END], true)) {
                        continue;
                    }
                    $seq++;
                    $step = new FlowchartStep();
                    $step->flowchart_id = $model->id;
                    $step->seq = $seq;
                    $step->type = in_array($type, FlowchartStep::TYPES, true) ? $type : FlowchartStep::TYPE_PROCESS;
                    $step->title = $title;
                    $step->actor = trim((string) ($row['actor'] ?? '')) ?: null;
                    $step->related_doc = trim((string) ($row['related_doc'] ?? '')) ?: null;
                    $step->duration = trim((string) ($row['duration'] ?? '')) ?: null;
                    $step->note = trim((string) ($row['note'] ?? '')) ?: null;
                    $step->branch_yes = isset($row['branch_yes']) && $row['branch_yes'] !== '' ? (int) $row['branch_yes'] : null;
                    $step->branch_no = isset($row['branch_no']) && $row['branch_no'] !== '' ? (int) $row['branch_no'] : null;
                    if (!$step->save()) {
                        throw new \RuntimeException(implode(' ', $step->getFirstErrors()));
                    }
                }

                $tx->commit();
                Yii::$app->session->setFlash('success', 'บันทึกผังเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            }
        }

        $stepsJson = Json::encode(array_map(
            static fn (FlowchartStep $s) => $s->toArray(),
            $model->steps
        ));

        return $this->render('form', [
            'model' => $model,
            'stepsJson' => $stepsJson,
        ]);
    }

    /** ดูผัง + ตารางกระบวนการ */
    public function actionView($id)
    {
        $model = $this->findModel((int) $id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /** หน้าพิมพ์เอกสาร (พิมพ์/บันทึก PDF ผ่านเบราว์เซอร์) */
    public function actionPrint($id)
    {
        $model = $this->findModel((int) $id);

        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /** ลบผัง (ขั้นตอนถูกลบตาม FK CASCADE) */
    public function actionDelete($id)
    {
        $model = $this->findModel((int) $id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบผังเรียบร้อย');
        return $this->redirect(['index']);
    }

    private function findModel(int $id): Flowchart
    {
        $model = Flowchart::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบผังกระบวนการนี้');
        }
        return $model;
    }

    private function currentEmployee(): ?Employees
    {
        return Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
    }

    private function currentDepartmentId(): ?int
    {
        $emp = $this->currentEmployee();
        return $emp && $emp->department ? (int) $emp->department : null;
    }

    /** อักษรย่อของหน่วยงาน (ทะเบียน org_unit) ใช้เป็นรหัสนำ — ไม่มีหน่วย/ไม่มีอักษรย่อใช้ FC */
    private function unitPrefix(?int $orgUnitId): string
    {
        if ($orgUnitId) {
            $unit = OrgUnit::findOne($orgUnitId);
            if ($unit && trim((string) $unit->code) !== '') {
                return strtoupper(trim((string) $unit->code));
            }
        }
        return 'FC';
    }

    /** เดาหน่วยงานเริ่มต้นจากหน่วยของผู้ใช้ (map tree.id ของแผนก -> org_unit ปีนั้น) */
    private function defaultUnitId(int $budgetYear): ?int
    {
        $dept = $this->currentDepartmentId();
        if (!$dept) {
            return null;
        }
        $year = OrgUnit::yearWithData($budgetYear);
        $unit = OrgUnit::find()
            ->where(['thai_year' => $year, 'ref_id' => $dept, 'active' => 1])
            ->one();
        return $unit ? (int) $unit->id : null;
    }
}
