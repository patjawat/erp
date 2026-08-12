<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\TalentGrid;
use app\modules\kpi\services\KpiService;

/**
 * ตารางจำแนกศักยภาพบุคลากร 9 Box — จัดวางบุคลากรตามผลการปฏิบัติงาน/ศักยภาพ พร้อมสรุปภาพรวม
 * รายชื่อดึงจากทะเบียนบุคลากร (Employees) เฉพาะผู้ที่ยังปฏิบัติงาน
 */
class TalentGridController extends Controller
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
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->assertCanManage();

        $currentFy = KpiService::currentFiscalYear();
        $fiscalYear = (int) Yii::$app->request->get('fy') ?: $currentFy;
        $depId = (int) Yii::$app->request->get('dep');

        $employeeQuery = Employees::find()
            ->with(['empDepartment', 'employeePosition'])
            ->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC, 'id' => SORT_ASC]);

        $department = $depId > 0 ? Organization::findOne($depId) : null;
        if ($department) {
            $subtreeIds = Organization::find()
                ->select('id')
                ->where(['root' => $department->root])
                ->andWhere(['>=', 'lft', $department->lft])
                ->andWhere(['<=', 'rgt', $department->rgt])
                ->column();
            $employeeQuery->andWhere(['department' => $subtreeIds ?: [$depId]]);
        }

        /** @var Employees[] $employees */
        $employees = $employeeQuery->all();
        $employeeById = ArrayHelper::index($employees, 'id');

        $placements = TalentGrid::find()
            ->where(['fiscal_year' => $fiscalYear, 'emp_id' => array_keys($employeeById) ?: [0]])
            ->all();

        // จัดคนเข้ากล่อง 1-9 และเก็บรายชื่อที่ยังไม่ถูกจัดวางไว้เตือน HR
        $boxes = array_fill_keys(range(1, 9), []);
        $placedEmpIds = [];
        foreach ($placements as $placement) {
            $employee = $employeeById[$placement->emp_id] ?? null;
            if (!$employee) {
                continue;
            }
            $placement->populateRelation('employee', $employee);
            $boxes[(int) $placement->box_no][] = $placement;
            $placedEmpIds[(int) $placement->emp_id] = true;
        }
        foreach ($boxes as $boxNo => $rows) {
            usort($rows, static fn (TalentGrid $a, TalentGrid $b): int => strcmp(
                (string) $a->employee->fname . $a->employee->lname,
                (string) $b->employee->fname . $b->employee->lname
            ));
            $boxes[$boxNo] = $rows;
        }

        $unplaced = array_values(array_filter(
            $employees,
            static fn (Employees $employee): bool => !isset($placedEmpIds[(int) $employee->id])
        ));

        $years = TalentGrid::find()->select('fiscal_year')->distinct()->column();
        $years = array_map('intval', $years);
        foreach ([$currentFy, $fiscalYear] as $extraYear) {
            if (!in_array($extraYear, $years, true)) {
                $years[] = $extraYear;
            }
        }
        rsort($years);

        return $this->render('index', [
            'fiscalYear' => $fiscalYear,
            'years' => $years,
            'depId' => $depId,
            'departments' => Organization::find()
                ->where(['>=', 'lvl', 1])
                ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
                ->all(),
            'boxes' => $boxes,
            'unplaced' => $unplaced,
            'totalEmployees' => count($employees),
        ]);
    }

    /** ฟอร์มจัดวาง/แก้ไขตำแหน่งของบุคลากร 1 คน (เปิดใน modal) */
    public function actionForm($id = null)
    {
        $this->assertCanManage();

        $fiscalYear = (int) Yii::$app->request->get('fy') ?: KpiService::currentFiscalYear();
        if ($id) {
            $model = TalentGrid::findOne($id);
            if (!$model) {
                throw new NotFoundHttpException('ไม่พบข้อมูลการจัดวาง');
            }
        } else {
            $model = new TalentGrid([
                'fiscal_year' => $fiscalYear,
                'performance' => (int) Yii::$app->request->get('performance') ?: TalentGrid::LEVEL_MEDIUM,
                'potential' => (int) Yii::$app->request->get('potential') ?: TalentGrid::LEVEL_MEDIUM,
                'emp_id' => (int) Yii::$app->request->get('emp_id') ?: null,
                'assessed_at' => date('Y-m-d'),
            ]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'บันทึกการจัดวางในตาราง 9 Box เรียบร้อยแล้ว'];
            }
            if (Yii::$app->request->isAjax) {
                // ส่ง error กลับให้แสดงใต้ช่องกรอก แทนการโหลด modal ใหม่ทั้งก้อน
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->modalOrPage('_form', [
            'model' => $model,
            'employeeItems' => $this->employeeItems((int) $model->fiscal_year, (int) $model->emp_id),
        ], $model->isNewRecord ? 'จัดวางบุคลากรในตาราง 9 Box' : 'แก้ไขการจัดวางในตาราง 9 Box');
    }

    public function actionDelete($id)
    {
        $this->assertCanManage();
        $model = TalentGrid::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบข้อมูลการจัดวาง');
        }
        $fiscalYear = (int) $model->fiscal_year;
        $model->delete();

        // ปุ่มลบอยู่ใน modal จึงเรียกแบบ ajax — ตอบ JSON ให้ JS ปิด modal แล้วโหลดหน้าใหม่เอง
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => 'นำบุคลากรออกจากตาราง 9 Box แล้ว'];
        }

        Yii::$app->session->setFlash('success', 'นำบุคลากรออกจากตาราง 9 Box แล้ว');
        return $this->redirect(['index', 'fy' => $fiscalYear]);
    }

    /**
     * รายชื่อสำหรับ dropdown — เอาเฉพาะผู้ที่ยังปฏิบัติงานและยังไม่ถูกจัดวางในปีนั้น
     * (คนที่กำลังแก้ไขอยู่ต้องคงไว้ในลิสต์ด้วย ไม่งั้นฟอร์มจะเลือกค่าเดิมไม่ได้)
     */
    private function employeeItems(int $fiscalYear, int $keepEmpId = 0): array
    {
        $takenIds = TalentGrid::find()
            ->select('emp_id')
            ->where(['fiscal_year' => $fiscalYear])
            ->column();
        $takenIds = array_diff(array_map('intval', $takenIds), [$keepEmpId]);

        $query = Employees::find()
            ->with(['empDepartment'])
            ->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC]);
        if ($takenIds !== []) {
            $query->andWhere(['not in', 'id', $takenIds]);
        }

        $items = [];
        foreach ($query->all() as $employee) {
            $department = $employee->empDepartment->name ?? '';
            $items[(int) $employee->id] = trim($employee->fullname())
                . ($department !== '' ? ' — ' . $department : '');
        }
        return $items;
    }

    protected function assertCanManage(): void
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูตารางจำแนกศักยภาพบุคลากร 9 Box');
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
