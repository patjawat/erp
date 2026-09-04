<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
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

class MedicalController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $searchModel = new HelpdeskSearch([
            'repair_group' => 3,
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('emp');
        $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        // รวม andFilterWhere เข้าด้วยกันเพื่อลด query building overhead
        $dataProvider->query
            ->andFilterWhere(['name' => 'repair'])
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
            'auth_item' => 'medical'
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
            'active' => 'dashboard',
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }

    /**
     * แดชบอร์ดงานซ่อมแบบ V2 (กลุ่มเครื่องมือแพทย์) — /helpdesk/medical/dashboard-v2
     * โหมด HA ฉบับ 6 หมวด II-3: ความพร้อมใช้ / สอบเทียบ / บำรุงรักษาเชิงป้องกัน
     */
    public function actionDashboardV2()
    {
        $filters = $this->request->queryParams;
        return $this->render('@app/modules/helpdesk2/views/service/dashboard-ha', [
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'active' => 'dashboard-v2',
            'haContext' => 'medical',
            'drillRoute' => '/helpdesk/medical/drilldown',
            'reportRoute' => 'report',
            'dashboardParams' => RepairDashboardV2Helper::prepareViewParams(3, $filters, false, true),
        ]);
    }

    /**
     * รายการงานย่อยสำหรับ offcanvas drill-down (AJAX) — /helpdesk/medical/drilldown
     */
    public function actionDrilldown()
    {
        $params = $this->request->queryParams;
        $scope = (string) ($params['scope'] ?? 'all');
        [$tickets, $meta] = RepairDashboardV2Helper::drilldownTickets(3, $params, $scope);

        return $this->renderAjax('@app/modules/helpdesk2/views/service/_drilldown_list', [
            'tickets' => $tickets,
            'meta' => $meta,
        ]);
    }

    /**
     * รายงานคุณภาพ HA สำหรับพิมพ์ — /helpdesk/medical/report
     */
    public function actionReport()
    {
        $filters = $this->request->queryParams;
        $this->layout = false;
        return $this->renderPartial('@app/modules/helpdesk2/views/service/ha-report', [
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'haContext' => 'medical',
            'dashboardParams' => RepairDashboardV2Helper::prepareViewParams(3, $filters, false, true),
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
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_name', $searchModel->q],
            ['like', 'code', $searchModel->q],
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

        $baseQuery = $dataProvider->query;
        $equipStats = [
            'total' => (int) (clone $baseQuery)->count('DISTINCT asset.id'),
            'good' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => 'good'])->count('DISTINCT asset.id'),
            'fair' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => 'fair'])->count('DISTINCT asset.id'),
            'damaged' => (int) (clone $baseQuery)->andWhere(['asset.asset_condition' => ['damaged', 'worn']])->count('DISTINCT asset.id'),
            'repairing' => (int) (clone $baseQuery)->andWhere(['asset.asset_status' => 'repair'])->count('DISTINCT asset.id'),
            'waiting_dispose' => (int) (clone $baseQuery)->andWhere(['asset.asset_status' => 'wait_dispose'])->count('DISTINCT asset.id'),
            'total_value' => (float) ((clone $baseQuery)->sum(new Expression('COALESCE(asset.price, 0)'))) ?: 0.0,
        ];
        $dataProvider->setSort([
            'defaultOrder' => [
                'code' => 'SORT_DESC',
                'receive_date' => 'SORT_DESC',
            ],
        ]);

        return $this->render('@app/modules/helpdesk2/views/service/list_asset', [
            'active' => 'asset',
            'title' => 'ศูนย์เครื่องมือแพทย์',
            'icon' => '<i class="fa-solid fa-briefcase-medical fs-2"></i>',
            'listAssetType' => $listAssetType,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'equipStats' => $equipStats,
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
