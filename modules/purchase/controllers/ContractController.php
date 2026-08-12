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
                    'due_date' => $due ?: null,
                    'delivered_date' => ($row['delivered_date'] ?? '') ?: null,
                    'receive_date' => ($row['receive_date'] ?? '') ?: null,
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
}
