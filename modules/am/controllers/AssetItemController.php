<?php

namespace app\modules\am\controllers;

use app\components\SiteHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\am\models\AssetItemSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AssetItemController implements the CRUD actions for AssetItem model.
 */
class AssetItemController extends Controller
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
        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all AssetItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AssetItem model.
     * @param string $id รหัสทรัพย์สิน
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new AssetItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetItem([
            'asset_group_id' => $this->request->get('asset_type_id'),
            'asset_category_id' => $this->request->get('category_id'),
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->id = $model->NextId();
                $model->asset_type_id = $model->asset_group_id;
                $model->save(false);

                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AssetItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id รหัสทรัพย์สิน
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        //$model->asset_type_id = $model->type->code;
        //$model->category_id = $model->category->code;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionGetAssetType()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $group_id = $parents[0];
                $out = Categorise::find()
                    ->where(['group_id' => $group_id, 'name' => 'asset_type'])
                    ->select(['code as id', 'title as name'])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => ''];
            }
        }

        return ['output' => '', 'selected' => ''];
    }

    public function actionGetAssetCategory()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];

            if ($parents != null) {
                $type_id = $parents[0];
                // เฉพาะหมวดที่ "พร้อมใช้งาน" = มีรหัส FSN (code ไม่ว่าง) และเปิดใช้งาน (active <> 0)
                // หมวดร่าง/ตัวอย่าง (ยังไม่กำหนดรหัส หรือปิดใช้งาน) จะไม่โผล่ใน picker — จัดการได้ที่แผง ⚙️
                $categories = Categorise::find()
                    ->where(['category_id' => $type_id, 'name' => 'asset_category'])
                    ->andWhere(['not', ['code' => null]])
                    ->andWhere(['<>', 'code', ''])
                    // NULL = ถือว่าเปิดใช้งาน · เฉพาะ active=0 เท่านั้นที่เป็น "ร่าง" แล้วซ่อนจาก picker
                    ->andWhere('(active IS NULL OR active <> 0)')
                    ->select(['code', 'title'])
                    ->asArray()
                    ->all();

                // ต่อท้ายรหัสหมวดหมู่ (categorise.code) ในชื่อรายการตัวเลือก เช่น "คอมพิวเตอร์และอุปกรณ์ : COM"
                $out = array_map(function ($category) {
                    return [
                        'id' => $category['code'],
                        'name' => $category['title'] . ' : ' . $category['code'],
                    ];
                }, $categories);

                return ['output' => $out, 'selected' => ''];
            }
        }

        return ['output' => '', 'selected' => ''];
    }

    /**
     * ดึงรหัสหมวดหมู่ครุภัณฑ์ (categorise.code, name=asset_category)
     * ใช้เติมช่อง FSN อัตโนมัติในฟอร์มเพิ่มทะเบียนทรัพย์สินใหม่ เมื่อเลือกหมวดหมู่
     */
    public function actionGetCategoryFsn()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $code = Yii::$app->request->get('code');
        if (!$code) {
            return ['fsn' => ''];
        }

        $category = Categorise::find()->where(['name' => 'asset_category', 'code' => $code])->one();
        if (!$category) {
            return ['fsn' => ''];
        }

        return ['fsn' => $category->code];
    }

    /**
     * ดึงค่า default ของหมวด (categorise.data_json, name=asset_category) — useful_life,
     * depreciation_rate, allow_other_note — ใช้ auto-fill ค่าเสื่อมในฟอร์มสิ่งปลูกสร้าง
     * เมื่อผู้ใช้เลือก "หมวด" (โครงสร้างเดียวกับครุภัณฑ์ ต่างแค่กลุ่ม)
     */
    public function actionCategoryDefaults()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $code = trim((string) Yii::$app->request->get('code', ''));
        $empty = ['useful_life' => null, 'depreciation_rate' => null, 'allow_other_note' => false];
        if ($code === '') {
            return ['status' => 'success', 'defaults' => $empty];
        }

        $category = Categorise::find()->where(['name' => 'asset_category', 'code' => $code])->one();
        if (!$category) {
            return ['status' => 'success', 'defaults' => $empty];
        }

        // ค่าเสื่อมย้ายมาเป็นคอลัมน์จริงบน categorise แล้ว (ไม่เก็บใน data_json)
        $json = is_array($category->data_json)
            ? $category->data_json
            : (is_string($category->data_json) ? (json_decode($category->data_json, true) ?: []) : []);

        return [
            'status' => 'success',
            'defaults' => [
                'useful_life'       => $category->useful_life,
                'depreciation_rate' => $category->depreciation_rate,
                'allow_other_note'  => !empty($json['allow_other_note']),
            ],
        ];
    }

    /**
     * Deletes an existing AssetItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id รหัสทรัพย์สิน
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionListItem()
    {
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'code', $searchModel->q],
            ['LIKE', 'title', $searchModel->q],
        ]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_item', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }

        return $this->render('list_item', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetItem();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->asset_group_id == '' ? $model->addError('asset_group_id', $requiredName) : null;
            $model->asset_category_id == '' ? $model->addError('asset_category_id', $requiredName) : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Finds the AssetItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id รหัสทรัพย์สิน
     * @return AssetItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
