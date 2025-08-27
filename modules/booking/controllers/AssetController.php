<?php

namespace app\modules\booking\controllers;

use yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use app\models\UploadForm;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\AssetHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use ruskid\csvimporter\MultipleImportStrategy;

/**
 * AssetController implements the CRUD actions for Asset model.
 */
class AssetController extends Controller
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
     * Lists all Asset models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $assetTypeItem = ['VEH'];
        $listAssetType = ArrayHelper::map(Categorise::find()->andWhere(['name' => 'asset_type'])->andWhere(['IN', 'code', $assetTypeItem])->all(), 'code', 'title');

        $searchModel = new AssetSearch([
             'asset_group_id' => 4,
             'asset_type_id' => $assetTypeItem
        ]);
$dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_name', $searchModel->q],
            ['like', 'code', $searchModel->q],
        ]);
        $dataProvider->query->andWhere('asset.deleted_at IS NULL');
        $dataProvider->query->andWhere('asset.license_plate IS NOT NULL');
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.budget_type')"), $searchModel->budget_type]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.method_get')"), $searchModel->method_get]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.po_number')"), $searchModel->po_number]);
        $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามรกลุ่มย่อย
        if (isset($searchModel->q_department) && isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา
        $dataProvider->query->andFilterWhere(['at.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'asset.code', $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.asset_name')"), $searchModel->q],
        ]);

        // ค้นหาตามอายุ
        if ($searchModel->price1 && !$searchModel->price2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('price = ' . $searchModel->price1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->price1 && $searchModel->price2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('price BETWEEN ' . $searchModel->price1 . ' AND ' . $searchModel->price2));
        }

        $dataProvider->setSort([
            'defaultOrder' => [
                'code' => 'SORT_DESC',
                'receive_date' => 'SORT_DESC',
            ],
        ]);

        return $this->render('index', [
             'active' => 'asset',
            'title' => 'ศูนย์คอมพิวเตอร์',
            'icon' => '<i class="fa-solid fa-computer fs-2"></i>',
            'listAssetType' => $listAssetType,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Asset();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $fsnAuto = $this->request->post('Asset');
            // ตรวจระหัสซ้ำ
            $checkCode = Asset::find()
                ->where(['code' => $model->code])
                ->andWhere(['<>', 'ref', $model->ref])
                ->andWhere(['not', ['code' => null]])
                ->andWhere(['not', ['code' => '']])
                ->one();
            //  return $checkCode;

            if ($checkCode) {
                $codeStatus = true;
            } else {
                $codeStatus = false;
            }

            //  return $model;
            // ตรวจสอลการลงปีงบประมาณ
            // return $model;

            if ($model->asset_group_id != 1 && $model->asset_group_id != 2) {  // ถ้าเป็นที่ดินไม่ต้องตรวจสอบปีงบประมาณ
                $model->data_json['budget_type'] == '' ? $model->addError('data_json[budget_type]', $requiredName) : null;
                $model->on_year == '' ? $model->addError('on_year', $requiredName) : null;
                $model->purchase == '' ? $model->addError('purchase', $requiredName) : null;
                $model->data_json['method_get'] == '' ? $model->addError('data_json[method_get]', $requiredName) : null;
                $model->data_json['vendor_id'] == '' ? $model->addError('data_json[vendor_id]', $requiredName) : null;

                $codeStatus ? $model->addError('code', 'หมายเลขครุภัณฑ์ซ้ำ') : null;

                // ถ้าสร้างรหัสอัตโนมัติ
                if (!isset($fsnAuto['fsn_auto']) || $fsnAuto['fsn_auto'] == '1') {
                }
                // ถ้ากำหนดรหัวเอง
                if (isset($fsnAuto['fsn_auto']) && $fsnAuto['fsn_auto'] == '0') {
                    $model->code == '' ? $model->addError('code', $requiredName) : null;
                }
            }
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Displays a single Asset model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['in', 'asset.code', $model->device_items != null ? $model->device_items : '']);

        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
        protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
