<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\components\InventoryTelegramNotify;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockOrderSearch;
use app\modules\inventoryV2\models\Warehouse;
use kartik\mpdf\Pdf;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class IssueController extends Controller
{
    /**
     * แสดงรายการใบขอเบิกที่รออนุมัติ อนุมัติแล้ว (รอคลังจ่าย) และที่จ่ายแล้ว
     */
    public function actionIndex()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->query->andWhere([
            'order_type' => 'OUT',
            'source_type' => 'REQUEST',
        ])->andWhere(['status' => [
            StockOrder::STATUS_PENDING,
            StockOrder::STATUS_APPROVED,
            StockOrder::STATUS_CONFIRMED,
        ]]);
        $dataProvider->query->with(['mainWarehouse', 'subWarehouse']);

        // เห็นหมดทุกคลัง = admin หรือผู้มีสิทธิ์ warehouse; นอกนั้นจำกัดเฉพาะคลังที่ user ถูกกำหนดเป็นผู้รับผิดชอบคลัง (officer)
        // ให้ตรงกับ badge เมนู: ผู้รับผิดชอบคลังเห็นทุกใบที่เข้าคลังของตน โดยไม่ผูกกับแผนก/ฝ่ายที่มีสิทธิเบิก
        $isAdmin = Yii::$app->user->can('admin');
        $canSeeAllWarehouses = $isAdmin || Yii::$app->user->can('warehouse');
        $accessibleWarehouses = Warehouse::findMainWarehousesForReceive();
        if (!$canSeeAllWarehouses) {
            $dataProvider->query->andWhere(['main_warehouse_id' => ArrayHelper::getColumn($accessibleWarehouses, 'id')]);
        }

        // วันที่เบิก (order_date)
        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        if ($start !== null && $start !== '') {
            $dataProvider->query->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $dataProvider->query->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }

        // วันที่จ่าย (updated_at)
        $confStart = AppHelper::convertToGregorian($searchModel->confirmed_date_start);
        $confEnd = AppHelper::convertToGregorian($searchModel->confirmed_date_end);
        if ($confStart !== null && $confStart !== '') {
            $dataProvider->query->andWhere(['>=', 'updated_at', $confStart . ' 00:00:00']);
        }
        if ($confEnd !== null && $confEnd !== '') {
            $dataProvider->query->andWhere(['<=', 'updated_at', $confEnd . ' 23:59:59']);
        }

        $dataProvider->sort->defaultOrder = ['order_date' => SORT_DESC, 'id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        $mainWarehouses = $canSeeAllWarehouses
            ? ['' => 'ทุกคลัง'] + ArrayHelper::map(
                Warehouse::find()
                    ->where(['warehouse_type' => 'MAIN'])
                    ->andWhere(['or', ['delete' => null], ['delete' => '']])
                    ->orderBy('warehouse_name')
                    ->all(),
                'id',
                'warehouse_name'
            )
            : ['' => 'ทุกคลัง'] + ArrayHelper::map($accessibleWarehouses, 'id', 'warehouse_name');
        $subWarehouses = ['' => 'ทุกหน่วยงาน'] + ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'SUB'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])
                ->orderBy('warehouse_name')
                ->all(),
            'id',
            'warehouse_name'
        );

        $statusLabels = array_merge(
            ['' => 'ทุกสถานะ'],
            StockOrder::optsStatusLabels()
        );

        // ยอดใบเบิกที่ยังรอ (รออนุมัติ/รอจ่าย) สำหรับ badge เมนู — ใช้ scope สิทธิ์เดียวกับตาราง เคารพคลังที่จ่ายที่เลือกด้วย
        $pendingCountQuery = StockOrder::find()
            ->where([
                'order_type'  => 'OUT',
                'source_type' => 'REQUEST',
                'status'      => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ]);
        if (!$canSeeAllWarehouses) {
            $pendingCountQuery
                ->andWhere(['main_warehouse_id' => ArrayHelper::getColumn($accessibleWarehouses, 'id')]);
        }
        if (!empty($searchModel->main_warehouse_id)) {
            $pendingCountQuery->andWhere(['main_warehouse_id' => (int) $searchModel->main_warehouse_id]);
        }
        $issuePendingCount = (int) $pendingCountQuery->count();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
            'statusLabels' => $statusLabels,
            'issuePendingCount' => $issuePendingCount,
        ]);
    }

    /**
     * หน้าจอสำหรับดำเนินการจ่าย — ระบบจัดสรรข้าม Lot ตาม FIFO เมื่อกดยืนยันจ่าย
     * เฉพาะใบที่ status = APPROVED เท่านั้นที่กดจ่ายได้
     */
    public function actionProcess($id)
    {
        $model = StockOrder::find()
            ->with(['stockDetails', 'stockDetails.item', 'stockDetails.item.categoryType'])
            ->where(['id' => $id])
            ->one();
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
        }

        if (!in_array($model->status, [StockOrder::STATUS_APPROVED, StockOrder::STATUS_CONFIRMED])) {
            Yii::$app->session->setFlash('warning', 'เฉพาะใบที่หัวหน้าอนุมัติแล้ว (สถานะอนุมัติแล้ว) จึงจะดำเนินการจ่ายได้');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isPost) {
            if ($model->status !== StockOrder::STATUS_APPROVED) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'ใบนี้จ่ายของไปแล้ว'];
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = Yii::$app->request->post('Issue', []);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $lockedOrder = InventoryService::lockOrder($model->id);
                if ((string) $lockedOrder['status'] !== StockOrder::STATUS_APPROVED) {
                    throw new \Exception('ใบนี้ถูกดำเนินการโดยผู้ใช้อื่นแล้ว กรุณาโหลดหน้าใหม่');
                }
                $processedCount = 0;
                $affectedStockPools = [];
                $issueAllocationSummary = [];
                // เก็บรายการเดิมของใบเบิกไว้ก่อนเริ่มจ่าย เพื่อให้ลบรายการที่ผู้ใช้
                // กดถังขยะออกจากหน้าจ่ายได้จริง รายการที่ถูกปิด input จะไม่อยู่ใน
                // payload แต่หากปล่อย StockDetail เดิมไว้ เมื่อหัวเอกสารเป็น CONFIRMED
                // รายงานที่รวม stock_detail.qty จะยังนับเป็นยอดจ่ายอยู่
                $originalDetailIds = array_map('intval', ArrayHelper::getColumn($model->stockDetails, 'id'));
                $processedOriginalDetailIds = [];

                foreach ($data as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    // 1. ตรวจสอบเบื้องต้น: ถ้าจำนวนเป็น 0 หรือไม่มีข้อมูลให้ข้าม
                    $qtyToProcess = (float)($item['qty_issued'] ?? 0);
                    if ($qtyToProcess <= 0) continue;

                    $detailId = $item['detail_id'] ?? null;
                    $isNewRow = ($detailId === 'new' || $detailId === '' || $detailId === null);

                    if ($isNewRow) {
                        $itemCode = trim((string) ($item['item_code'] ?? ''));
                        if ($itemCode === '') {
                            throw new \Exception('รายการเพิ่มเติม: กรุณาเลือกวัสดุ');
                        }
                        $detail = new StockDetail();
                        $detail->stock_order_id = $model->id;
                        $detail->item_code = $itemCode;
                    } else {
                        $detail = StockDetail::findOne(['id' => $detailId, 'stock_order_id' => $model->id]);
                        if (!$detail) {
                            throw new \Exception('ไม่พบรายการวัสดุในใบเบิกนี้');
                        }
                        $processedOriginalDetailIds[] = (int) $detail->id;
                    }

                    // ล็อก item + balance + source lot ก่อนอ่านยอด ป้องกันผู้ใช้อื่น
                    // จ่าย/คืนวัสดุ pool เดียวกันระหว่าง transaction นี้
                    InventoryService::lockStockPool(
                        $detail->item_code,
                        $model->main_warehouse_id
                    );

                    $reservedAhead = InventoryService::reservedAheadQty(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        $model->id
                    );
                    $actualItemBalance = (float) StockBalance::find()
                        ->where(['item_code' => $detail->item_code, 'warehouse_id' => $model->main_warehouse_id])
                        ->sum('balance_qty');
                    $availableForThisOrder = max(0.0, $actualItemBalance - $reservedAhead);
                    if ($qtyToProcess > $availableForThisOrder + 0.000001) {
                        throw new \Exception("พัสดุรหัส {$detail->item_code} คงเหลือจริง {$actualItemBalance} แต่มีใบเบิกก่อนหน้าจองไว้ {$reservedAhead} จึงพร้อมจ่ายสำหรับใบนี้ {$availableForThisOrder}");
                    }

                    // 2. จัดสรรข้าม Lot อัตโนมัติตาม FIFO
                    $sourceLots = StockDetail::find()
                        ->joinWith('stockOrder')
                        ->where(['stock_detail.item_code' => $detail->item_code])
                        ->andWhere(['stock_order.status' => StockOrder::STATUS_CONFIRMED])
                        ->andWhere(['or',
                            ['and',
                                ['stock_order.main_warehouse_id' => $model->main_warehouse_id],
                                ['or',
                                    ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                                    ['and',
                                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST],
                                        ['>', 'stock_detail.qty', 0],
                                    ],
                                ],
                            ],
                            ['and',
                                ['stock_order.order_type' => [
                                    StockOrder::ORDER_TYPE_TRANSFER,
                                    StockOrder::ORDER_TYPE_OUT,
                                ]],
                                ['stock_order.sub_warehouse_id' => $model->main_warehouse_id],
                                ['>', 'stock_detail.qty', 0],
                            ],
                        ])
                        ->andWhere(['>', 'stock_detail.remain_qty', 0])
                        ->orderBy(['stock_order.order_date' => SORT_ASC, 'stock_detail.id' => SORT_ASC])
                        ->all();

                    $tempQty = $qtyToProcess;
                    $lotGroups = [];

                    foreach ($sourceLots as $sourceIn) {
                        if ($tempQty <= 0) break;

                        $take = min($tempQty, (float)$sourceIn->remain_qty);
                        $sourceIn->remain_qty -= $take;
                        if (!$sourceIn->save(false)) {
                            throw new \Exception("ไม่สามารถปรับปรุงยอดคงเหลือของ Lot {$sourceIn->lot_number}");
                        }

                        $lot = (string) $sourceIn->lot_number;
                        if (!isset($lotGroups[$lot])) {
                            $lotGroups[$lot] = ['qty' => 0.0, 'value' => 0.0, 'allocations' => []];
                        }
                        $lotGroups[$lot]['qty'] += $take;
                        $lotGroups[$lot]['value'] += $take * (float) $sourceIn->unit_price;
                        $lotGroups[$lot]['allocations'][] = [
                            'source_detail_id' => (int) $sourceIn->id,
                            'source_order_id' => (int) $sourceIn->stock_order_id,
                            'lot_number' => $lot,
                            'qty' => (float) $take,
                        ];
                        $tempQty -= $take;
                    }

                    if ($tempQty > 0) {
                        throw new \Exception("พัสดุรหัส {$detail->item_code} รวมทุก Lot มีไม่พอจ่าย (ขาดอีก {$tempQty})");
                    }

                    $firstLot = true;
                    foreach ($lotGroups as $lot => $group) {
                        InventoryService::updateBalance($detail->item_code, $model->main_warehouse_id, $group['qty'], 'OUT', $lot);
                        if ($model->sub_warehouse_id) {
                            InventoryService::updateBalance($detail->item_code, $model->sub_warehouse_id, $group['qty'], 'IN', $lot);
                        }

                        $issuedDetail = $firstLot ? $detail : new StockDetail();
                        $issuedDetail->stock_order_id = $model->id;
                        $issuedDetail->item_code = $detail->item_code;
                        $issuedDetail->qty = $group['qty'];
                        $issuedDetail->lot_number = $lot;
                        $issuedDetail->unit_price = $group['qty'] > 0 ? $group['value'] / $group['qty'] : 0;
                        $issuedDetail->remain_qty = $model->sub_warehouse_id ? $group['qty'] : 0;
                        $issuedDetail->data_json = json_encode([
                            'fifo_allocations' => $group['allocations'],
                            'issued_qty' => (float) $group['qty'],
                            'allocation_recorded_at' => date('Y-m-d H:i:s'),
                            'automatic_multi_lot_fifo' => 1,
                        ], JSON_UNESCAPED_UNICODE);
                        if (!$issuedDetail->save(false)) {
                            throw new \Exception("ไม่สามารถบันทึกรายการจ่าย Lot {$lot}");
                        }
                        $firstLot = false;
                    }
                    $issueAllocationSummary[] = [
                        'item_code' => (string) $detail->item_code,
                        'requested_qty' => (float) $qtyToProcess,
                        'lots' => array_map(static function ($lot, $group) {
                            return [
                                'lot_number' => (string) $lot,
                                'qty' => (float) $group['qty'],
                                'unit_price' => $group['qty'] > 0 ? (float) ($group['value'] / $group['qty']) : 0.0,
                            ];
                        }, array_keys($lotGroups), $lotGroups),
                    ];
                    $affectedStockPools[$model->main_warehouse_id . '|' . $detail->item_code] = [(int) $model->main_warehouse_id, (string) $detail->item_code];
                    if ($model->sub_warehouse_id) {
                        $affectedStockPools[$model->sub_warehouse_id . '|' . $detail->item_code] = [(int) $model->sub_warehouse_id, (string) $detail->item_code];
                    }
                    $processedCount++;
                }

                if ($processedCount === 0) {
                    throw new \Exception('กรุณาระบุรายการที่ต้องการจ่ายอย่างน้อย 1 รายการ');
                }

                // ทำให้รายละเอียดของเอกสารตรงกับรายการที่จ่ายจริงก่อนยืนยันเอกสาร
                // เพื่อให้ Stock Card, Dashboard และรายงานยอดจ่ายไม่รวมแถวที่ยกเลิก
                $cancelledDetailIds = array_diff($originalDetailIds, $processedOriginalDetailIds);
                if (!empty($cancelledDetailIds)) {
                    StockDetail::deleteAll([
                        'id' => array_values($cancelledDetailIds),
                        'stock_order_id' => $model->id,
                    ]);
                }

                // 4.5 ถ้ายังไม่มี "ผู้จ่ายพัสดุ" ให้เซ็ตจาก user ปัจจุบัน (ดึงตำแหน่งจากระบบพนักงาน)
                $disbursing = $model->getIssueSignature('disbursing');
                if (empty($disbursing['name'])) {
                    $userId = Yii::$app->user->id;
                    $emp = $userId ? Employees::findOne(['user_id' => $userId]) : null;
                    if ($emp) {
                        $disbData = StockOrder::getEmployeeNameAndPosition($emp->id);
                        $model->setIssueSignatures([
                            'disbursing' => [
                                'name' => $disbData['name'],
                                'position' => $disbData['position'],
                                'date' => date('Y-m-d'),
                                'emp_id' => $emp->id,
                            ],
                        ]);
                    }
                }

                // 5. เปลี่ยนสถานะหัวเอกสารใบเบิก และตั้งวันที่จ่าย (เก็บใน data_json เพื่อไม่ถูกเขียนทับเมื่อแก้ค่าอื่น)
                $model->status = 'CONFIRMED';
                $confirmedDate = $this->request->post('confirmed_date', date('Y-m-d'));
                $confirmedDate = trim((string) $confirmedDate);
                if (strpos($confirmedDate, '/') !== false) {
                    $yMd = AppHelper::convertToGregorian($confirmedDate);
                    $ts = $yMd ? strtotime($yMd . ' 12:00:00') : time();
                } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $confirmedDate, $m)) {
                    $ts = strtotime($m[1] . '-' . $m[2] . '-' . $m[3] . ' 12:00:00');
                } else {
                    $ts = time();
                }
                $model->setDisbursementDate($ts);
                // เก็บเวลาที่ตัดสต๊อกจริงแยกจากวันที่จ่ายทางเอกสาร
                $model->setStockPostedAt(time());
                $model->updated_at = date('Y-m-d H:i:s', $ts); // ใช้ filter วันที่จ่ายใน index ได้
                if (!$model->save(false)) {
                    throw new \Exception("ไม่สามารถบันทึกสถานะใบเบิกได้");
                }

                foreach ($affectedStockPools as [$warehouseId, $itemCode]) {
                    InventoryService::assertBalanceMatchesFifo($itemCode, $warehouseId);
                }

                $transaction->commit();

                try {
                    InventoryTelegramNotify::notifyRequisitionDisbursed($model);
                } catch (\Throwable $notifyError) {
                    Yii::warning(
                        'Issue notification failed for stock_order_id=' . $model->id . ': ' . $notifyError->getMessage(),
                        __METHOD__
                    );
                }

                return [
                    'success' => true,
                    'message' => 'บันทึกการจ่ายเรียบร้อยแล้ว',
                    'fifo_allocations' => $issueAllocationSummary,
                ];
            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ];
            }
        }

        return $this->render('process', ['model' => $model]);
    }

    /**
     * บันทึกข้อมูลผู้ลงนามใบเบิก (ผู้เบิก, ผู้จ่ายพัสดุ, ผู้เห็นชอบ, ผู้รับวัสดุ, ผู้สั่งจ่าย)
     * รองรับ AJAX: คืน JSON { success, message } แล้วแสดง SweetAlert ปิดอัตโนมัติ 2 วินาที
     */
    public function actionSaveIssueSignatures($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $signatures = [
                'requester' => Yii::$app->request->post('IssueSignatures', [])['requester'] ?? [],
                'disbursing' => Yii::$app->request->post('IssueSignatures', [])['disbursing'] ?? [],
                'approver' => Yii::$app->request->post('IssueSignatures', [])['approver'] ?? [],
                'recipient' => Yii::$app->request->post('IssueSignatures', [])['recipient'] ?? [],
                'authorizer' => Yii::$app->request->post('IssueSignatures', [])['authorizer'] ?? [],
            ];
            $model->setIssueSignatures($signatures);
            if ($model->save(false)) {
                return ['success' => true, 'message' => 'บันทึกข้อมูลผู้ลงนามเรียบร้อยแล้ว'];
            }
            return ['success' => false, 'message' => 'ไม่สามารถบันทึกได้'];
        }
        if (Yii::$app->request->isPost) {
            $signatures = [
                'requester' => Yii::$app->request->post('IssueSignatures', [])['requester'] ?? [],
                'disbursing' => Yii::$app->request->post('IssueSignatures', [])['disbursing'] ?? [],
                'approver' => Yii::$app->request->post('IssueSignatures', [])['approver'] ?? [],
                'recipient' => Yii::$app->request->post('IssueSignatures', [])['recipient'] ?? [],
                'authorizer' => Yii::$app->request->post('IssueSignatures', [])['authorizer'] ?? [],
            ];
            $model->setIssueSignatures($signatures);
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลผู้ลงนามเรียบร้อยแล้ว');
            } else {
                Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกได้');
            }
        }
        return $this->redirect(['process', 'id' => $id]);
    }

    /**
     * หน้ารูปแบบใบเบิกวัสดุสำหรับพิมพ์ (ตามแบบฟอร์มราชการ)
     * ใช้ layout เฉพาะพิมพ์ ไม่มี template หลักของเว็บ
     */
    // public function actionPrint($id)
    // {
    //     $this->layout = '/print';
    //     $model = StockOrder::find()
    //         ->where(['id' => $id, 'order_type' => 'OUT', 'source_type' => 'REQUEST'])
    //         ->with(['mainWarehouse', 'subWarehouse', 'stockDetails', 'stockDetails.item'])
    //         ->one();
    //     if (!$model) {
    //         throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
    //     }
    //     return $this->render('print', ['model' => $model]);
    // }

