<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\MeetingStatus;
use app\modules\booking\models\MeetingStatusSearch;

/**
 * MeetingStatusController implements the CRUD actions for MeetingStatus model.
 */
class MeetingStatusController extends Controller
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
                        'update-color' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all MeetingStatus models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new MeetingStatusSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'meeting_status']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MeetingStatus model.
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
     * Creates a new MeetingStatus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
   public function actionCreate()
    {
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        // ต้องเป็น meeting_status ไม่ใช่ meeting_room ไม่งั้นสถานะที่สร้างใหม่จะไม่ขึ้นในหน้านี้
        // (actionIndex กรอง name = meeting_status) แต่ไปโผล่ในทะเบียนห้องประชุมแทน
        $model = new MeetingStatus([
            'ref' => $ref,
            'name' => 'meeting_status'
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
     * Deletes an existing MeetingStatus model.
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
     * บันทึกสีพื้นหลัง/สีตัวหนังสือของสถานะจากหน้าทะเบียน
     * (เดิมหน้า index ยิงไปที่ /hr/leave-type/update-color ซึ่งถูกลบไปแล้ว จึงได้ 404 และสีไม่ถูกบันทึก)
     * @param int $id ID
     * @return array
     */
    public function actionUpdateColor($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $data = is_array($model->data_json) ? $model->data_json : [];

        foreach (['color', 'text_color'] as $key) {
            $value = $this->request->post($key);
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }

        $model->data_json = $data;
        $model->save(false);

        return [
            'status' => 'success',
            'data' => [
                'code' => $model->code,
                'data_json' => $model->data_json,
            ],
        ];
    }

    /**
     * Finds the MeetingStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return MeetingStatus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // ต้องล็อกด้วย name ด้วย เพราะตาราง categorise ใช้ร่วมกับชุดข้อมูลอื่น
        if (($model = MeetingStatus::findOne(['id' => $id, 'name' => 'meeting_status'])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
