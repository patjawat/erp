<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\hr\models\Employees;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskSearch;

/**
 * RepairV2Controller implements the CRUD actions for Helpdesk model.
 */
class RepairV2Controller extends Controller
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
     * Lists all Helpdesk models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $userId = \Yii::$app->user->id;
        $emp = UserHelper::GetEmployee();
        $searchModel = new HelpdeskSearch([
            'created_by' => $userId,
            'emp_id' => $emp->id,
            'date_filter' => 'this_month',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '\$.repair_note')"), $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        $dataProvider->query->andFilterWhere(['between', new \yii\db\Expression('DATE(created_at)'), AppHelper::convertToGregorian($searchModel->date_start), AppHelper::convertToGregorian($searchModel->date_end)]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summary' => $dataProvider->getTotalCount(),
        ]);
    }

    /**
     * Displays a single Helpdesk model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Helpdesk model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $model = new Helpdesk([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id' => $me->id,
            'asset_number' => $this->request->get('asset_number')
         ]);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                try {
                    $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
                } catch (\Throwable $th) {
                }
                $model->status = 'pending';
                switch ($model->repair_group) {
                    case '1':
                        $depCode = 'GEN';
                        break;

                    case '2':
                        $depCode = 'IT';
                        break;
                    case '3':
                        $depCode = 'MED';
                        break;

                    default:
                        $depCode = '';
                        break;
                }
                $model->repair_number = $model->HelpdeskGenNumber($depCode);;
                if ($model->save()) {
                if($model->asset_number !==''){
                    $this->changAssetStatus($model->asset_number);
                }
                    //ส่งการแจ้งเตือน
                    $this->sendMsg($model);
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    // ส่งข้อความแจ้งเตือน
    protected function sendMsg($model)
    {
        // template message
        $emp = Yii::$app->employee::GetEmployee();
        $message = 'แจ้งซ่อม ' . $emp->departmentName() . "\nสถานที่อื่นๆ : " . $model->data_json['location'] . (isset($checkAssetType['title']) ? "\nประเภท :" . $checkAssetType['title'] . "\nเลขคุภัณฑ์ : " . $model->asset_number : '') . "\nอาการ : " . $model->title . "\nความเร่งด่วน : " . $model->UrgencyName() . "\nเพิ่มเติม  : " . $model->data_json['note'] . "\nเบอร์โทร  : " . $model->data_json['note'] . "\nผู้ร้องขอ  : " . $emp->fullname;

        // try {
            if($model->repair_group == 1){
            $sendTo = 'repair';
            } else if($model->repair_group == 2){
            $sendTo = 'computer_service';
            } else if($model->repair_group == 3){
            $sendTo = 'medical_service';
            } else {
            $sendTo = '';
            }
            $response = Yii::$app->telegram->sendMessage($sendTo, $message, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            ]);
        // } catch (\Exception $e) {
        // }
    }


    
    protected function changAssetStatus($code)
    {
        $model = Asset::findOne(['code' => $code]);
        if ($model) {
            $model->asset_status = 5;
            $model->save();
        }
    }


    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';

            $model->title == '' ? $model->addError('title', 'ต้องระบุอาการ...') : null;
            $model->data_json['urgency'] == '' ? $model->addError('data_json[urgency]', 'ต้องระบุความเร่งด่วน...') : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', 'ต้องระบุสถานะที่...') : null;
            // $model->data_json['technician_req'] == '' ? $model->addError('data_json[technician_req]', 'ต้องระบุช่างเพื่อรับการแจ้งเตือน...') : null;
            $model->repair_group == '' ? $model->addError('repair_group', 'ต้องระบุ...') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }


    /**
     * Updates an existing Helpdesk model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->request_repair_date = AppHelper::convertToThai($model->request_repair_date);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                try {
                    $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
                } catch (\Throwable $th) {
                }
                if ($model->save()) {
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Deletes an existing Helpdesk model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = 'cancel';
        if ($model->save())



            return $this->redirect(['index']);
    }

    //ดึงแผนกของช่างซ่อมบำรุงตามครุภัณฑ์ที่เลืกอ
    public function actionGetRepairGroup($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $repairGroup = Asset::find()->where(['code' => $id])->one();
        return $this->GroupMach($repairGroup->asset_type_id);
    }

        public function GroupMach($group)
        {
            // กลุ่ม
            $group2 = ['COM'];       // in
            $group3 = ['MED'];       // in

            if (in_array($group, $group2)) {
                return 2;
            } elseif (in_array($group, $group3)) {
                return 2;
            } else {
                return 1; // ไม่มีในกลุ่มใด
            }
        }


    /**
     * Finds the Helpdesk model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Helpdesk the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
