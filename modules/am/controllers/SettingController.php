<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\sm\models\AssetType;
use app\modules\sm\models\AssetTypeSearch;
use app\modules\am\models\AssetItem;
use app\modules\am\models\AssetItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use app\modules\am\models\AmAssetNumberFormat;
use app\modules\am\models\AmAssetSequence;
use app\modules\am\services\AssetNumberGenerator;

class SettingController extends \yii\web\Controller
{
    /**
     * หน้าการกำหนดรูปแบบและลำดับ FSN ครุภัณฑ์
     */
    public function actionFsnFormat()
    {
        $formats = [];
        $sequences = [];
        $tableFormatsExists = Yii::$app->db->getSchema()->getTableSchema('am_asset_number_formats') !== null;
        $tableSeqsExists = Yii::$app->db->getSchema()->getTableSchema('am_asset_sequences') !== null;

        if ($tableFormatsExists) {
            $formats = AmAssetNumberFormat::find()->orderBy(['id' => SORT_ASC])->all();
        }
        if ($tableSeqsExists) {
            $sequences = AmAssetSequence::find()
                ->orderBy(['year' => SORT_DESC, 'category_id' => SORT_ASC])
                ->limit(200)
                ->all();
        }

        return $this->render('fsn-format', [
            'formats' => $formats,
            'sequences' => $sequences,
            'tableFormatsExists' => $tableFormatsExists,
            'tableSeqsExists' => $tableSeqsExists,
        ]);
    }

    /**
     * ตั้งค่ารูปแบบที่ใช้งาน (ใช้รูปแบบนี้เท่านั้น)
     * Supports AJAX (JSON status) and full-page fallback.
     */
    public function actionSetActiveFormat($id)
    {
        $tableExists = Yii::$app->db->getSchema()->getTableSchema(AmAssetNumberFormat::tableName()) !== null;
        if (!$tableExists) {
            return $this->jsonOrRedirect('error', 'ตารางรูปแบบยังไม่มีในระบบ');
        }
        $model = AmAssetNumberFormat::findOne($id);
        if (!$model) {
            return $this->jsonOrRedirect('error', 'ไม่พบรูปแบบนี้');
        }
        $model->setAsActive();
        return $this->jsonOrRedirect('success', 'ตั้งค่ารูปแบบ «' . $model->name . '» เป็นรูปแบบที่ใช้สร้างหมายเลขครุภัณฑ์แล้ว');
    }

