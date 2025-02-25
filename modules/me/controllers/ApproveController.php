<?php

namespace app\modules\me\controllers;

use yii\web\Response;
use app\models\Approve;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\ApproveSearch;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;

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
        $name = $this->request->get('name');
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => $name,'emp_id' => $me->id,'status' => 'Pending']);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        
        return $this->render(($name ? $name.'/index' : 'index'), [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLeaveApprove()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) 
        {
            $data = $this->request->post();
            $approve = Approve::findOne($data['id']);
            $leave = Leave::findOne($approve->from_id);

            //ถ้ามีข้อมุลอยู่จริง
            if($approve){
                
                $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
                $approve->data_json = ArrayHelper::merge($approve->data_json, $approveDate);

                $approve->status = $data['status'];
            
            //ถ้าหัวหน้ากลุ่ม Approve
            if($approve->level == 2){
                $leave->status = 'Checking';
              $leave->save();
            }

            // ผุ้ตรวจสอบวันลาก่อนส่งให้ ผอ.
            if ($approve->level == 3) {
                $me = UserHelper::GetEmployee();
                $approve->emp_id = $me->id;
            }
           

            if ($approve->save()) {
                
                $nextApprove = Approve::findOne(['from_id' => $approve->from_id, 'level' => ($approve->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                    // ส่ง line
                    try {
                        $toUserId = $nextApprove->employee->user->line_id;
                        LineNotify::sendLeave($nextApprove->id, $toUserId);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
             
                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($approve->level == 4) {
                    $leave->status = 'Allow';
                    $leave->save();
                    // try {
                        $toUserId = $leave->employee->user->line_id;
                        $message = 'วันลาขอคุณได้รับการอนุมัติแล้ว';
                        LineNotify::sendPushMessage(toUserId, $message);
                          // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                
                    // } catch (\Throwable $th) {
                    //     //throw $th;
                    // }
                } else if ($approve->status == 'Reject') {
                    $leave->status = 'Reject';
                    $leave->save();
                } else {
                  
                }
             
            }

            return [
                'status' => 'success',
                'container' => '#leave',
            ];
                
            }
            // return $this->request->post();
        }
    }
    
    public function actionLeave($id)
    {
        $me = UserHelper::GetEmployee();
        //ข้อมูลการ Approve
        $approve = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        // ข้อมูลการลา
        $leave = Leave::findOne($approve->from_id);
        
        if (!$approve) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาติ</h6>',
            ];
        }
        if ($this->request->isPost && $approve->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $approve->data_json = ArrayHelper::merge($approve->data_json, $approveDate);
            
            //ถ้าหัวหน้ากลุ่ม Approve
            if($approve->level == 2){
                $leave->status = 'Checking';
                $leave->save();
          }

            // ผุ้ตรวจสอบวันลาก่อนส่งให้ ผอ.
            if ($approve->level == 3) {
                $approve->emp_id = $me->id;
            }
           

            if ($approve->save()) {
                
                $nextApprove = Approve::findOne(['from_id' => $approve->from_id, 'level' => ($approve->level + 1)]);
                if ($nextApprove) {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }
             
                
                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($approve->level == 4) {
                    $leave->status = 'Allow';
                    $leave->save();
                } else if ($approve->status == 'Reject') {
                    $leave->status = 'Reject';
                    $leave->save();
                } else {
                  
                }
             
              
                
                return [
                    'status' => 'success',
                    'container' => '#approve',
                ];
            }
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($approve->leave) ? $approve->leave->getAvatar('ขอ')['avatar'] : '',
                'content' => $this->renderAjax('@app/modules/hr/views/leave/view', [
                    'model' => $approve->leave,
                ]),
            ];
        } else {
            return $this->render('leave/form_approve', [
                'model' => $approve,
            ]);
        }
    }

    public function actionPurchase($id)
    {
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($approve->leave) ? $model->leave->getAvatar('ขอ')['avatar'] : '',
                'content' => $this->renderAjax('leave/form_approve', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('leave/form_approve', [
                'model' => $model,
            ]);
        }
        
    }

    /**
     * Displays a single Approve model.
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
     * Creates a new Approve model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Approve();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
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
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Approve model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
