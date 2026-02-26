<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockOrderSearch;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class IssueController extends Controller
{
    /**
     * แสดงรายการใบขอเบิกที่อนุมัติแล้ว (รอคลังจ่าย) และที่จ่ายแล้ว
     */
    public function actionIndex()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->query->andWhere([
            'order_type' => 'OUT',
            'source_type' => 'REQUEST',
        ])->andWhere(['status' => [StockOrder::STATUS_APPROVED, StockOrder::STATUS_CONFIRMED]]);
        $dataProvider->query->with(['mainWarehouse', 'subWarehouse']);

        // วันที่เบิก (order_date)
        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        if ($start !== null && $start !== '') {
            $dataProvider->query->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $dataProvider->query->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }

        // วันที่จ่าย (updated_at เป็น timestamp)
        $confStart = AppHelper::convertToGregorian($searchModel->confirmed_date_start);
        $confEnd = AppHelper::convertToGregorian($searchModel->confirmed_date_end);
        if ($confStart !== null && $confStart !== '') {
            $dataProvider->query->andWhere(['>=', 'updated_at', strtotime($confStart . ' 00:00:00')]);
        }
        if ($confEnd !== null && $confEnd !== '') {
            $dataProvider->query->andWhere(['<=', 'updated_at', strtotime($confEnd . ' 23:59:59')]);
        }

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        $mainWarehouses = ['' => 'ทุกคลัง'] + ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])
                ->orderBy('warehouse_name')
                ->all(),
            'id',
            'warehouse_name'
        );
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
            'statusLabels' => $statusLabels,
        ]);
    }

    /**
     * หน้าจอสำหรับดำเนินการจ่าย (เลือก Lot/จำนวน) — ตัดสต็อกเมื่อกดยืนยันจ่าย
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
                foreach ($data as $item) {
                    // 1. ตรวจสอบเบื้องต้น: ถ้าจำนวนเป็น 0 หรือไม่มีข้อมูลให้ข้าม
                    if (empty($item['qty_issued']) || $item['qty_issued'] <= 0) continue;

                    $detail = StockDetail::findOne($item['detail_id']);
                    if (!$detail) continue;

                    $qtyToProcess = (float)$item['qty_issued'];
                    $selectedLot = $item['lot_number'];

                    // 2. หักลบ remain_qty จาก "รายการรับเข้า (IN)" ต้นทาง 
                    // เพื่อให้ยอดเหลือรายแถวในหน้า process.php อัปเดตถูกต้อง
                    $sourceLots = StockDetail::find()
                        ->joinWith('stockOrder')
                        ->where([
                            'stock_detail.item_code' => $detail->item_code,
                            'stock_detail.lot_number' => $selectedLot,
                            'stock_order.order_type' => 'IN',
                            'stock_order.main_warehouse_id' => $model->main_warehouse_id
                        ])
                        ->andWhere(['>', 'remain_qty', 0])
                        ->orderBy(['stock_detail.id' => SORT_ASC]) // ตัดตัวที่เข้าก่อน (FIFO ภายใน Lot)
                        ->all();

                    $tempQty = $qtyToProcess;
                    $lastUnitPrice = 0;

                    foreach ($sourceLots as $sourceIn) {
                        if ($tempQty <= 0) break;

                        $take = min($tempQty, (float)$sourceIn->remain_qty);
                        $sourceIn->remain_qty -= $take;
                        $sourceIn->save(false);

                        $lastUnitPrice = $sourceIn->unit_price; // เก็บราคาทุนไว้บันทึกกลับ
                        $tempQty -= $take;
                    }

                    if ($tempQty > 0) {
                        throw new \Exception("พัสดุรหัส {$detail->item_code} ใน Lot {$selectedLot} มีไม่พอจ่าย (ขอจ่าย " . $qtyToProcess . " เหลือใน Lot ไม่เพียงพอ)");
                    }

                    $qtyActuallyIssued = $qtyToProcess - $tempQty;

                    // 3. อัปเดตยอดรวมใน StockBalance (แยกตามคลังและ Lot) — หักเฉพาะจำนวนที่หักได้จริง
                    InventoryService::updateBalance(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        $qtyActuallyIssued,
                        'OUT',
                        $selectedLot
                    );

                    // 3.1 โอนยอดเข้าคลังย่อย (ถ้ามี sub_warehouse_id) เพื่อให้คลังย่อยมีสต็อกสำหรับบันทึกการใช้งาน
                    if ($model->sub_warehouse_id) {
                        InventoryService::updateBalance(
                            $detail->item_code,
                            $model->sub_warehouse_id,
                            $qtyActuallyIssued,
                            'IN',
                            $selectedLot
                        );
                    }

                    // 4. บันทึกข้อมูลกลับลงใน StockDetail ของ "ใบเบิกใบนี้"
                    $detail->qty = $qtyActuallyIssued;   // จำนวนที่จ่ายจริง
                    $detail->lot_number = $selectedLot;    // ล็อตที่เลือกจ่าย
                    $detail->unit_price = $lastUnitPrice;   // ราคาทุนที่ดึงมาจากต้นทาง

                    if (!$detail->save(false)) {
                        throw new \Exception("ไม่สามารถบันทึกรายละเอียดพัสดุรหัส: " . $detail->item_code);
                    }
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
                $model->updated_at = $ts; // ใช้ filter วันที่จ่ายใน index ได้
                if (!$model->save(false)) {
                    throw new \Exception("ไม่สามารถบันทึกสถานะใบเบิกได้");
                }

                $transaction->commit();
                return ['success' => true];
            } catch (\Exception $e) {
                $transaction->rollBack();
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
    public function actionPrint($id)
    {
        $this->layout = '/print';
        $model = StockOrder::find()
            ->where(['id' => $id, 'order_type' => 'OUT', 'source_type' => 'REQUEST'])
            ->with(['mainWarehouse', 'subWarehouse', 'stockDetails', 'stockDetails.item'])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
        }
        return $this->render('print', ['model' => $model]);
    }

    /**
     * ส่งออกใบเบิกวัสดุเป็น PDF (รูปแบบตามแบบฟอร์ม 100%)
     */
    public function actionPdf($id)
    {
        $model = StockOrder::find()
            ->where(['id' => $id, 'order_type' => 'OUT', 'source_type' => 'REQUEST'])
            ->with(['mainWarehouse', 'subWarehouse', 'stockDetails', 'stockDetails.item'])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
        }

        $html = $this->renderPartial('_print_pdf', ['model' => $model]);
        $html = '<!DOCTYPE html><html lang="th"><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';

        $fontPath = Yii::getAlias('@webroot/fonts');
        $fontPathTh = $fontPath . DIRECTORY_SEPARATOR . 'THSarabunNew';
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'freeserif',
        ];

        $ttfR = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew.ttf';
        $ttfB = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew Bold.ttf';
        $ttfBAlt = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew-Bold.ttf';
        $ttfI = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew Italic.ttf';
        $ttfIAlt = $fontPathTh . DIRECTORY_SEPARATOR . 'THSarabunNew-Italic.ttf';
        if (is_dir($fontPathTh) && file_exists($ttfR)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $defaultFont = (new \Mpdf\Config\FontVariables())->getDefaults();
            $config['fontDir'] = array_merge($defaultConfig['fontDir'], [$fontPathTh]);
            $fontdata = [
                'R' => 'THSarabunNew.ttf',
                'B' => file_exists($ttfB) ? 'THSarabunNew Bold.ttf' : (file_exists($ttfBAlt) ? 'THSarabunNew-Bold.ttf' : 'THSarabunNew.ttf'),
                'I' => file_exists($ttfI) ? 'THSarabunNew Italic.ttf' : (file_exists($ttfIAlt) ? 'THSarabunNew-Italic.ttf' : 'THSarabunNew.ttf'),
            ];
            $config['fontdata'] = array_merge($defaultFont['fontdata'], [
                'thsarabun' => $fontdata,
            ]);
            $config['default_font'] = 'thsarabun';
        }

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetTitle('ใบเบิกวัสดุ - ' . $model->order_no);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $filename = 'ใบเบิกวัสดุ_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', $model->order_no) . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }

    public function actionGetAvailableLots($item_code, $warehouse_id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    return \app\modules\inventoryV2\models\StockDetail::find()
        ->joinWith('stockOrder')
        ->select(['stock_detail.lot_number', 'stock_detail.remain_qty', 'stock_detail.unit_price'])
        ->where([
            'stock_detail.item_code' => $item_code,
            'stock_order.main_warehouse_id' => $warehouse_id,
            'stock_order.order_type' => 'IN'
        ])
        ->andWhere(['>', 'remain_qty', 0])
        ->asArray()
        ->all();
}

    protected function findModel($id)
    {
        if (($model = StockOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบใบเบิกที่ต้องการ');
    }
}