// public function actionPdf($id)
// {
//     $model = $this->findModel($id);
    
//     // ดึงเนื้อหา HTML จาก View ที่คุณเขียนไว้
//     $content = $this->renderPartial('view_pdf', [
//         'model' => $model,
//     ]);

//     // นำค่า Config เริ่มต้นของ mPDF มาเพื่อจะ Merge กับฟอนต์ของเรา
//     $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
//     $fontDirs = $defaultConfig['fontDir'];

//     $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
//     $fontData = $defaultFontConfig['fontdata'];

//     $pdf = new Pdf([
//         'mode' => Pdf::MODE_UTF8,
//         'format' => Pdf::FORMAT_A4, 
//         'orientation' => Pdf::ORIENT_PORTRAIT, 
//         'destination' => Pdf::DEST_BROWSER, 
//         'content' => $content,  
//         'options' => [
//             'title' => 'ใบเบิกวัสดุ - ' . $model->order_no,
//             // 1. เพิ่ม Path ที่เก็บไฟล์ฟอนต์ของเรา
//             'fontDir' => array_merge($fontDirs, [
//                 Yii::getAlias('@webroot/fonts'), 
//             ]),
//             // 2. Map ชื่อฟอนต์เข้ากับไฟล์ .ttf
//             'fontdata' => $fontData + [
//                 'sarabun' => [
//                     'R' => 'THSarabunNew.ttf',
//                     'B' => 'THSarabunNew Bold.ttf',
//                     'I' => 'THSarabunNew Italic.ttf',
//                     'BI' => 'THSarabunNew BoldItalic.ttf',
//                 ]
//             ],
//             // 3. กำหนดฟอนต์เริ่มต้นของเอกสาร PDF ทั้งหมดเป็น sarabun
//             'default_font' => 'sarabun' 
//         ],
//         'methods' => [ 
//             'SetTitle' => 'ใบเบิกวัสดุ',
//         ]
//     ]);

