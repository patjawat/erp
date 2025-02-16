<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomSearch;

/**
 * RoomController implements the CRUD actions for Room model.
 */
class MeetingRoomController extends Controller
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
     * Lists all Room models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RoomSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'meeting_room']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Room model.
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
                'title' => $this->request->get('title'),
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
     * Creates a new Room model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $model = new Room([
            'ref' => $ref,
            'name' => 'meeting_room'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // return $this->redirect(['view', 'id' => $model->id]);
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return $this->CheckRoomAccessory($model);
                
                return [
                    'status' => 'success',
                    'container' => '#booking',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    //ตรวจสอบว่ามีอุปกรณ์รายการใหม่หรือไม่
    protected function CheckRoomAccessory($model)
    {

        $data = $model->data_json['room_accessory'];

foreach ($data as $item) {
    if (!Categorise::findOne(['name' => 'room_accessory','title' => $item])) { // เช็คว่ามีข้อมูลหรือยัง
               $maxCode = Categorise::find()
                ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
                ->where(['like', 'name', 'room_accessory'])
                ->scalar();
                    $model = new Categorise();
                    $model->name = 'room_accessory';
                    $model->code = ($maxCode+1);
                    $model->title = $item;
                    $model->save(false);
                }
            }

    //     return $model->data_json['room_accessory'];
    //  $location = Categorise::findOne($model->location);  
    //  if(!$location){
    //     $maxCode = Categorise::find()
    // ->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])
    // ->where(['like', 'name', 'document_org'])
    // ->scalar();
    //     $newLocation = new Categorise;
    //     $newLocation->code = ($maxCode+1);
    //     $newLocation->title = $model->location;
    //     $newLocation->name = 'document_org';
    //     $newLocation->save(false);
    //  } 
    }
    
    /**
     * Updates an existing Room model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post() && $model->save(false))) {
            // return $this->redirect(['view', 'id' => $model->id]);
            \Yii::$app->response->format = Response::FORMAT_JSON;
            // return $model;
            // $this->CheckRoomAccessory($model);
            return [
                'status' => 'success',
                'container' => '#booking',
            ];
        }
        
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            // return [
            //     'title' => $this->request->get('title'),
            //     'content' => $this->renderAjax('update', [
            //     'model' => $model,
            //     ]),
            // ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Room model.
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
     * Finds the Room model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Room the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Room::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
