<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\db\Expression;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractSearch;
use app\modules\purchase\models\ContractMilestone;
use app\modules\purchase\models\ContractReceipt;
use app\modules\purchase\models\ContractReceiptItem;
use app\modules\finance\services\ContractReceiptFinanceSnapshotBuilder;
use app\modules\purchase\components\ContractWordExporter;

/**
 * งานบริหารสัญญา — ทะเบียนสัญญา งวดงาน ค่าปรับ และเอกสาร Word
 *
 * สิทธิ์: role 'purchase' เท่านั้น (งานพัสดุเป็นผู้บันทึกสัญญา)
 * route ชุดนี้อยู่ใน allow list ของ AccessControl ระดับแอป และกันสิทธิ์เองที่ behaviors()
 */
class ContractController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['purchase'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'receipt-status' => ['POST'],
                    'receipt-delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new ContractSearch();
        if (!$this->request->get('ContractSearch')) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'counters' => $searchModel->counters(),
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * สร้างสัญญาใหม่ — ส่ง order_id มาด้วยเพื่อเติมค่าจากใบสั่งซื้อให้อัตโนมัติ
     * ค่าที่เติมมาเป็นเพียงค่าตั้งต้น ผู้ใช้แก้ได้ และสัญญาจะเก็บสำเนาของตัวเอง
     */
    public function actionCreate($order_id = null)
    {
        $model = new Contract([
            'thai_year' => (int) AppHelper::YearBudget(),
            'sign_date' => date('Y-m-d'),
            'status' => Contract::STATUS_DRAFT,
            'fine_rate' => 0.01,
        ]);

        $emp = UserHelper::GetEmployee();
        if ($emp) {
            $model->emp_id = $emp->id;
            $model->department_id = $emp->department;
        }

        if ($order_id && !$this->request->isPost) {
            $this->fillFromOrder($model, (int) $order_id);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithMilestones($model)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'milestones' => [],
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($this->saveWithMilestones($model)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'milestones' => $model->milestones,
        ]);
    }

    /**
     * ลบแบบ soft delete — สัญญาเป็นเอกสารที่ต้องตรวจสอบย้อนหลังได้
     * และมีค่าปรับ/ภาษีที่เคยพิมพ์ลงเอกสารไปแล้ว
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by']);

        Yii::$app->session->setFlash('success', 'ลบสัญญา "' . $model->title . '" แล้ว');
        return $this->redirect(['index']);
    }

    /**
     * บันทึกหัวสัญญา + งวดงานในทรานแซกชันเดียว แล้วคำนวณค่าปรับใหม่
     *
     * ค่าปรับต้องคำนวณ "หลัง" บันทึกงวดงานเสร็จ เพราะฐาน milestone อ่านจากงวดที่
     * เพิ่งบันทึก ถ้าคำนวณใน beforeSave จะได้งวดชุดเก่าที่ยังไม่ถูกแทนที่
     */
    private function saveWithMilestones(Contract $model): bool
    {
        // ฟอร์มส่งวันที่เป็น พ.ศ. วว/ดด/ปปปป — แปลงเป็น ค.ศ. ก่อน validate
        // ค่าที่แปลงไม่ได้ปล่อยไว้ตามเดิมให้กฎ date ของ model แจ้งผิด แทนที่จะหายเงียบ
        foreach (['sign_date', 'start_date', 'end_date', 'delivery_date', 'receive_date', 'warranty_end'] as $attr) {
            $raw = trim((string) $model->$attr);
            $model->$attr = $raw === '' ? null : (AppHelper::normalizeDateToDb($raw) ?? $raw);
        }
        $toDb = fn($v) => AppHelper::normalizeDateToDb(trim((string) $v));

        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                $tx->rollBack();
                return false;
            }

            ContractMilestone::deleteAll(['contract_id' => $model->id]);

            $seq = 0;
            foreach ((array) $this->request->post('milestones', []) as $row) {
                $detail = trim((string) ($row['detail'] ?? ''));
                $amount = (float) ($row['amount'] ?? 0);
                $due = trim((string) ($row['due_date'] ?? ''));
                // แถวที่ไม่กรอกอะไรเลย ถือว่าผู้ใช้เว้นไว้
                if ($detail === '' && $amount <= 0 && $due === '') {
                    continue;
                }
                $item = new ContractMilestone([
                    'contract_id' => $model->id,
                    'seq' => ++$seq,
                    'detail' => $detail ?: null,
                    'percent' => ($row['percent'] ?? '') !== '' ? (float) $row['percent'] : null,
                    'amount' => $amount,
                    'due_date' => $toDb($due),
                    'delivered_date' => $toDb($row['delivered_date'] ?? ''),
                    'receive_date' => $toDb($row['receive_date'] ?? ''),
                    'status' => $row['status'] ?? ContractMilestone::STATUS_PENDING,
                ]);
                if (!$item->save()) {
                    $tx->rollBack();
                    $model->addError('title', 'บันทึกงวดงานไม่สำเร็จ: ' . implode(' ', $item->getFirstErrors()));
                    return false;
                }
            }

            // เขียนค่าปรับรายงวดกลับลงแต่ละแถว เพื่อให้หน้ารายละเอียดแสดงได้ว่างวดไหนปรับเท่าไร
            $model->refresh();
            $fine = $model->fineInfo();
            foreach ($model->milestones as $ms) {
                $row = $fine['per_milestone'][$ms->seq] ?? null;
                if ($row === null) {
                    continue;
                }
                $ms->fine_days = $row['days'];
                $ms->fine_amount = $row['amount'];
                $ms->save(false, ['fine_days', 'fine_amount']);
            }

            $model->refreshFine();

            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกสัญญา "' . $model->title . '" เรียบร้อย');
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('บันทึกสัญญาไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            $model->addError('title', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }
    }

    /** เติมค่าจากใบสั่งซื้อลงฟอร์มสัญญาใหม่ */
    private function fillFromOrder(Contract $model, int $orderId): void
    {
        $snapshot = Contract::orderSnapshot($orderId);
        if ($snapshot === null) {
            Yii::$app->session->setFlash('warning', 'ไม่พบใบสั่งซื้อที่เลือก');
            return;
        }

        $model->order_id = $snapshot['order_id'];
        $model->title = $snapshot['title'] ?: $model->title;
        $model->contract_type = $snapshot['contract_type'];
        $model->start_date = $snapshot['start_date'];
        $model->vendor_id = $snapshot['vendor_id'];
        $model->vendor_name = $snapshot['vendor_name'];
        $model->egp_no = $snapshot['egp_no'];
        $model->budget = $snapshot['budget'];
        $model->sign_date = $snapshot['sign_date'] ?: $model->sign_date;
        $model->end_date = $snapshot['end_date'];
        $model->delivery_date = $snapshot['delivery_date'];
        $model->receive_date = $snapshot['receive_date'];
        $model->warranty_end = $snapshot['warranty_end'];
        if (!empty($snapshot['thai_year'])) {
            $model->thai_year = (int) $snapshot['thai_year'];
        }
    }

    /**
     * หน้าต่างเลือกใบสั่งซื้อ (โหลดเข้า modal)
     * แสดงเฉพาะใบที่ออกใบสั่งซื้อแล้วและยังไม่มีสัญญาผูก
     */
    public function actionOrderPicker()
    {
        $linked = Contract::find()
            ->select('order_id')
            ->where(['deleted_at' => null])
            ->andWhere(['not', ['order_id' => null]])
            ->column();

        $query = Order::find()
            ->alias('o')
            ->where(['o.name' => 'order', 'o.deleted_at' => null])
            ->andWhere(['not', ['o.po_number' => null]])
            ->andWhere(['!=', 'o.po_number', '']);

        if ($linked) {
            $query->andWhere(['not in', 'o.id', $linked]);
        }

        $year = (int) $this->request->get('year', (int) AppHelper::YearBudget());
        if ($year > 0) {
            $query->andWhere(['o.thai_year' => $year]);
        }

        $q = trim((string) $this->request->get('q', ''));
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'o.po_number', $q],
                ['like', 'o.pr_number', $q],
                ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.vendor_name'))"), $q],
                ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.pq_project_name'))"), $q],
            ]);
        }

        $orders = $query->orderBy(['o.id' => SORT_DESC])->limit(100)->all();

        $items = [];
        foreach ($orders as $order) {
            $snapshot = Contract::orderSnapshot($order->id);
            if ($snapshot !== null) {
                $items[] = $snapshot;
            }
        }

        $data = [
            'items' => $items,
            'year' => $year,
            'years' => Order::find()
                ->select('thai_year')
                ->distinct()
                ->where(['name' => 'order'])
                ->andWhere(['not', ['thai_year' => null]])
                ->orderBy(['thai_year' => SORT_DESC])
                ->column(),
            'q' => $q,
        ];

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เลือกใบสั่งซื้อที่จะผูกกับสัญญา',
                'content' => $this->renderAjax('_order_picker', $data),
            ];
        }

        return $this->render('_order_picker', $data);
    }

    /** ส่งออกเอกสาร Word — contract = ร่างสัญญา, fine = หนังสือแจ้งค่าปรับ */
    public function actionWord($id, $type = ContractWordExporter::DOC_CONTRACT)
    {
        $model = $this->findModel($id);

        if ($type === ContractWordExporter::DOC_FINE && (float) $model->fine_amount <= 0) {
            Yii::$app->session->setFlash('warning', 'สัญญานี้ยังไม่มีค่าปรับ จึงไม่ต้องออกหนังสือแจ้งค่าปรับ');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return ContractWordExporter::send($model, $type);
    }

    /** ทะเบียนคุมสัญญาทั้งปีเป็นไฟล์ Word */
    public function actionRegister($year = null)
    {
        $year = (int) ($year ?: AppHelper::YearBudget());
        $models = Contract::find()
            ->where(['deleted_at' => null, 'thai_year' => $year])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return ContractWordExporter::sendRegister($models, $year);
    }

    protected function findModel($id)
    {
        $model = Contract::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบสัญญาที่ต้องการ');
        }
        return $model;
    }

    // ═════════════════════════════════════════════════════════════════════
    // ตรวจรับรายงวด — สัญญาที่ออกใบสั่งซื้อเต็มวงเงินแต่ตรวจรับ/เรียกเก็บรายเดือน
    // ═════════════════════════════════════════════════════════════════════

    /** บันทึกตรวจรับงวดใหม่ */
    public function actionReceiptCreate($contract_id)
    {
        $contract = $this->findModel($contract_id);
        if (!$contract->isInstallment()) {
            Yii::$app->session->setFlash('warning', 'สัญญานี้ตั้งรูปแบบเป็น "ตรวจรับครั้งเดียว" — แก้ไขสัญญาเลือก "ตรวจรับรายงวด" ก่อน');
            return $this->redirect(['view', 'id' => $contract->id]);
        }

        $model = new ContractReceipt([
            'contract_id' => $contract->id,
            'order_id' => $contract->order_id,
            'seq' => $contract->nextReceiptSeq(),
            'vat_type' => $contract->orderVatType(),
            'status' => ContractReceipt::STATUS_DRAFT,
        ]);
        $model->populateRelation('contract', $contract);

        // ช่วงผลงานตั้งต้น = เดือนถัดจากงวดล่าสุด (หรือเดือนเริ่มสัญญา)
        $last = ContractReceipt::find()
            ->where(['contract_id' => $contract->id, 'deleted_at' => null])
            ->andWhere(['<>', 'status', ContractReceipt::STATUS_CANCELLED])
            ->andWhere(['not', ['period_end' => null]])
            ->orderBy(['period_end' => SORT_DESC])
            ->one();
        $startTs = $last ? strtotime($last->period_end . ' +1 day') : strtotime($contract->start_date ?: date('Y-m-01'));
        $model->period_start = date('Y-m-01', $startTs);
        $model->period_end = date('Y-m-t', $startTs);

        // รายการตั้งต้น: บรรทัดของใบสั่งซื้อ ปริมาณว่างให้กรอกตามจริง
        // ราคาต่อหน่วย/หน่วยนับ: ใช้ของงวดก่อนหน้า (บรรทัดเดียวกัน) ถ้ามี — ใบสั่งซื้อจ้างเหมามักเป็น "1 งาน × วงเงินทั้งสัญญา"
        // ซึ่งไม่ใช่ราคาต่อหน่วยจริง (ต่อครั้ง/ต่อราย) สัญญาตามปริมาณจริงจึงปล่อยราคาว่างให้กรอกในงวดแรก
        $isUnitPrice = $contract->billing_mode === Contract::BILLING_UNIT_PRICE;
        $lines = array_map(function ($l) use ($contract, $isUnitPrice) {
            $prev = ContractReceiptItem::find()->alias('i')
                ->innerJoin(['r' => ContractReceipt::tableName()], 'r.id = i.receipt_id')
                ->where(['r.contract_id' => $contract->id, 'r.deleted_at' => null, 'i.order_item_id' => $l['order_item_id']])
                ->andWhere(['<>', 'r.status', ContractReceipt::STATUS_CANCELLED])
                ->orderBy(['r.seq' => SORT_DESC])
                ->one();
            $price = $prev ? (float) $prev->unit_price : (($isUnitPrice && $l['qty'] <= 1) ? 0.0 : $l['unit_price']);
            return new ContractReceiptItem([
                'order_item_id' => $l['order_item_id'],
                'asset_item' => $l['asset_item'],
                'item_name' => $l['item_name'],
                'unit_name' => $prev ? $prev->unit_name : $l['unit_name'],
                'unit_price' => $price,
                'qty' => 0,
            ]);
        }, $contract->orderLines());

        return $this->saveReceiptOrRender($contract, $model, $lines);
    }

    public function actionReceiptUpdate($id)
    {
        $model = $this->findReceipt($id);
        $contract = $this->findModel($model->contract_id);
        if (!$model->isEditable()) {
            Yii::$app->session->setFlash('warning', 'งวดนี้' . ContractReceipt::statusList()[$model->status] . ' แก้ไขไม่ได้');
            return $this->redirect(['view', 'id' => $contract->id, '#' => 'receipts']);
        }
        return $this->saveReceiptOrRender($contract, $model, $model->items);
    }

    /** เปลี่ยนสถานะงวด: received = ยืนยันตรวจรับ, cancelled = ยกเลิก, draft = ถอยกลับเป็นร่าง */
    public function actionReceiptStatus($id, $to)
    {
        $model = $this->findReceipt($id);
        $allowed = [
            ContractReceipt::STATUS_DRAFT => [ContractReceipt::STATUS_RECEIVED, ContractReceipt::STATUS_CANCELLED],
            ContractReceipt::STATUS_RECEIVED => [ContractReceipt::STATUS_DRAFT, ContractReceipt::STATUS_CANCELLED],
        ];
        // ส่งการเงินแล้ว: เปิดแก้ได้เฉพาะเมื่อการเงินตีกลับ (rejected) — แล้วส่งใหม่เป็นรุ่นถัดไป
        if ($model->status === ContractReceipt::STATUS_SENT_FINANCE
            && ContractReceiptFinanceSnapshotBuilder::isReturned(ContractReceiptFinanceSnapshotBuilder::latestInbox((int) $model->id))) {
            $allowed[ContractReceipt::STATUS_SENT_FINANCE] = [ContractReceipt::STATUS_RECEIVED];
        }
        if (!in_array($to, $allowed[$model->status] ?? [], true)) {
            Yii::$app->session->setFlash('danger', 'เปลี่ยนสถานะงวดนี้ไม่ได้');
        } else {
            $model->status = $to;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'งวดที่ ' . $model->seq . ': ' . ContractReceipt::statusList()[$to]);
            } else {
                Yii::$app->session->setFlash('danger', implode(' ', $model->getFirstErrors()));
            }
        }
        return $this->redirect(['view', 'id' => $model->contract_id, '#' => 'receipts']);
    }

    /** ลบได้เฉพาะร่าง (ยังไม่เคยตรวจรับ/ส่งออกไปไหน) — งวดที่ตรวจรับแล้วใช้ยกเลิกแทน */
    public function actionReceiptDelete($id)
    {
        $model = $this->findReceipt($id);
        if ($model->status !== ContractReceipt::STATUS_DRAFT) {
            Yii::$app->session->setFlash('danger', 'ลบได้เฉพาะงวดที่เป็นร่าง งวดที่ตรวจรับแล้วให้ใช้ "ยกเลิก"');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบร่างงวดที่ ' . $model->seq . ' แล้ว');
        }
        return $this->redirect(['view', 'id' => $model->contract_id, '#' => 'receipts']);
    }

    /**
     * @param ContractReceiptItem[] $lines
     */
    private function saveReceiptOrRender(Contract $contract, ContractReceipt $model, array $lines)
    {
        $locked = $model->getAttributes(['contract_id', 'order_id', 'seq', 'status', 'sent_finance_at']);
        if ($this->request->isPost && $model->load($this->request->post())) {
            // ช่องที่ฟอร์มห้ามเปลี่ยน (สถานะเปลี่ยนผ่านปุ่มยืนยัน/actionReceiptStatus เท่านั้น)
            $model->setAttributes($locked, false);
            // วันที่ในฟอร์มเป็น พ.ศ. — แปลงเป็น ค.ศ. ก่อน validate (ค่าที่แปลงไม่ได้ปล่อยให้กฎ date แจ้ง)
            foreach (['period_start', 'period_end', 'invoice_date', 'delivered_date', 'receive_date'] as $attr) {
                $raw = trim((string) $model->$attr);
                $model->$attr = $raw === '' ? null : (AppHelper::normalizeDateToDb($raw) ?? $raw);
            }
            $model->populateRelation('contract', $contract);

            // รายการจากฟอร์ม — แถวปริมาณ 0 ไม่เก็บ
            $lines = [];
            $lineTotal = 0.0;
            foreach ((array) $this->request->post('lines', []) as $row) {
                $qty = (float) ($row['qty'] ?? 0);
                $price = (float) ($row['unit_price'] ?? 0);
                $item = new ContractReceiptItem([
                    'order_item_id' => ($row['order_item_id'] ?? '') !== '' ? (int) $row['order_item_id'] : null,
                    'asset_item' => $row['asset_item'] ?? null,
                    'item_name' => trim((string) ($row['item_name'] ?? '')) ?: 'รายการ',
                    'unit_name' => ($row['unit_name'] ?? '') ?: null,
                    'qty' => $qty,
                    'unit_price' => $price,
                ]);
                $lines[] = $item;
                $lineTotal += $qty * $price;
            }

            $model->applyTotals($lineTotal);
            if ($this->request->post('confirm')) {
                $model->status = ContractReceipt::STATUS_RECEIVED;
            }

            $valid = $model->validate();
            if ($lineTotal <= 0) {
                $model->addError('amount', 'กรอกปริมาณหรือยอดของงวดนี้อย่างน้อย 1 รายการ');
                $valid = false;
            }
            // ห้ามเกินวงเงินสัญญา (นับงวดอื่นที่ไม่ถูกยกเลิกรวมร่าง)
            $used = $contract->receiptUsedTotal($model->isNewRecord ? null : (int) $model->id);
            if ((float) $contract->budget > 0 && $used + (float) $model->amount > (float) $contract->budget + 0.005) {
                $model->addError('amount', sprintf(
                    'ยอดงวดนี้ %s บาท เกินวงเงินคงเหลือของสัญญา %s บาท',
                    number_format((float) $model->amount, 2),
                    number_format((float) $contract->budget - $used, 2)
                ));
                $valid = false;
            }

            if ($valid) {
                $tx = Yii::$app->db->beginTransaction();
                try {
                    $model->save(false);
                    ContractReceiptItem::deleteAll(['receipt_id' => $model->id]);
                    foreach ($lines as $item) {
                        if ((float) $item->qty <= 0) {
                            continue;
                        }
                        $item->receipt_id = $model->id;
                        if (!$item->save()) {
                            throw new \RuntimeException(implode(' ', $item->getFirstErrors()));
                        }
                    }
                    $tx->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกงวดที่ ' . $model->seq . ' ('
                        . ContractReceipt::statusList()[$model->status] . ') ยอด ' . number_format((float) $model->amount, 2) . ' บาท');
                    return $this->redirect(['view', 'id' => $contract->id, '#' => 'receipts']);
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    $model->addError('amount', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
                }
            }
            // ตรวจไม่ผ่าน: ถ้ากดยืนยันตรวจรับมา ให้สถานะกลับเป็นค่าเดิมบนฟอร์ม
            if ($this->request->post('confirm') && $model->isNewRecord) {
                $model->status = ContractReceipt::STATUS_DRAFT;
            }
        }

        return $this->render('receipt-form', [
            'contract' => $contract,
            'model' => $model,
            'lines' => $lines,
            'used' => $contract->receiptUsedTotal($model->isNewRecord ? null : (int) $model->id),
        ]);
    }

    protected function findReceipt($id): ContractReceipt
    {
        $model = ContractReceipt::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบงวดตรวจรับที่ต้องการ');
        }
        return $model;
    }
}
