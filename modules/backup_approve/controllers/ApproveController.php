<?php

namespace app\modules\approve\controllers;

use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\approve\models\Approve;
use app\modules\approve\models\ApproveSearch;

/**
 * ApproveController implements the CRUD actions for Approve model.
 */
class ApproveController extends Controller
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
     * Lists all Approve models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $name = trim($this->request->get("name", "")); // กำหนดค่าเริ่มต้นเป็น "" ถ้าไม่มีค่า name
        $me = UserHelper::GetEmployee();
        
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['emp_id' => $me->id,'status' => 'Pending']);
        
        // ตรวจสอบว่ามีค่า name จริงก่อนเพิ่มเงื่อนไข
        if (!empty($name)) {

            $dataProvider->query->andFilterWhere(['name' => $name]);
        return $this->render('../'.$name.'/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Approve model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $old = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {
            
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old,$model->data_json, $approveDate); 

            if($model->save()){
                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = ($model->maxLevel() ? "Allow": "Pending");

                    if($model->level == 2 && $model->status == 'Pass'){
                        $model->leave->status = "Checking";
                        $model->leave->save();
                    }else{
                    }
                    $nextApprove->save();
                }
            }
            return [
                'status' => 'success'
            ];
        }
            return [
                'title' => isset($approve->leave) ? $model->leave->getAvatar('ขอ')['avatar'] : '',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];

    }

    /**
     * Finds the Approve model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Approve the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Approve::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
