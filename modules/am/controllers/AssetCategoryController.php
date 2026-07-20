<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\SiteHelper;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetCategory;
use app\modules\am\models\AssetCategorySearch;

/**
 * FsnController implements the CRUD actions for Fsn model.
 */
class AssetCategoryController extends Controller
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
                        'toggle-active' => ['POST'],
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

        // กัน browser/proxy cache ผลลัพธ์ของ endpoint ที่ offcanvas จัดการหมวดหมู่ใช้แบบ real-time
        // ไม่งั้นข้อมูลที่เพิ่งแก้ไขอาจไม่อัพเดตทันทีถ้ามี caching layer อยู่ระหว่างทาง (browser หรือ proxy)
        if (in_array($action->id, ['quick-list', 'quick-list-items', 'setting-panel', 'setting-panel-items', 'update', 'create', 'delete', 'toggle-active'], true)) {
            Yii::$app->response->getHeaders()
                ->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->set('Pragma', 'no-cache');
        }

        return parent::beforeAction($action);
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetCategory();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {

            // code ไม่บังคับ — หมวดสร้างเป็น "ร่าง/ตัวอย่าง" ได้ก่อน แล้วค่อยกำหนดรหัส FSN + เปิดใช้งานทีหลัง
            // (หมวดที่ยังไม่มีรหัส/ยังไม่เปิดใช้งาน จะผูกกับครุภัณฑ์ไม่ได้ — กันไว้ที่ Asset::validateCategoryReady)
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->category_id == '' ? $model->addError('category_id', $requiredName) : null;
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Lists all Fsn models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // กลุ่มที่เลือก (ครุภัณฑ์/สิ่งปลูกสร้าง/อาคาร) — default = ครุภัณฑ์
        $group = (string) $this->request->get('group', 'EQUIP');
        if (!\app\modules\am\components\AssetHelper::isEnabledAssetGroup($group)) {
            $group = 'EQUIP';
        }

        $searchModel = new AssetCategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_category']);

        // จำกัดหมวดตามกลุ่ม: หมวดที่ประเภทแม่ (category_id) อยู่ในกลุ่มที่เลือก
        $typeCodes = \app\models\Categorise::find()
            ->select('code')
            ->where(['name' => 'asset_type', 'group_id' => $group])
            ->column();
        $dataProvider->query->andWhere(['category_id' => $typeCodes ?: ['__none__']]);

        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $q],
            ['like', 'code', $q],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'group' => $group,
        ]);
    }

    /**
     * รายการหมวดหมู่ครุภัณฑ์แบบย่อ (พร้อมตัวกรอง) สำหรับ offcanvas จัดการหมวดหมู่ในฟอร์มเพิ่ม/แก้ไขครุภัณฑ์
     * ใช้ AssetCategorySearch ตัวเดียวกับหน้า /am/asset-category/index (หมวดทรัพย์สิน) เพื่อให้ผลลัพธ์ตรงกันเสมอ
     */
    public function actionQuickList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new AssetCategorySearch();
        $categories = $this->findQuickListCategories($searchModel);

        return [
            'content' => $this->renderAjax('_quick_list', [
                'categories' => $categories,
                'assetTypes' => (new AssetCategory())->listAssetType(),
                'q' => trim($searchModel->q ?? ''),
                'selectedCategoryId' => $searchModel->category_id,
            ]),
        ];
    }

    /**
     * แถวรายการหมวดหมู่แบบย่ออย่างเดียว (ไม่มีตัวกรอง) — ใช้รีเฟรชหลังค้นหา/กรองประเภท
     * โดยไม่แทนที่ input ค้นหา/dropdown กรอง เพื่อไม่ให้เสีย focus ระหว่างพิมพ์
     */
    public function actionQuickListItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new AssetCategorySearch();
        $categories = $this->findQuickListCategories($searchModel);

        return [
            'content' => $this->renderAjax('_quick_list_items', [
                'categories' => $categories,
            ]),
        ];
    }

    /**
     * ตรรกะกรอง/ค้นหาร่วมของรายการหมวดหมู่แบบย่อ (ใช้ทั้ง actionQuickList และ actionQuickListItems)
     */
    private function findQuickListCategories(AssetCategorySearch $searchModel)
    {
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_category']);
        $dataProvider->pagination = false;

        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $q],
            ['like', 'code', $q],
        ]);

        return $dataProvider->getModels();
    }

    /**
     * Offcanvas ตั้งค่า "หมวดหมู่" จากเมนู — โหลด panel เต็ม (ตัวกรอง + ค้นหา + รายการ)
     */
    public function actionSettingPanel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = $this->settingConfig((string) $this->request->get('q', ''), (string) $this->request->get('filter', ''));

        return ['content' => $this->renderAjax('@app/modules/am/views/setting-quick/_panel', ['cfg' => $cfg])];
    }

    /**
     * Offcanvas ตั้งค่า "หมวดหมู่" — เฉพาะแถวรายการ (ใช้รีเฟรชหลังค้นหา/กรอง/ลบ/บันทึก)
     */
    public function actionSettingPanelItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cfg = $this->settingConfig((string) $this->request->get('q', ''), (string) $this->request->get('filter', ''));

        return ['content' => $this->renderAjax('@app/modules/am/views/setting-quick/_items', ['cfg' => $cfg])];
    }

    /**
     * config ร่วมของ setting-quick panel/items (กรอง name=asset_category ตามประเภทแม่ + ค้นหาชื่อ/รหัส)
     */
    private function settingConfig($q, $filter)
    {
        $query = AssetCategory::find()->where(['name' => 'asset_category']);
        if ($filter !== '') {
            $query->andWhere(['category_id' => $filter]);
        }
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'title', $q], ['like', 'code', $q]]);
        }
        $items = $query->orderBy(['id' => SORT_DESC])->all();

        return [
            'entity' => 'category',
            'label' => 'หมวดหมู่ครุภัณฑ์',
            'items' => $items,
            'q' => $q,
            'filterOptions' => (new AssetCategory())->listAssetType(),
            'filterLabel' => 'ทุกประเภทครุภัณฑ์',
            'selectedFilter' => $filter,
            'createUrl' => ['/am/asset-category/create', 'group' => 'EQUIP'],
            'updateUrl' => ['/am/asset-category/update'],
            'deleteUrl' => ['/am/asset-category/delete'],
            'toggleUrl' => ['/am/asset-category/toggle-active'],
            'indexUrl' => ['/am/asset-category/index'],
        ];
    }

        public function actionListFsn()
    {
        $searchModel = new FsnSearch();
         $dataProvider = $searchModel->search($this->request->queryParams);


 if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('list_fsn', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]),
            ];
        } else {
            return $this->render('list_fsn', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
        }
       
    }

    /**
     * Displays a single Fsn model.
     * @param int $id ID
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
        } else {
            return $this->render('view', [
                'model' => $model,

            ]);
        }
    }


    /**
     * Creates a new Fsn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
     // กลุ่มที่กำลังจัดการ — ใช้กรองรายการ "ประเภท" ในฟอร์มให้ตรงกลุ่ม (default = ครุภัณฑ์)
     $group = (string) $this->request->get('group', 'EQUIP');
     if (!\app\modules\am\components\AssetHelper::isEnabledAssetGroup($group)) {
         $group = 'EQUIP';
     }

     $model = new AssetCategory([
        'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        'name' => 'asset_category',
        'group_id' => $group,
     ]);

        if ($this->request->isPost && $model->load($this->request->post()) )
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
        } else {
            $model->loadDefaultValues();

        }

     if($this->request->isAjax)
     {
        Yii::$app->response->format = Response::FORMAT_JSON;

         return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'group' => $group,
            ]),
        ];
     }else{

         return $this->render('create', [
             'model' => $model,
             'group' => $group,
            ]);
        }
    }

    /**
     * Updates an existing Fsn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        // $model->data_json =  ['asset_type' => '2','asset_type_text' => 'ครุภัณฑ์'];

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // return $model->save();
            return [
                'status' => 'success',
                'container' => '#am-container',
            ];
        } else {
            $model->loadDefaultValues();
        }

        // กลุ่มของหมวดนี้ = กลุ่มของประเภทแม่ (category_id → asset_type.group_id) เพื่อให้ dropdown ประเภทตรงกลุ่ม
        $parentType = $model->category_id
            ? \app\models\Categorise::find()->where(['name' => 'asset_type', 'code' => $model->category_id])->one()
            : null;
        $group = $parentType->group_id ?? 'EQUIP';

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('update', [
                'model' => $model,
                'group' => $group,
            ]),
        ];
    }

    /**
     * สลับสถานะเปิด/ปิดใช้งานหมวด (active) แบบ inline จาก offcanvas จัดการหมวดหมู่
     * ใช้ updateAttributes เพื่อข้าม validation/beforeSave (แก้เฉพาะ flag ไม่กระทบฟิลด์อื่น)
     * @param int $id ID
     * @return array
     */
    public function actionToggleActive($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        // รับสถานะที่ต้องการจาก UI (checkbox) ถ้าไม่ส่งมาให้สลับจากค่าเดิม (NULL/1 → 0, 0 → 1)
        $posted = $this->request->post('active', null);
        $active = $posted !== null
            ? ((int) $posted === 1 ? 1 : 0)
            : ((int) $model->active === 0 ? 1 : 0);

        $model->active = $active;
        $model->updateAttributes(['active']);

        return ['status' => 'success', 'active' => $active];
    }

    /**
     * Deletes an existing Fsn model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findModel($id)->delete();

        return ['status' => 'success'];
    }

    /**
     * Finds the Fsn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Fsn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetCategory::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
