<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\ModalHelper;
use yii\web\NotFoundHttpException;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockSearch;
use app\modules\inventory\models\StockEventSearch;

/**
 * StockController implements the CRUD actions for Stock model.
 */
class StockController extends Controller
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
     * Lists all Stock models.
     *
     * @return string
     */


    public function actionProduct()
    {
        $orderId = $this->request->get('order_id');
        $warehouse = Yii::$app->session->get('warehouse');

        $searchModel = new StockSearch([
            'order_id' => $orderId
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'title', $searchModel->q],
            ['LIKE', 'p.code', $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere(['warehouse_id' => $warehouse->id]);


        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('product/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('product/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


    public function actionInStock()
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse = Yii::$app->session->get('warehouse');
        if (!$warehouse) {
            return $this->redirect(['/inventory']);
        }
        $searchModel = new StockSearch([
            'warehouse_id' => $warehouse->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->query->groupBy('asset_item');

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('in_stock', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('in_stock', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }




    public function actionListStock($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $models = Stock::find()
            ->leftJoin('categorise p', 'p.code=stock.asset_item')
            ->where(['warehouse_id' => 1])
            // ->andWhere(['or', ['LIKE', 'title',$q]])
            ->limit(10)
            ->all();
        $data = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                // 'text' => $model->Avatar(false),
                // 'fullname' => $model->title,
                // 'avatar' => $model->Avatar(false)
            ];
        }
        return $data;
        // return [
        //     'results' => $data,
        //     'items' => $model
        // ];
    }


    /**
     * Displays a single Stock model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $warehouse = Yii::$app->session->get('warehouse');
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionViewStockCard($id)
    {
        $model = $this->findModel($id);

        // ----- รับ filter จาก GET -----
        $dateFrom = Yii::$app->request->get('date_from');
        $dateTo   = Yii::$app->request->get('date_to');
        // warehouse_id: query → Stock.warehouse_id → session (ไม่บังคับ session)
        $whId = Yii::$app->request->get('warehouse_id');
        if ($whId === '' || $whId === null) {
            $whId = (int) $model->warehouse_id;
            if (!$whId) {
                $sessWh = Yii::$app->session->get('warehouse');
                $whId = $sessWh ? (int) $sessWh->id : 0;
            }
        } else {
            $whId = (int) $whId;
        }

        // default: เดือนปัจจุบัน
        if (empty($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }
        if (empty($dateTo)) {
            $dateTo = date('Y-m-t');
        }

        $card = Stock::getStockCardData($model->asset_item, $whId, $dateFrom, $dateTo);

        $renderParams = [
            'model'    => $model,
            'card'     => $card,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'whId'     => $whId,
        ];

        if ($this->request->isAjax) {
            $content = $this->renderAjax('view_stock_card-v2', $renderParams);
            if ($this->request->isPjax) {
                return $content;
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title'   => $this->request->get('title'),
                'count'   => count($card['movements']),
                'content' => $content,
            ];
        }

        return $this->render('view_stock_card-v2', $renderParams);
    }

    /**
     * พิมพ์สต๊อกการ์ด A4 + ลายเซ็น
     */
    public function actionStockCardPrint($id)
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $model    = $this->findModel($id);
        $dateFrom = Yii::$app->request->get('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->get('date_to') ?: date('Y-m-t');
        $whId     = (int) (Yii::$app->request->get('warehouse_id') ?: ($warehouse->id ?? 0));

        $card = Stock::getStockCardData($model->asset_item, $whId, $dateFrom, $dateTo);

        $whName = '';
        if ($whId) {
            $wh = \app\modules\inventory\models\Warehouse::findOne($whId);
            $whName = $wh ? $wh->warehouse_name : '';
        }

        $this->layout = false;
        return $this->render('print_stock_card', [
            'model'    => $model,
            'card'     => $card,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'whName'   => $whName,
        ]);
    }

    /**
     * Export สต๊อกการ์ดเป็น Excel
     */
    public function actionStockCardExcel($id)
    {
        $warehouse = Yii::$app->session->get('warehouse');
        $model    = $this->findModel($id);
        $dateFrom = Yii::$app->request->get('date_from') ?: date('Y-m-01');
        $dateTo   = Yii::$app->request->get('date_to') ?: date('Y-m-t');
        $whId     = (int) (Yii::$app->request->get('warehouse_id') ?: ($warehouse->id ?? 0));

        $card = Stock::getStockCardData($model->asset_item, $whId, $dateFrom, $dateTo);
        $whName = '';
        if ($whId) {
            $wh = \app\modules\inventory\models\Warehouse::findOne($whId);
            $whName = $wh ? $wh->warehouse_name : '';
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock Card');
        $spreadsheet->getDefaultStyle()->getFont()->setName('TH Sarabun New')->setSize(14);

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'สต๊อกการ์ด — ' . $card['item_info']['code'] . ' ' . $card['item_info']['title']);
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'คลัง: ' . ($whName ?: '-') . '  |  ช่วงวันที่: '
            . \app\components\AppHelper::convertToThai($dateFrom) . ' — '
            . \app\components\AppHelper::convertToThai($dateTo));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // หัวตาราง row 4
        $headers = ['วันที่', 'เลขที่เอกสาร', 'รายการ', 'หมายเลขล็อต', 'รับเข้า (qty)',
                    'จ่าย รพ. (qty)', 'จ่าย รพ.สต. (qty)', 'ราคาต่อหน่วย', 'คงเหลือ (qty)'];
        foreach ($headers as $i => $h) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue($col . '4', $h);
        }
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:I4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:I4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle('A4:I4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row = 5;
        $balanceQty = $card['opening']['qty'];

        // Opening row
        $sheet->setCellValue('A' . $row, \app\components\AppHelper::convertToThai($dateFrom));
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('B' . $row, 'ยอดยกมา (' . ($card['opening']['source'] === 'monthly_close' ? 'จากปิดเดือนก่อน' : 'จากประวัติ stock_events') . ')');
        $sheet->setCellValue('I' . $row, $balanceQty);
        $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFF3CD');
        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $row++;

        // Movements
        foreach ($card['movements'] as $m) {
            $sheet->setCellValue('A' . $row, \app\components\AppHelper::convertToThai(date('Y-m-d', strtotime($m['movement_date']))));
            $sheet->setCellValue('B' . $row, $m['code']);
            $sheet->setCellValue('C' . $row, ($m['kind'] === 'IN' ? 'รับเข้า' : ($m['kind'] === 'OUT_HOSP' ? 'จ่ายให้ รพ.' : ($m['kind'] === 'OUT_BRANCH' ? 'จ่ายให้ รพ.สต.' : 'จ่ายอื่น ๆ'))) . ' ' . ($m['note'] ?? ''));
            $sheet->setCellValue('D' . $row, $m['lot_number']);
            $sheet->setCellValue('E' . $row, $m['kind'] === 'IN' ? $m['qty'] : null);
            $sheet->setCellValue('F' . $row, $m['kind'] === 'OUT_HOSP' ? $m['qty'] : null);
            $sheet->setCellValue('G' . $row, $m['kind'] === 'OUT_BRANCH' ? $m['qty'] : null);
            $sheet->setCellValue('H' . $row, $m['unit_price']);

            if ($m['kind'] === 'IN') $balanceQty += $m['qty'];
            elseif (in_array($m['kind'], ['OUT', 'OUT_HOSP', 'OUT_BRANCH'])) $balanceQty -= $m['qty'];
            $sheet->setCellValue('I' . $row, $balanceQty);
            $row++;
        }

        // Adjustment rows
        foreach ($card['adjustments'] as $a) {
            $sheet->setCellValue('A' . $row, \app\components\AppHelper::convertToThai($a['shown_date']));
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $sheet->setCellValue('B' . $row, 'ปรับยอด: ' . $a['note']);
            $sheet->setCellValue($a['delta_qty'] >= 0 ? 'E' . $row : 'F' . $row, abs($a['delta_qty']));
            $balanceQty += $a['delta_qty'];
            $sheet->setCellValue('I' . $row, $balanceQty);
            $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0E0');
            $row++;
        }

        // Closing row
        $sheet->setCellValue('A' . $row, \app\components\AppHelper::convertToThai($dateTo));
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('B' . $row, 'ยอดยกไป (คงเหลือสิ้นช่วง)');
        $sheet->setCellValue('I' . $row, $card['closing']['qty']);
        $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFF3CD');
        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);

        $bodyRange = 'A5:I' . $row;
        $sheet->getStyle($bodyRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('E5:I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'I') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'stock_card_' . $card['item_info']['code'] . '_' . $dateFrom . '_' . $dateTo . '.xlsx';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        Yii::$app->response->headers->set('Cache-Control', 'max-age=0');
        ob_start();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        return ob_get_clean();
    }

    public function actionUpdateStockCard($id)
    {
        $model= StockEvent::findOne($id);

         if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'url' => Url::to(['/inventory/stock/view-stock-card', 'id' => $model->product->id])
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

          if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_update_stock_card', [
                    'model' => $model,
                ]),
                'footer' => ModalHelper::modalFooterSaveClose()
            ];
        } else {
            return $this->render('_form_update_stock_card', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Creates a new Stock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stock();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Stock model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionStockCard()
    {
        // รับ warehouse_id จาก query (อิสระ ไม่ผูก session); fallback เป็น session warehouse ถ้ามี
        $warehouseId = Yii::$app->request->get('warehouse_id');
        if ($warehouseId === '' || $warehouseId === null) {
            $sessWh = Yii::$app->session->get('warehouse');
            $warehouseId = $sessWh ? (int) $sessWh->id : null;
        } else {
            $warehouseId = (int) $warehouseId;
        }

        $searchModel = new StockSearch();
        if ($warehouseId) {
            $searchModel->warehouse_id = $warehouseId;
        }
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
        if ($warehouseId) {
            $dataProvider->query->andWhere(['stock.warehouse_id' => $warehouseId]);
        }
        $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'asset_item', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->query->groupBy('asset_item');

        $warehouseOptions = \yii\helpers\ArrayHelper::map(
            \app\modules\inventory\models\Warehouse::find()
                ->orderBy(['warehouse_name' => SORT_ASC])->all(),
            'id', 'warehouse_name'
        );

        $renderParams = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'warehouseOptions' => $warehouseOptions,
            'currentWarehouseId' => $warehouseId,
        ];

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('stock_card', $renderParams),
            ];
        }
        return $this->render('stock_card', $renderParams);
    }

    /**
     * Deletes an existing Stock model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Stock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Stock the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stock::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
