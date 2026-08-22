<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanOrderSearch;
use app\modules\plan\components\PlanHelper;

/**
 * PersonnelController implements the CRUD actions for PlanOrder model.
 */
class PersonnelController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all PlanOrder models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanOrderSearch();
        if (!$this->request->get('PlanOrderSearch')) {
            $searchModel->thai_year = \app\modules\plan\components\PlanHelper::currentPlanYear();
        }
        $dataProvider = $searchModel->search($this->request->queryParams);
         $dataProvider->query->andFilterWhere(['plan_group_id' => 'personnel']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionGetPlanItem()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $planCategoryId = $parents[0];

                    $out = Categorise::find()
                        ->where(['category_id' => $planCategoryId, 'name' => 'plan_item'])
                        ->select(['code as id', 'title as name'])
                        ->asArray()
                        ->all();
        
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }


    
    /**
     * Displays a single PlanOrder model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PlanOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        // ใช้หน้าจัดทำแผนบุคลากรแบบรวมรายชื่อของหน่วยงานเป็นจุดสร้างหลัก
        // เพื่อให้เมนูเดิม /plan/personnel/create เปิด workflow ใหม่ทันที
        // (เลือกประเภทค่าใช้จ่าย -> ดึงรายชื่อ -> เฉลี่ยงบทั้งปีรายเดือน)
        return $this->redirect(['/me/plan/create-personnel']);

        /* Legacy single-person form retained below temporarily for reference.
        $model = new PlanOrder([
            'thai_year' => \app\modules\plan\components\PlanHelper::currentPlanYear(),
            'plan_group_id' => 'personnel',
            // plan_category_id/plan_type_id ถูก derive จาก plan_item_id ที่ PlanOrder::beforeSave()
            // (เดิมตั้ง 'PER' ตายตัวซึ่งไม่ใช่รหัสหมวดจริง ทำให้ข้อมูลปนเปื้อน)
        ]);

        if ($model->load(Yii::$app->request->post())) {

            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
        */
    }

    /**
     * Updates an existing PlanOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $editableStatuses = PlanHelper::canAdjust($model->thai_year) ? ['draft', 'reject', 'renew'] : ['draft', 'reject'];
        if (!in_array($model->status, $editableStatuses, true)) {
            throw new ForbiddenHttpException('แผนที่ส่งขออนุมัติหรืออนุมัติแล้ว แก้ไขไม่ได้');
        }
           $items = $model->getPlanItems()->all(); // โหลดรายการเดิม

        if ($this->request->isPost && $model->load($this->request->post())) {
             Yii::$app->response->format = Response::FORMAT_JSON;
             $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items
        ]);
    }

    /**
     * Deletes an existing PlanOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== 'draft') {
            throw new ForbiddenHttpException('ลบได้เฉพาะแผนสถานะร่างเท่านั้น');
        }
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PlanOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PlanOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlanOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