    /**
     * เพิ่มรูปแบบ FSN ใหม่ (รองรับ modal GET = title+content, POST = JSON status)
     */
    public function actionFsnFormatCreate()
    {
        $this->ensureFsnFormatTable();
        $model = new AmAssetNumberFormat(['is_active' => 0]);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                $model->is_active = !empty($this->request->post('set_active')) ? 1 : 0;
                if ($model->save()) {
                    if ($model->is_active) {
                        $model->setAsActive();
                    }
                    return [
                        'status' => 'success',
                        'message' => 'เพิ่มรูปแบบ «' . $model->name . '» แล้ว',
                    ];
                }
                return \yii\widgets\ActiveForm::validate($model);
            }
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-plus me-1"></i> เพิ่มรูปแบบหมายเลข FSN',
                'content' => $this->renderAjax('fsn-format/form', ['model' => $model]),
            ];
        }

        return $this->render('fsn-format/form', ['model' => $model]);
    }

    /**
     * แก้ไขรูปแบบ FSN
     */
    public function actionFsnFormatUpdate($id)
    {
        $this->ensureFsnFormatTable();
        $model = $this->findFormatModel($id);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                $setActive = !empty($this->request->post('set_active'));
                if ($setActive) {
                    $model->is_active = 1;
                }
                if ($model->save()) {
                    if ($setActive) {
                        $model->setAsActive();
                    }
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกรูปแบบ «' . $model->name . '» แล้ว',
                    ];
                }
                return \yii\widgets\ActiveForm::validate($model);
            }
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-pen me-1"></i> แก้ไขรูปแบบ «' . \yii\helpers\Html::encode($model->name) . '»',
                'content' => $this->renderAjax('fsn-format/form', ['model' => $model]),
            ];
        }

        return $this->render('fsn-format/form', ['model' => $model]);
    }

    /**
     * ลบรูปแบบ FSN (POST only)
     */
    public function actionFsnFormatDelete($id)
    {
        $this->ensureFsnFormatTable();
        $model = $this->findFormatModel($id);
        $name = $model->name;
        $wasActive = (int) $model->is_active === 1;
        $count = AmAssetNumberFormat::find()->count();
        if ($count <= 1) {
            return $this->jsonOrRedirect('error', 'ต้องมีอย่างน้อย 1 รูปแบบ ไม่สามารถลบได้');
        }
        if (!$model->delete()) {
            return $this->jsonOrRedirect('error', 'ลบรูปแบบไม่สำเร็จ');
        }
        if ($wasActive) {
            $first = AmAssetNumberFormat::find()->orderBy(['id' => SORT_ASC])->one();
            if ($first) {
                $first->setAsActive();
            }
        }
        return $this->jsonOrRedirect('success', 'ลบรูปแบบ «' . $name . '» แล้ว');
    }

    /**
     * Helper: return JSON for AJAX or session-flash redirect for full page.
     */
    private function jsonOrRedirect($status, $message)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => $status, 'message' => $message];
        }
        Yii::$app->session->setFlash($status, $message);
        return $this->redirect(['fsn-format']);
    }

    protected function findFormatModel($id)
    {
        $model = AmAssetNumberFormat::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบรูปแบบนี้');
    }

    private function ensureFsnFormatTable()
    {
        if (Yii::$app->db->getSchema()->getTableSchema('am_asset_number_formats') === null) {
            throw new NotFoundHttpException('ตารางรูปแบบยังไม่มีในระบบ กรุณารัน migration');
        }
    }

    /**
     * เพิ่มลำดับ (ต่อปีต่อหมวด)
     */
    public function actionFsnSequenceCreate()
    {
        $this->ensureSequenceTable();
        $model = new AmAssetSequence(['current_sequence' => 0]);
        $model->year = (int) (\app\components\AppHelper::YearBudget());

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'message' => 'เพิ่มลำดับสำหรับหมวด «' . $model->category_id . '» ปี ' . $model->year . ' แล้ว',
                ];
            }
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-plus me-1"></i> เพิ่มลำดับ FSN',
                'content' => $this->renderAjax('fsn-sequence/form', ['model' => $model]),
            ];
        }

        return $this->render('fsn-sequence/form', ['model' => $model]);
    }

    /**
     * แก้ไขลำดับ
     */
    public function actionFsnSequenceUpdate($id)
    {
        $this->ensureSequenceTable();
        $model = $this->findSequenceModel($id);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post()) && $model->save()) {
                return ['status' => 'success', 'message' => 'บันทึกลำดับแล้ว'];
            }
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-pen me-1"></i> แก้ไขลำดับหมวด «' . \yii\helpers\Html::encode($model->category_id) . '»',
                'content' => $this->renderAjax('fsn-sequence/form', ['model' => $model]),
            ];
        }

        return $this->render('fsn-sequence/form', ['model' => $model]);
    }

    /**
     * ลบลำดับ
     */
    public function actionFsnSequenceDelete($id)
    {
        $this->ensureSequenceTable();
        $model = $this->findSequenceModel($id);
        $categoryId = $model->category_id;
        $year = $model->year;
        if (!$model->delete()) {
            return $this->jsonOrRedirect('error', 'ลบลำดับไม่สำเร็จ');
        }
        return $this->jsonOrRedirect('success', 'ลบลำดับหมวด «' . $categoryId . '» ปี ' . $year . ' แล้ว');
    }

    protected function findSequenceModel($id)
    {
        $model = AmAssetSequence::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบรายการลำดับนี้');
    }

    private function ensureSequenceTable()
    {
        if (Yii::$app->db->getSchema()->getTableSchema('am_asset_sequences') === null) {
            throw new NotFoundHttpException('ตารางลำดับยังไม่มีในระบบ กรุณารัน migration');
        }
    }

    public function actionIndex()
    {
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_type','category_id' => 3]);
        $dataProvider->query->orderBy(new \yii\db\Expression("SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 1), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 2), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 3), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 4), '.', -1) + 0"));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionItemsDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->post('expandRowKey');
        $typeModel = AssetType::findOne($id);
      
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'asset_item','category_id' => $typeModel->code]);

        return $this->renderAjax('list_items', [
            'model' => $typeModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,

            ]);
        }
    }

    public function actionViewItem($id)
    {
        $model = $this->findModel($id);
      
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_item', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('view_item', [
                'model' => $model,
            ]);
        }
    }

    public function actionOmit()
    {
         $sql_old = "SELECT i.id,g.title as group_name,a.code as asset_code,a.data_json->>'$.asset_name' as asset_name,t.code as type_code,t.title as type_title,i.code as item_code,i.title as item_title FROM asset a
         LEFT JOIN categorise i ON i.code = a.asset_item AND i.name = 'asset_item'
         LEFT JOIN categorise t ON t.code = i.category_id AND t.name = 'asset_type'
         LEFT JOIN categorise g ON g.code = a.asset_group AND g.name = 'asset_group'
         WHERE a.asset_group <> 1 AND t.code IS NULL
         LIMIT 10000;";

         $sql = "SELECT asset_item.id as asset_item_id,asset_item.code as asset_code,asset_item.title as asset_title,asset_type.code,asset_type.code as asset_code FROM categorise as asset_item
         LEFT JOIN categorise asset_type ON asset_type.code = asset_item.category_id AND asset_type.name = 'asset_type'
         WHERE  asset_item.name = 'asset_item' AND asset_type.code is NULL";
         
         $models = Yii::$app->db->createCommand($sql)->queryAll();
         if($this->request->isAjax)
         {
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'title' => 'รายการที่ยังไม่ระบุประเภท',
                 'content' => $this->renderAjax('omit',['models' => $models])
                ];
            }else{
                return $this->render('omit',['models' => $models]);
            }
    }


    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $name = $this->request->get('name');
        $assetType = AssetType::findOne(['id' => $id]);

        if($name == 'asset_type'){
            $model = new AssetType([
                'name' => $name,
                ]);
        }else{
            $model = new AssetType([
                'name' => $name,
                'group_id' => $assetType->category_id,
                'category_id' => $assetType->code,
                'data_json' => [
                    'service_life' => isset($assetType->data_json['service_life']) ? $assetType->data_json['service_life'] : '',
                    'depreciation' => isset($assetType->data_json['depreciation']) ? $assetType->data_json['service_life'] : '',
                    ]
                ]);
            }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#am-container',
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
                'container' => '#am-container',
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

    protected function findModel($id)
    {
        if (($model = AssetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionDelete($id)
    {
        $container = $this->request->get('container');
        $cache = $this->findModel($id);
        $model = $this->findModel($id)->delete();
        Yii::$app->response->format = Response::FORMAT_JSON;
        // return $cache;
        if($cache->name == "asset_type"){
            return $this->redirect(['/am/setting']);
        }else{
            return [
                'status' => 'success',
                'data' => $model,
                'container' => '#am-container',
                'close' => true,
            ];
        }
    }


}
