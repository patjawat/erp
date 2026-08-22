<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\RoomLayout;
use app\modules\booking\models\RoomLayoutSearch;

/**
 * RoomLayoutController implements the CRUD actions for RoomLayout model.
 */
class RoomLayoutController extends Controller
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
     * Lists all RoomLayout models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RoomLayoutSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RoomLayout model.
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
     * Creates a new RoomLayout model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
   public function actionCreate()
    {
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        // ต้องเป็น room_layout ไม่ใช่ meeting_room ไม่งั้นรูปแบบที่สร้างใหม่จะไม่ขึ้นในหน้านี้
        // (RoomLayoutSearch กรอง name = room_layout) แต่ไปโผล่ในทะเบียนห้องประชุมแทน
        $model = new RoomLayout([
            'ref' => $ref,
            'name' => 'room_layout'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                // return $this->redirect(['view', 'id' => $model->id]);
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'status' => 'success',
                    'container' => '#room-type',
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
        
        if(!$model->ref){
            $model->ref  = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            // return $model->data_json;

            return [
                'status' => 'success',
                'container' => '#room-type',
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Deletes an existing RoomLayout model.
     * ตอบกลับเป็น JSON ให้ JS ปุ่มลบพาไปหน้า index เอง
     * @param int $id ID
     * @return array
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        // JS ปุ่มลบ (.delete-item) อ่านผลเป็น JSON — ถ้าตอบเป็น redirect หน้าเดิมจะไม่รีเฟรช
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => 'success',
            'url' => \yii\helpers\Url::to(['index']),
        ];
    }

    /**
     * Finds the RoomLayout model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RoomLayout the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // ต้องล็อกด้วย name ด้วย เพราะตาราง categorise ใช้ร่วมกับชุดข้อมูลอื่น
        if (($model = RoomLayout::findOne(['id' => $id, 'name' => 'room_layout'])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