//     return $pdf->render(); 
// }


    // ... โค้ดอื่นๆ ใน Controller ...

    public function actionPrint($id)
    {
        $model = StockOrder::find()
            ->where(['id' => $id, 'order_type' => 'OUT', 'source_type' => 'REQUEST'])
            ->with(['mainWarehouse', 'subWarehouse', 'stockDetails', 'stockDetails.item'])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
        }

        // เรนเดอร์เนื้อหาจากไฟล์ view (_print_pdf)
        $content = $this->renderPartial('_print_pdf', ['model' => $model]);

        // กำหนด Path ของฟอนต์ไทย
        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');

        // ดึงค่าเริ่มต้นของ mPDF มาเพื่อใช้งานร่วมกับฟอนต์ใหม่
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'fontDir' => array_merge($fontDirs, [$fontPath]),
            'fontdata' => $fontData + [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf', // เปลี่ยนชื่อไฟล์ตรงนี้ให้ตรงกับในโฟลเดอร์จริงถ้ามีการใช้ช่องว่าง
                    'I' => 'THSarabunNew-Italic.ttf',
                    'BI' => 'THSarabunNew-BoldItalic.ttf',
                ]
            ],
            'default_font' => 'thsarabun',
            // ตั้งค่าให้ mPDF รองรับภาษาไทยและเลือกฟอนต์ให้อัตโนมัติ
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];

        $mpdf = new \Mpdf\Mpdf($config);

        // กำหนดหัวข้อใน PDF Meta Data
        $mpdf->SetTitle('ใบเบิกวัสดุ - ' . $model->order_no);

        // 1. นำเข้าไฟล์ CSS ภายนอก (ถ้าต้องการใช้)
        $cssFile = Yii::getAlias('@webroot/css/kv-mpdf-bootstrap.css');
        if (file_exists($cssFile)) {
            $stylesheet = file_get_contents($cssFile);
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        }

        // 2. เขียน HTML เนื้อหาหลักลงใน PDF
        $signatureFooterCss = <<<'CSS'
