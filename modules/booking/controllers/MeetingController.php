<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Room;
use app\modules\booking\models\Booking;
use app\modules\booking\models\RoomSearch;
use app\modules\booking\models\BookingSearch;

/**
 * ConferenceController implements the CRUD actions for Booking model.
 */
class MeetingController extends Controller
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
     * Lists all Booking models.
     *
     * @return string
     */
    public function actionIndex()
    {
        //ตรวจสอบว่าผู้ที่ Login มีห้องประชุมที่รับผิดชอบ
        $me = UserHelper::GetEmployee();
        $checkOwnerRoom = Room::find()
        ->where(['name' => 'meeting_room'])
        ->andWhere(new Expression("JSON_EXTRACT(data_json, '$.owner') = :owner"), [':owner' => strval($me->id)])
        ->all();
        $roomIds = ArrayHelper::getColumn($checkOwnerRoom, 'code');
        
        // ตรวจสอบว่ามีห้องอยู่จริงก่อนใช้ IN
        if (empty($roomIds)) {
            $roomIds = [-1]; // ป้องกัน error ถ้าไม่มีห้องให้ค้นหา
        }

        $searchModel = new BookingSearch([
            'status' => 'pending'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'meeting']);
        $dataProvider->query->andFilterWhere(['IN','room_id',$roomIds]);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel = new RoomSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Displays a single Booking model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
            $model = $this->findModel($id);

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'ขอใช้'.$model->room->title,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
        
    }

    /**
     * Creates a new Booking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Booking();

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
     * Updates an existing Booking model.
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
     * Deletes an existing Booking model.
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


    public function actionRoomStatus()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->status = $status;
        $model->save(false);
        if($model->status == 'approve'){
            $model->sendMessage('ขอเชิญเข้าร่วมประชุม');
        }

        if($model->status == 'cancel'){
            $model->sendMessage('ยกเลิกประชุม');
        }
       return [
        'status' => 'success'
       ];
    }
    /**
     * Finds the Booking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Booking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
