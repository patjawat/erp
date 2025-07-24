<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\DateFilterHelper;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use app\modules\helpdesk2\models\HelpdeskSearch;

class MedicalController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 3,
            'date_filter' => 'this_month',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'repair_number', $searchModel->q],
            ['like', 'title', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        $dataProvider->query->andFilterWhere(['between', new \yii\db\Expression('DATE(created_at)'), AppHelper::convertToGregorian($searchModel->date_start), AppHelper::convertToGregorian($searchModel->date_end)]);


        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('@app/modules/helpdesk2/views/service/list', [
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }

    public function actionDashboard()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 3,
            'auth_item' => 'technician'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'repair_number', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('@app/modules/helpdesk2/views/service/dashboard', [
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }


    public function actionAsset()
    {
        $assetTypeItem = ['MED', 'SCI'];
        $listAssetType = ArrayHelper::map(Categorise::find()->andWhere(['name' => 'asset_type'])->andWhere(['IN', 'code', $assetTypeItem])->all(), 'code', 'title');

        $searchModel = new AssetSearch([
            'asset_group_id' => 4,
            'asset_type_id' => $assetTypeItem
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $q = trim($searchModel->q);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_name', $q],
            ['like', 'code', $q],
        ]);
        $dataProvider->query->andWhere('asset.deleted_at IS NULL');
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

        return $this->render('@app/modules/helpdesk2/views/service/list_asset', [
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'listAssetType' => $listAssetType,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
