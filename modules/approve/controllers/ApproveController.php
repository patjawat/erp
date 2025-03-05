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


    public function actionView($id)
    {
        $me = UserHelper::GetEmployee();
        //ข้อมูลการ Approve
        $approve = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        // ข้อมูลการลา
        $leave = Leave::findOne($approve->from_id);
        
        if (!$approve) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('view', [
                    'model' => $approve,
                ]),
            ];
        } else {
            return $this->render('@app/modules/approve/views/'.$aprove->name, [
               'model' => $approve,
            ]);
        }
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
        
        if ($this->request->isPost) {
            $me = UserHelper::GetEmployee();
            $status =  $this->request->post('status');
            $old = $model->data_json;
            
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old,$model->data_json, $approveDate); 
            $model->status = $status;
            
            if($model->emp_id ==''){
                $model->emp_id = $me->id;
                
            }
            
            if($model->save()){
                 //ถ้าไม่อนุมัติให้ return ออกเลย
                 
                 //########## ถ้าหากเป็นระบบลา
                 if($model->name == 'leave'){
                            if($status == 'Reject'){
                            $model->leave->status = "Reject";
                            $model->leave->save();
                            $model->leave->MsgReject();
                            
                            return [
                                'status' => 'success'
                            ];
                            
                        }

                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove && $status !=='Reject') {
                    
                    //เงื่อนไขระบบลา
                    if($model->name == 'leave'){                        
                        if($model->level == 2 && $model->status == 'Pass'){
                            $model->leave->status = "Checking";
                            $model->leave->save();
                        }
                        if($model->level == 3 && $model->status == 'Pass'){
                            $model->leave->status = "Verify";
                            $model->leave->save();
                        }else{
                        }
                    }
                    
                    
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                   
                }

                if($model->maxLevel() && $model->status == 'Pass' && $model->name == "leave"){
                    $model->leave->status = "Approve";
                    $model->leave->save();
                    $model->leave->MsgApprove();
                }
                

            }

             

                
                if($model->maxLevel() && $model->status == 'Pass' && $model->name == "purchase"){
                    $model->purchase->status = 2;
                    $model->purchase->save();
                    // $model->leave->MsgApprove();
                }




                // return [
                //     'status' => 'success'
                // ];
            }
         
        }

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
