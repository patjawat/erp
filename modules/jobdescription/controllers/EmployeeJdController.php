<?php

namespace app\modules\jobdescription\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\modules\hr\models\Employees;
use app\modules\jobdescription\models\JdEmployee;
use app\modules\jobdescription\models\JdEmployeeSection;
use app\modules\jobdescription\models\JdTemplate;

class EmployeeJdController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-section' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * ดู/จัดการ JD ของพนักงาน (โหลด template ได้ แก้ไข/เพิ่มหัวข้อได้)
     */
    public function actionView($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $jd = JdEmployee::find()->where(['emp_id' => $emp_id])->with(['sections', 'template'])->one();
        if (!$jd) {
            $jd = new JdEmployee();
            $jd->emp_id = (int) $emp_id;
        }

        $templateForPosition = null;
        $positionCode = $employee->position_name ?? null;
        if ($positionCode) {
            $templateForPosition = JdTemplate::find()
                ->where(['position_code' => $positionCode, 'is_active' => 1])
                ->with('sections')
                ->one();
        }

        return $this->render('view', [
            'employee' => $employee,
            'jd' => $jd,
            'templateForPosition' => $templateForPosition,
        ]);
    }

    /**
     * โหลด template ตามตำแหน่งงานของพนักงาน แล้ว copy เป็น JD ของพนักงาน
     */
    public function actionLoadTemplate($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $templateId = (int) (Yii::$app->request->post('template_id') ?? Yii::$app->request->get('template_id'));
        $positionCode = $employee->position_name;

        if (!$templateId && $positionCode) {
            $template = JdTemplate::find()
                ->where(['position_code' => $positionCode, 'is_active' => 1])
                ->with('sections')
                ->one();
        } else {
            $template = JdTemplate::find()->where(['id' => $templateId])->with('sections')->one();
        }

        if (!$template) {
            Yii::$app->session->setFlash('error', 'ไม่พบ template สำหรับตำแหน่งนี้ หรือกรุณาเลือก template');
            return $this->redirect(['view', 'emp_id' => $emp_id]);
        }

        $jd = JdEmployee::find()->where(['emp_id' => $emp_id])->one();
        if (!$jd) {
            $jd = new JdEmployee();
            $jd->emp_id = (int) $emp_id;
        }
        $jd->template_id = $template->id;
        $jd->save(false);

        // ลบหัวข้อเดิม แล้ว copy จาก template
        JdEmployeeSection::deleteAll(['jd_employee_id' => $jd->id]);
        foreach ($template->sections as $s) {
            $es = new JdEmployeeSection();
            $es->jd_employee_id = $jd->id;
            $es->title = $s->title;
            $es->content = $s->content;
            $es->sort_order = $s->sort_order;
            $es->save(false);
        }

        Yii::$app->session->setFlash('success', 'โหลด template "' . $template->name . '" แล้ว สามารถแก้ไขหรือเพิ่มหัวข้อได้');
        return $this->redirect(['view', 'emp_id' => $emp_id]);
    }

    public function actionAddSection($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $jd = JdEmployee::find()->where(['emp_id' => $emp_id])->one();
        if (!$jd) {
            $jd = new JdEmployee();
            $jd->emp_id = (int) $emp_id;
            $jd->save(false);
        }

        $section = new JdEmployeeSection();
        $section->jd_employee_id = $jd->id;
        $maxOrder = (int) JdEmployeeSection::find()->where(['jd_employee_id' => $jd->id])->max('sort_order');
        $section->sort_order = $maxOrder + 1;

        if ($section->load(Yii::$app->request->post()) && $section->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มหัวข้อแล้ว');
            return $this->redirect(['view', 'emp_id' => $emp_id]);
        }

        return $this->render('add-section', ['employee' => $employee, 'jd' => $jd, 'section' => $section]);
    }

    public function actionUpdateSection($id)
    {
        $section = JdEmployeeSection::findOne($id);
        if (!$section) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $jd = $section->jdEmployee;
        $employee = $jd->employee;
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบพนักงาน');
        }

        if ($section->load(Yii::$app->request->post()) && $section->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหัวข้อแล้ว');
            return $this->redirect(['view', 'emp_id' => $jd->emp_id]);
        }

        return $this->render('update-section', ['employee' => $employee, 'jd' => $jd, 'section' => $section]);
    }

    public function actionDeleteSection($id)
    {
        $section = JdEmployeeSection::findOne($id);
        if (!$section) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $jd = $section->jdEmployee;
        $empId = $jd ? $jd->emp_id : null;
        $section->delete();
        Yii::$app->session->setFlash('success', 'ลบหัวข้อแล้ว');
        if ($empId) {
            return $this->redirect(['view', 'emp_id' => $empId]);
        }
        return $this->redirect(['/hr/employees/index']);
    }

    protected function findEmployee($id)
    {
        $model = Employees::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบพนักงาน');
        }
        return $model;
    }
}
