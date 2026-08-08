<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\models\Projects;
use app\modules\pm\models\ProjectsSearch;
use app\modules\pm\models\ProjectObjective;
use app\modules\pm\models\ProjectIndicator;
use app\modules\pm\models\ProjectResponsible;
use app\components\UserHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * จัดการโครงการ (แบบเสนอโครงการ)
 */
class ProjectsController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new ProjectsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Projects();
        $model->status = Projects::STATUS_DRAFT;
        $model->thai_year = (int) (date('Y') + 543 + (date('n') >= 10 ? 1 : 0));

        // ตั้งค่าเริ่มต้นจากผู้ใช้ปัจจุบัน — แปลงหน่วยงานในผังเป็นรายการในทะเบียนของปีนั้น
        $me = UserHelper::GetEmployee();
        if ($me) {
            $model->department_id = $me->department;
            $model->syncOrgUnit();
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithChildren($model)) {
                Yii::$app->session->setFlash('success', 'บันทึกโครงการเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'objectives' => [new ProjectObjective()],
            'indicators' => [new ProjectIndicator()],
            'responsibles' => $this->defaultResponsibles($me),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithChildren($model)) {
                Yii::$app->session->setFlash('success', 'แก้ไขโครงการเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $objectives = $model->objectives ?: [new ProjectObjective()];
        $indicators = $model->indicators ?: [new ProjectIndicator()];
        $responsibles = $model->responsibles ?: $this->defaultResponsibles(UserHelper::GetEmployee());

        return $this->render('update', [
            'model' => $model,
            'objectives' => $objectives,
            'indicators' => $indicators,
            'responsibles' => $responsibles,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by']);

        Yii::$app->session->setFlash('success', 'ลบโครงการเรียบร้อย');
        return $this->redirect(['index']);
    }

    /**
     * บันทึกโครงการพร้อมแถวลูกในทรานแซกชันเดียว
     */
    protected function saveWithChildren(Projects $model): bool
    {
        $post = $this->request->post();
        $tx = Yii::$app->db->beginTransaction();
        try {
            // สร้างรหัสโครงการอัตโนมัติเมื่อยังไม่กรอก (เฉพาะตอนสร้างใหม่)
            if ($model->isNewRecord && trim((string) $model->code) === '') {
                $model->syncOrgUnit(); // ต้องรู้หน่วยงานในทะเบียนก่อน จึงจะได้อักษรย่อที่ถูกต้อง
                $model->code = Projects::generateCode(
                    $model->org_unit_id ? (int) $model->org_unit_id : null,
                    $model->thai_year ? (int) $model->thai_year : null
                );
            }

            if (!$model->save()) {
                $tx->rollBack();
                return false;
            }

            $this->syncObjectives($model, $post['Objectives'] ?? []);
            $this->syncIndicators($model, $post['Indicators'] ?? []);
            $this->syncResponsibles($model, $post['Responsibles'] ?? []);

            $tx->commit();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('Save project failed: ' . $e->getMessage(), __METHOD__);
            $model->addError('name', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /** ลบของเดิมแล้วเขียนใหม่ (แถวไม่เยอะ) */
    protected function syncObjectives(Projects $model, array $rows): void
    {
        ProjectObjective::deleteAll(['project_id' => $model->id]);
        $sort = 0;
        foreach ($rows as $row) {
            $detail = trim((string) ($row['detail'] ?? ''));
            if ($detail === '') {
                continue;
            }
            $item = new ProjectObjective();
            $item->project_id = $model->id;
            $item->sort = $sort++;
            $item->detail = $detail;
            $item->save();
        }
    }

    protected function syncIndicators(Projects $model, array $rows): void
    {
        ProjectIndicator::deleteAll(['project_id' => $model->id]);
        $sort = 0;
        foreach ($rows as $row) {
            $detail = trim((string) ($row['detail'] ?? ''));
            if ($detail === '') {
                continue;
            }
            $item = new ProjectIndicator();
            $item->project_id = $model->id;
            $item->sort = $sort++;
            $item->detail = $detail;
            $item->target_percent = ($row['target_percent'] ?? '') === '' ? null : $row['target_percent'];
            $item->save();
        }
    }

    protected function syncResponsibles(Projects $model, array $rows): void
    {
        ProjectResponsible::deleteAll(['project_id' => $model->id]);
        $sort = 0;
        foreach ($rows as $row) {
            $empId = ($row['emp_id'] ?? '') === '' ? null : (int) $row['emp_id'];
            $fullname = trim((string) ($row['fullname'] ?? ''));
            $position = trim((string) ($row['position'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));

            // เลือกชื่อจากทะเบียนแล้วแต่ช่องชื่อว่าง (เช่นสคริปต์ไม่ทำงาน) ให้ดึงจากทะเบียนแทน
            // ไม่เช่นนั้นแถวจะถูกข้ามไปเงียบ ๆ ทั้งที่ผู้ใช้เลือกคนไว้แล้ว
            if ($empId && ($fullname === '' || $position === '' || $phone === '')) {
                $info = ProjectResponsible::employeeInfo($empId) ?: [];
                $fullname = $fullname ?: (string) ($info['fullname'] ?? '');
                $position = $position ?: (string) ($info['position'] ?? '');
                $phone = $phone ?: (string) ($info['phone'] ?? '');
            }
            if ($fullname === '') {
                continue;
            }

            $item = new ProjectResponsible();
            $item->project_id = $model->id;
            $item->sort = $sort++;
            $item->role = $row['role'] ?? ProjectResponsible::ROLE_OWNER;
            $item->emp_id = $empId;
            $item->fullname = $fullname;
            $item->position = $position ?: null;
            $item->phone = $phone ?: null;
            $item->save();
        }
    }

    /** แถวผู้รับผิดชอบเริ่มต้น: ผู้รับผิดชอบ (ผู้ใช้ปัจจุบัน) + ผู้บังคับบัญชา */
    protected function defaultResponsibles($me): array
    {
        $owner = new ProjectResponsible(['role' => ProjectResponsible::ROLE_OWNER]);
        if ($me && ($info = ProjectResponsible::employeeInfo((int) $me->id))) {
            $owner->emp_id = $me->id;
            $owner->fullname = $info['fullname'];
            $owner->position = $info['position'];
            $owner->phone = $info['phone'];
        }
        $director = new ProjectResponsible(['role' => ProjectResponsible::ROLE_DIRECTOR]);

        return [$owner, $director];
    }

    protected function findModel($id)
    {
        if (($model = Projects::findOne(['id' => $id, 'deleted_at' => null])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบโครงการที่ต้องการ');
    }
}
