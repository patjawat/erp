<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\FinanceCashProject;
use app\modules\finance\models\FinanceCashTxn;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * โครงการเงินบำรุง/เงินนอกงบประมาณ (2.4) — จัดการโครงการ + ผูกรายการรับ-จ่ายเข้าโครงการ
 * ทะเบียนคุมทางการ (พิมพ์/Excel) อยู่ที่ /finance/register (fund_by_project)
 */
class ProjectController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['financeView']],
                    ['allow' => true, 'actions' => ['create', 'update', 'assign', 'unassign', 'delete'], 'roles' => ['financeOperate']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['assign' => ['post'], 'unassign' => ['post'], 'delete' => ['post']],
            ],
        ]);
    }

    private static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    public function actionIndex()
    {
        $model = new FinanceCashProject(['fiscal_year' => self::currentFiscalYear()]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างโครงการเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('index', [
            'model' => $model,
            'projects' => FinanceCashProject::find()->orderBy(['is_active' => SORT_DESC, 'fiscal_year' => SORT_DESC, 'name' => SORT_ASC])->all(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $fy = $model->fiscal_year ?: self::currentFiscalYear();

        $assigned = FinanceCashTxn::find()->with('category')
            ->where(['project_id' => $model->id])
            ->orderBy(['doc_date' => SORT_ASC, 'id' => SORT_ASC])->all();

        // รายการที่ยังไม่ผูกโครงการ (ปีงบเดียวกัน) ให้เลือกผูก
        $unassigned = FinanceCashTxn::find()->with('category')
            ->where(['fiscal_year' => $fy, 'project_id' => null])
            ->orderBy(['doc_date' => SORT_DESC, 'id' => SORT_DESC])->limit(300)->all();

        return $this->render('view', [
            'model' => $model,
            'assigned' => $assigned,
            'unassigned' => $unassigned,
        ]);
    }

    public function actionAssign(int $id)
    {
        $model = $this->findModel($id);
        $ids = (array) Yii::$app->request->post('txn_ids', []);
        $ids = array_filter(array_map('intval', $ids));
        if ($ids) {
            $n = FinanceCashTxn::updateAll(['project_id' => $model->id], ['id' => $ids, 'project_id' => null]);
            Yii::$app->session->setFlash('success', "ผูกรายการเข้าโครงการ $n รายการ");
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionUnassign(int $id)
    {
        // $id = txn id
        $txn = FinanceCashTxn::findOne($id);
        if ($txn === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $projectId = $txn->project_id;
        $txn->project_id = null;
        $txn->save(false, ['project_id']);
        return $this->redirect(['view', 'id' => $projectId]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        // ปลดรายการที่ผูกไว้ก่อน (FK SET NULL รองรับอยู่แล้ว แต่ทำให้ชัด)
        FinanceCashTxn::updateAll(['project_id' => null], ['project_id' => $model->id]);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบโครงการเรียบร้อย');
        return $this->redirect(['index']);
    }

    private function findModel(int $id): FinanceCashProject
    {
        $model = FinanceCashProject::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบโครงการ');
        }
        return $model;
    }
}