.issue-signature-footer {
    position: fixed;
    right: 0;
    bottom: 0;
    left: 0;
    width: 100%;
    margin: 0;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 13px;
    line-height: 1.5;
}

.issue-signature-footer td {
    width: 50%;
    height: 88px;
    padding: 8px 14px 6px;
    vertical-align: top;
    border-color: #777 !important;
}

.issue-signature-footer .signature-row--large td {
    height: 118px;
}

.issue-signature-footer .signature-cell--blank {
    border: none !important;
}

.issue-signature-footer .signature-block {
    width: 100%;
    text-align: center;
}

.issue-signature-footer .signature-line,
.issue-signature-footer .signature-name,
.issue-signature-footer .signature-position,
.issue-signature-footer .signature-date {
    margin: 0;
}

.issue-signature-footer .signature-line {
    margin-bottom: 14px;
    white-space: nowrap;
}

.issue-signature-footer .signature-dots {
    letter-spacing: 1px;
    color: #444;
}

.issue-signature-footer .signature-stamp-table {
    width: 100%;
    border-collapse: collapse;
}

.issue-signature-footer .signature-stamp-cell {
    text-align: center;
    padding: 0;
    border: none !important;
}

.issue-signature-footer .signature-stamp {
    display: inline-block;
}

.issue-signature-footer .signature-name {
    min-height: 20px;
    margin-bottom: 8px;
}

