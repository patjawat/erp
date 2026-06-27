<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\WarehouseSearch;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\db\Expression;
use yii\web\Controller;

/**
 * Default controller for the `inventory-v2` module
 */
class DefaultController extends Controller
{
    /**
     * หน้าแรกโมดูลคลัง: ถ้า user มีสิทธิในคลังหลัก → ไป main-stock/dashboard
     * ถ้ามีสิทธิในคลังย่อย/สาขา → ไป sub-stock/dashboard ไม่เช่นนั้นแสดง index
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
      return $this->render('index');
    }

        public function actionMainStock()
    {
      return $this->render('main-stock');
    }


    public function actionOverview()
    {
        return $this->render('overview');
    }


    /**
     * เมนูนำทาง - แผนผังขั้นตอนการทำงาน (ใช้ view เดียวกับ index)
     * @return string
     */
    public function actionNavigation()
    {
        return $this->render('index');
    }

    /**
     * Dashboard คลังหลัก - redirect ไป main-stock/dashboard
     * @return \yii\web\Response
     */
    public function actionMainDashboard()
    {
        return $this->redirect(['/inventory-v2/main-stock/dashboard']);
    }

    /**
     * Dashboard คลังย่อย - redirect ไป sub-stock/dashboard
     * @return \yii\web\Response
     */
    public function actionSubStockDashboard()
    {
        return $this->redirect(['/inventory-v2/sub-stock/dashboard']);
    }

    /**
     * ตั้งค่าคลังสินค้า - จัดการคลังหลัก/คลังย่อย
     * โหลดข้อมูลแบบ batch เพื่อลดจำนวน query (แก้ N+1)
     */
    public function actionSetting()
    {
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['delete' => null]);
        $dataProvider->pagination->defaultPageSize = 12;
        $dataProvider->pagination->pageSizeParam = 'per-page';
        if ($dataProvider->sort !== null) {
            $dataProvider->sort->defaultOrder = ['warehouse_name' => SORT_ASC];
        }

        if (!\Yii::$app->user->can('admin')) {
            $userId = (string) \Yii::$app->user->id;
            $dataProvider->query->andWhere(
                new Expression("JSON_CONTAINS(COALESCE(data_json,'{}'), '\"$userId\"', '$.officer')")
            );
        }

        $models = $dataProvider->getModels();
        $departmentNames = [];
        $reqCounts = [];
        $imgUrls = [];
        $employees = [];
        $departmentOptions = [];

        $organizations = Organization::find()
            ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
            ->all();
        foreach ($organizations as $org) {
            $level = max(0, (int)($org->lvl ?? 0) - 1);
            $departmentOptions[$org->id] = str_repeat(' - ', $level) . ($org->name ?? 'ไม่ระบุ');
        }

        if (!empty($models)) {
            $warehouseIds = array_map(function ($m) { return $m->id; }, $models);
            $refs = array_unique(array_filter(array_column($models, 'ref')));
            $departmentIds = array_unique(array_filter(array_column($models, 'department')));
            $officerIds = [];
            foreach ($models as $m) {
                if (!empty($m->data_json['officer']) && is_array($m->data_json['officer'])) {
                    $officerIds = array_merge($officerIds, $m->data_json['officer']);
                }
            }
            $officerIds = array_unique(array_filter($officerIds));

            if (!empty($departmentIds)) {
                $orgs = Organization::find()->where(['id' => $departmentIds])->indexBy('id')->all();
                foreach ($orgs as $id => $org) {
                    $departmentNames[$id] = $org->name ?? 'ไม่ระบุ';
                }
            }

            if (!empty($warehouseIds)) {
                $counts = StockOrder::find()
                    ->select(['main_warehouse_id', 'COUNT(*) as cnt'])
                    ->where([
                        'main_warehouse_id' => $warehouseIds,
                        'order_type' => 'OUT',
                        'source_type' => 'REQUEST',
                        'status' => 'DRAFT',
                    ])
                    ->groupBy('main_warehouse_id')
                    ->asArray()
                    ->all();
                foreach ($counts as $row) {
                    $reqCounts[(int) $row['main_warehouse_id']] = (int) $row['cnt'];
                }
            }

            if (!empty($refs)) {
                $uploads = Uploads::find()
                    ->where(['ref' => $refs, 'name' => 'warehouse'])
                    ->indexBy('ref')
                    ->all();
                $defaultImg = \Yii::getAlias('@web') . '/images/store1.jpg';
                foreach ($uploads as $ref => $upload) {
                    $imgUrls[$ref] = FileManagerHelper::getImg($upload->id) ?: $defaultImg;
                }
            }

            if (!empty($officerIds)) {
                $employees = Employees::find()
                    ->where(['user_id' => $officerIds])
                    ->indexBy('user_id')
                    ->all();
            }
        }

        return $this->render('setting', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'departmentNames' => $departmentNames,
            'departmentOptions' => $departmentOptions,
            'reqCounts' => $reqCounts,
            'imgUrls' => $imgUrls,
            'employees' => $employees,
        ]);
    }
}
