<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomSearch;

/**
 * RoomController implements the CRUD actions for Room model.
 */
class RoomController extends Controller
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
     * Lists all Room models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RoomSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'meeting_room']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $searchModel->q],
            ['like', 'code', $searchModel->q],
            ['like', 'description', $searchModel->q],
            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.location'))"), $searchModel->q],
            ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.seat_capacity'))"), $searchModel->q],
        ]);

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
            if ($model->load($this->request->post()) && $model->save()) {
                // return $this->redirect(['view', 'id' => $model->id]);
                \Yii::$app->response->format = Response::FORMAT_JSON;

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
        // เก็บ data_json เดิมไว้ก่อน load() เพราะฟอร์มส่งกลับมาไม่ครบทุกคีย์
        $currentJson = is_array($model->data_json) ? $model->data_json : [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            // ฟอร์มแก้ไขห้องประชุมมีเฉพาะ location/seat_capacity/advance_booking/owner/room_status/color
            // ถ้าปล่อยให้ load() ทับ data_json ทั้งก้อน คีย์อื่น (เช่น room_accessory ที่ฟอร์ม LINE ใช้)
            // จะถูกลบทิ้งทุกครั้งที่กดบันทึก จึง merge ทับของเดิมแทน
            $model->data_json = array_merge(
                $currentJson,
                is_array($model->data_json) ? $model->data_json : []
            );

            if ($model->save()) {
                $this->CheckRoomAccessory($model);
                \Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'status' => 'success',
                    'container' => '#booking',
                ];
            }
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

    // ตรวจสอบว่ามีอุปกรณ์รายการใหม่หรือไม่
    protected function CheckRoomAccessory($model)
    {
        try {
            $data = $model->data_json['room_accessory'] ?? [];

            foreach ($data as $item) {
                if (!Categorise::findOne(['category_id' => $model->code, 'name' => 'room_accessory', 'title' => $item])) {  // เช็คว่ามีข้อมูลหรือยัง
                    $maxCode = Categorise::find()
                        ->select(['code' => new Expression('MAX(CAST(code AS UNSIGNED))')])
                        ->where(['name' => 'room_accessory'])
                        ->scalar();
                    // เดิมเขียนทับตัวแปร $model ทำให้ห้องประชุมที่ส่งเข้ามาหายไปกลางลูป
                    // และไม่ได้ใส่ category_id ทำให้เงื่อนไขค้นหาด้านบนไม่มีวันเจอ → สร้างซ้ำทุกครั้ง
                    $accessory = new Categorise();
                    $accessory->name = 'room_accessory';
                    $accessory->category_id = $model->code;
                    $accessory->code = ($maxCode + 1);
                    $accessory->title = $item;
                    $accessory->save(false);
                }
            }
        } catch (\Throwable $th) {
        }
    }

    /**
     * บันทึกสีพื้นหลัง/สีตัวหนังสือของห้องประชุมจากหน้าทะเบียน
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
     * Deletes an existing Room model.
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
     * Finds the Room model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Room the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // ต้องล็อกด้วย name ด้วย เพราะตาราง categorise ใช้ร่วมกับชุดข้อมูลอื่น
        // ถ้าไม่ล็อก id ของชุดข้อมูลอื่นจะถูกแก้ทับกลายเป็นห้องประชุมได้
        if (($model = Room::findOne(['id' => $id, 'name' => 'meeting_room'])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