.issue-signature-footer .signature-position {
    color: #222;
}

.issue-signature-footer .signature-date {
    margin-top: 8px;
    font-size: 12px;
}

.issue-signature-footer .dot-line {
    display: inline-block;
    min-width: 150px;
    border-bottom: 1px dotted #555;
    line-height: 1.2;
}
CSS;
        $mpdf->WriteHTML($signatureFooterCss, \Mpdf\HTMLParserMode::HEADER_CSS);

        $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);

        // ป้องกันอักขระพิเศษในชื่อไฟล์
        $filename = 'ใบเบิกวัสดุ_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', $model->order_no) . '.pdf';

        // ส่งออกไฟล์ PDF ให้เปิดดูใน Browser ทันที
        return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }

    public function actionGetAvailableLots($item_code, $warehouse_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return \app\modules\inventoryV2\models\StockDetail::find()
            ->joinWith('stockOrder')
            // COALESCE unit_price: lot ADJUST อาจมี unit_price = NULL → กัน NaN ในการคิดยอดฝั่ง JS
            ->select([
                'stock_detail.lot_number',
                'stock_detail.remain_qty',
                'unit_price' => new \yii\db\Expression('COALESCE(stock_detail.unit_price, 0)'),
            ])
            ->where(['stock_detail.item_code' => $item_code])
            ->andWhere(['stock_order.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and',
                    ['stock_order.main_warehouse_id' => $warehouse_id],
                    ['or',
                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                        ['and',
                            ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST],
                            ['>', 'stock_detail.qty', 0],
                        ],
                    ],
                ],
                ['and',
                    ['stock_order.order_type' => [
                        StockOrder::ORDER_TYPE_TRANSFER,
                        StockOrder::ORDER_TYPE_OUT,
                    ]],
                    ['stock_order.sub_warehouse_id' => $warehouse_id],
                    ['>', 'stock_detail.qty', 0],
                ],
            ])
            ->andWhere(['>', 'stock_detail.remain_qty', 0])
            ->orderBy(['stock_order.order_date' => SORT_ASC, 'stock_detail.id' => SORT_ASC])
            ->asArray()
            ->all();
    }

    public function actionViewModal($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'title' => '<i class="bi bi-eye me-1"></i>รายละเอียดใบตัดจ่าย',
            'content' => $this->renderAjax('view', [
                'model' => $model,
            ]),
        ];
    }

    protected function findModel($id)
    {
        if (($model = StockOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
    }
}
