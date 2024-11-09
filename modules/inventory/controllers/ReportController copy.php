<?php

namespace app\modules\inventory\controllers;
use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use app\components\AppHelper;
use yii\data\ArrayDataProvider;
// use yii2tech\spreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSummary;

// Microsoft Excel
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\StockSummarySearch;


class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new StockEventSearch([
            'thai_year' => AppHelper::YearBudget(),
            'receive_month' => date('m')
        ]);
         $dataProvider = $searchModel->search($this->request->queryParams);
         $dataProvider->query->groupBy('type_code');
         
         return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportExcel()
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/html; charset=UTF-8');
        $params = Yii::$app->request->queryParams;
        // return $this->findModel($params);
        $exporter = new Spreadsheet([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $this->findModel($params)
                // 'allModels' => [
                //     [
                //         'ที่' => '1.1',
                //         'รายการ' => '1.2',
                //         'สินค้าคงเหลือ' => '1.3',
                //         'วื้อระหว่างเดือน' => '1.4',
                //         'รวม' => '1.5',
                //         'สินค้าที่ใช้ไป' => 2,
                //         'column7' => '1.7',
                //     ],
                //     [
                //         'ที่' => '2.1',
                //         'รายการ' => '2.2',
                //         'column3' => '2.3',
                //         'column4' => '2.4',
                //         'column5' => '2.5',
                //         'column6' => '2.6',
                //         'column7' => '2.7',
                //     ],
                // ],
            ]),
            
            // 'headerColumnUnions' => [
            //     [
            //         'header' => 'สรุปวัสดุคงคลัง คลังพัสดุ',
            //         'offset' => 1,
            //         'length' => 2,
            //     ],
            //     [
            //         'header' => 'Skip 2 columns and group 2 next',
            //         'offset' => 2,
            //         'length' => 2,
            //     ],
            // ],
        ]);
        $exporter->renderCell('A2', 'Overridden serial column header');

        $exporter->save(Yii::getAlias('@webroot').'/myData.xlsx');
     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web').'/myData.xlsx'), ['class' => 'btn btn-info']);  //สร้าง link download

    }

    protected function findModel($params)
    {
        // return $params['warehouse_id'];
        $sql = "WITH t as (SELECT  
            t.title,
                i.category_id,
                so.code,
                w.warehouse_type,
                si.transaction_type,
                si.qty,
                si.unit_price,
                
                -- Extract month from the receive_date in JSON and convert it to integer for stock month
                MONTH(so.data_json->>'$.receive_date') AS stock_month,
                
                -- Calculate Thai year with adjustment based on receive_date month
                (IF(MONTH(so.data_json->>'$.receive_date') > 9, 
                    YEAR(so.data_json->>'$.receive_date') + 1, 
                    YEAR(so.data_json->>'$.receive_date')
                ) + 543) AS thai_year,
                
                -- Calculate sum for main warehouse 'IN' transactions minus 'OUT' transactions for sub/branch warehouses
                (
                    SUM(CASE 
                            WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                            THEN (si.qty * si.unit_price) 
                            ELSE 0 
                        END)
                ) AS sum_last,
                
                -- Sum of Purchase Orders (PO) where PO number is not NULL
                    SUM(
                    CASE 
                        WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                -- Calculate total for 'IN' transactions in branch warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                -- Calculate total for 'IN' transactions in sub-warehouse
                SUM(
                    CASE 
                        WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
                        THEN (si.qty * si.unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM 
                stock_events so
                LEFT OUTER JOIN stock_events si 
                    ON si.category_id = so.id AND si.name = 'order_item'
                LEFT OUTER JOIN categorise i 
                    ON i.code = si.asset_item AND i.name = 'asset_item'
                LEFT OUTER JOIN categorise t 
                    ON t.code = i.category_id AND t.name='asset_type'
                LEFT OUTER JOIN warehouses w 
                    ON w.id = si.warehouse_id
            WHERE i.category_id <> ''

            -- Group results by category ID
            GROUP BY 
                i.category_id  
            -- Order results by category ID in ascending order
            ORDER BY 
                i.category_id ASC) SELECT *,((t.sum_last + t.sum_po) - (t.sum_branch - t.sum_sub)) as total FROM t";
    
    $querys =  Yii::$app->db->createCommand($sql, [
        ':warehouse_id' => $params['warehouse_id'],
        ':receive_month' =>$params['receive_month'],
        ':thai_year' =>$params['thai_year'],
    ])->queryAll();

    $data = [];
    foreach ($querys as $key => $value) {
       $data[] = [
        'ที่' => $key+1,
        'รายการ' => $value['title'],
        'สินค้าคงเหลือ' => $value['sum_last'],
        'ซื้อระหว่างเดือน' => $value['sum_po'],
        'รวม' => ($value['sum_po'] + $value['sum_last']),
        'จ่ายส่วนของ รพ.สต.' => $value['sum_branch'],
        'จ่ายส่วนของโรงพยาบาล' => $value['sum_sub'],
        'รวม2' => ($value['sum_branch'] + $value['sum_sub']),
        'รวมสินค้าคงเหลือ' => $value['total'],
       ];
    }
    
    return $data;
    }
}
