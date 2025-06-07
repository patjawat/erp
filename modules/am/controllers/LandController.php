<?php

namespace app\modules\am\controllers;

use yii;
use yii\helpers\Json;
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
use app\components\UserHelper;
use app\components\AssetHelper;
use app\modules\am\models\Asset;
use app\modules\sm\models\Vendor;
use ruskid\csvimporter\CSVReader;
use yii\web\NotFoundHttpException;
use ruskid\csvimporter\CSVImporter;
use app\components\CategoriseHelper;
use app\modules\hr\models\UploadCsv;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use ruskid\csvimporter\MultipleImportStrategy;

class LandController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new AssetSearch([
            'asset_group' => 1
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=asset.asset_item');
        $dataProvider->query->andWhere('deleted_at IS NULL');

            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.budget_type')"), $searchModel->budget_type]);
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.method_get')"), $searchModel->method_get]);
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.po_number')"), $searchModel->po_number]);
            $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);


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
                    // 'service_start_time' => SORT_DESC
                ],
            ]);

            if ($this->request->get('view')) {
                SiteHelper::setDisplay($this->request->get('view'));
            }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
