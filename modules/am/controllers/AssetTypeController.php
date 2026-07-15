<?php

namespace app\modules\am\controllers;

use app\modules\sm\models\AssetType;
use app\modules\sm\models\AssetTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;
use app\models\Categorise;

/**
 * AssetTypeController implements the CRUD actions for AssetType model.
 */
class AssetTypeController extends Controller
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

    public function beforeAction($action)
    {
        // กัน browser/proxy cache ผลลัพธ์ของ offcanvas ตั้งค่า (ต้องเห็นข้อมูลล่าสุดทันทีหลังแก้ไข)
        if (in_array($action->id, ['setting-panel', 'setting-panel-items', 'update', 'create', 'delete'], true)) {
            Yii::$app->response->getHeaders()
                ->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->set('Pragma', 'no-cache');
        }

        return parent::beforeAction($action);
    }

    /**
     * Offcanvas ตั้งค่า "ประเภท" จากเมนู — โหลด panel เต็ม (ตัวกรองกลุ่ม + ค้นหา + รายการ)
     */
    public function actionSettingPanel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = $this->settingConfig((string) $this->request->get('q', ''), (string) $this->request->get('filter', ''));

        return ['content' => $this->renderAjax('@app/modules/am/views/setting-quick/_panel', ['cfg' => $cfg])];
    }

    /**
     * Offcanvas ตั้งค่า "ประเภท" — เฉพาะแถวรายการ (ใช้รีเฟรชหลังค้นหา/กรอง/ลบ/บันทึก)
     */
    public function actionSettingPanelItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = $this->settingConfig((string) $this->request->get('q', ''), (string) $this->request->get('filter', ''));

        return ['content' => $this->renderAjax('@app/modules/am/views/setting-quick/_items', ['cfg' => $cfg])];
    }

    /**
     * config ร่วมของ setting-quick panel/items (กรอง name=asset_type ตามกลุ่ม + ค้นหาชื่อ/รหัส)
     */
    private function settingConfig($q, $filter)
    {
        $query = AssetType::find()->where(['name' => 'asset_type']);
        if ($filter !== '') {
            $query->andWhere(['group_id' => $filter]);
        }
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'title', $q], ['like', 'code', $q]]);
        }
        $items = $query->orderBy(['id' => SORT_DESC])->all();

        return [
            'entity' => 'type',
            'label' => 'ประเภททรัพย์สิน',
            'items' => $items,
            'q' => $q,
            'filterOptions' => \app\modules\am\components\AssetHelper::assetGroupOptions(),
            'filterLabel' => 'ทุกกลุ่มทรัพย์สิน',
            'selectedFilter' => $filter,
            'createUrl' => ['/am/asset-type/create', 'group' => $filter !== '' ? $filter : 'EQUIP'],
            'updateUrl' => ['/am/asset-type/update'],
            'deleteUrl' => ['/am/asset-type/delete'],
            'indexUrl' => ['/am/asset-type/index'],
        ];
    }

        public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetType();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->code == '' ? $model->addError('code', $requiredName) : null;
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }


    // ตรวจสอบความถูกต้อง
    public function actionValidatorxx()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Categorise();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {
            
            if (isset($model->title)) {
                $model->title  == "" ? $model->addError('title', $requiredName) : null;
            }
            
            if (isset($model->code) && $model->auto == 0) {
                $model->code  == "" ? $model->addError('code', $requiredName) : null;
                $checkCode = Categorise::findOne(['name' => 'asset_type','code' => $model->code]);
                if($checkCode){
                    $model->addError('code', 'รหัสซ้ำ');
                }
            }

        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    /**
     * Lists all AssetType models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // กลุ่มที่เลือกจาก dropdown (ครุภัณฑ์/สิ่งปลูกสร้าง/อาคาร) — default = ครุภัณฑ์
        $group = (string) $this->request->get('group', 'EQUIP');
        if (!\app\modules\am\components\AssetHelper::isEnabledAssetGroup($group)) {
            $group = 'EQUIP';
        }

        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['id' => SORT_DESC]];
        $dataProvider->query->where(['name' => 'asset_type', 'group_id' => $group]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->sort = ['defaultOrder' => ['id' => SORT_DESC]];
        $dataProviderGroup->query->andFilterWhere(['name' => 'asset_type', 'active' => true]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProviderGroup' => $dataProviderGroup,
            'dataProvider' => $dataProvider,
            'group' => $group,
        ]);
    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_type', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
    }

    /**
     * Creates a new AssetType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // สร้างประเภทให้กับกลุ่มที่กำลังดูอยู่ (default = ครุภัณฑ์) กัน param ปลอมด้วย whitelist
        $group = (string) $this->request->get('group', 'EQUIP');
        if (!\app\modules\am\components\AssetHelper::isEnabledAssetGroup($group)) {
            $group = 'EQUIP';
        }

        $model = new AssetType([
            'name' => 'asset_type',
            'group_id' => $group,
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)
            ])
        ];
    }

    /**
     * Updates an existing AssetType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return [
                'status' => 'success',
                'container' => '#sm-container',
            ];
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => $model->ref == '' ? substr(Yii::$app->getSecurity()->generateRandomString(), 10) : $model->ref
            ])
        ];

    }

    /**
     * Deletes an existing AssetType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        // ลบผ่าน offcanvas ตั้งค่า (AJAX) — ตอบ JSON ให้ JS รีเฟรชรายการได้ ไม่ต้องโหลดหน้า index
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success'];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the AssetType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


        /**
     * Displays a single Assetitem model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewType($id)
    {

        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_type', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view_type', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view_type', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'title' => $title ,

        ]);
    }


    
}
