<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use app\modules\helpdesk2\models\HelpdeskSearch;
use app\modules\helpdesk2\helpers\RepairDashboardV2Helper;

class GeneralController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new HelpdeskSearch([
            'repair_group' => 1,
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('emp');
        $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        // รวม andFilterWhere เข้าด้วยกันเพื่อลด query building overhead (helpdesk.name เพื่อไม่ให้กำกวมกับ employees)
        $dataProvider->query
            ->andFilterWhere(['helpdesk.name' => 'repair'])
            ->andFilterWhere(['=', new Expression("JSON_EXTRACT(helpdesk.data_json, '$.urgency')"), $searchModel->urgency])
            ->andFilterWhere([
                'between',
                new Expression('DATE(helpdesk.created_at)'),
                AppHelper::convertToGregorian($searchModel->date_start),
                AppHelper::convertToGregorian($searchModel->date_end)
            ]);

        // ย้าย search condition มาไว้ท้ายสุด และเช็ค empty ก่อน
        if (!empty($searchModel->q)) {
            $q = trim($searchModel->q);
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'repair_number', $q],
                ['like', 'title', $q],
                ['like', new Expression("JSON_EXTRACT(helpdesk.data_json, '$.repair_note')"), $q],
                ['like', new Expression("JSON_EXTRACT(helpdesk.data_json, '$.note')"), $q],
                ['like', new Expression("JSON_EXTRACT(helpdesk.data_json, '$.location')"), $q],
            ]);
        }

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('@app/modules/helpdesk2/views/service/list', [
            'active' => 'index',
            'title' => 'ศูนย์งานซ่อมบำรุง',
            'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 1,
            'auth_item' => 'technician'
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['helpdesk.name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'repair_number', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        return $this->render('@app/modules/helpdesk2/views/service/dashboard', [
            'active' => 'dashboard',
            'title' => 'ศูนย์งานซ่อมบำรุง',
            'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }

    /**
     * แดชบอร์ดงานซ่อมแบบ V2 (กลุ่มทั่วไป) — /helpdesk/general/dashboard-v2
     */
    public function actionDashboardV2()
    {
        return $this->render('@app/modules/helpdesk2/views/service/dashboard-v2', [
            'title' => 'ศูนย์งานซ่อมบำรุง',
            'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
            'active' => 'dashboard-v2',
            'dashboardParams' => RepairDashboardV2Helper::prepareViewParams(1),
        ]);
    }

    public function actionAsset()
    {

        $assetTypeItem = ['MED', 'SCI', 'COM'];
        $listsData = Categorise::find()->andWhere(['name' => 'asset_type', 'group_id' => 'EQUIP'])->andWhere(['NOT IN', 'code', $assetTypeItem])->all();
        $listAssetType = ArrayHelper::map($listsData, 'code', 'title');
        $assetTypeColumn = ArrayHelper::getColumn($listsData, 'code');
        
        $searchModel = new AssetSearch([
            'asset_group_id' => 4,
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        if (empty($searchModel->asset_type_id)) {
        $dataProvider->query->andWhere(['asset_type_id' => $assetTypeColumn]);
    }

        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_name', $searchModel->q],
            ['like', 'code', $searchModel->q],
        ]);

        $dataProvider->query->andWhere('asset.deleted_at IS NULL');
        //  $dataProvider->query->andFilterWhere(['asset_items.asset_type_id' => $searchModel->asset_type_id]);

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
            'title' => 'ศูนย์งานซ่อมบำรุง',
            'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
            'listAssetType' => $listAssetType,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'active' => 'asset',
        ]);
    }

    public function actionViewAsset($id)
    {
        $model = Asset::findOne($id);
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['in', 'asset.code', $model->device_items != null ? $model->device_items : '']);

        return $this->render('@app/modules/helpdesk2/views/service/view_asset', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionRepairHistory($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/_view_repair_history', [
            'model' => $model,
        ]);
    }
    //หนังสือเอกสารคู่มือต่างๆ
    public function actionDocument($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/_view_document', [
            'model' => $model,
        ]);
    }

    // การบำรุงรักษา
    public function actionMaintenance($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/maintenance', [
            'model' => $model,
        ]);
    }

    // พรบ.ต่อภาษี
    public function actionVehicleTax($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/vehicle_tax', [
            'model' => $model,
        ]);
    }

    // พรบ.ต่อภาษี
    public function actionCalibration($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/calibration', [
            'model' => $model,
        ]);
    }


    //การยืมคืน
    public function actionBorrow($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/borrow', [
            'model' => $model,
        ]);
    }

    //การเคลื่อนย้าย
    public function actionMove($id)
    {
        $model = $this->findModelAsset($id);
        return $this->render('@app/modules/am/views/equip/move', [
            'model' => $model,
        ]);
    }

     protected function findModelAsset($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
