<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use app\modules\finance\models\FinanceLoanImportForm;
use app\modules\finance\models\FinanceLoanItem;
use app\modules\finance\models\FinanceLoanSearch;
use app\modules\finance\models\FinanceLoanSettlement;
use app\modules\finance\services\FinanceLoanFollowupService;
use app\modules\finance\services\FinanceLoanImportService;
use app\modules\finance\services\FinanceLoanSettlementService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class LoanController extends Controller
{
    /** session เก็บแค่ token ส่วนตัวข้อมูล preview เก็บเป็นไฟล์ (session อยู่ในคอลัมน์ TEXT 64KB) */
    private const IMPORT_SESSION = 'finance_loan_import_token';

    /** อายุไฟล์ preview ค้างก่อนถูกเก็บกวาด (วินาที) */
    private const PREVIEW_TTL = 6 * 3600;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeOperate']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'delete' => ['POST'],
                'transition' => ['POST'],
                'settle-delete' => ['POST'],
                'followup-delete' => ['POST'],
                'confirm-import' => ['POST'],
                'delete-import-preview' => ['POST'],
            ]],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new FinanceLoanSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $summary = $searchModel->summary();
        return $this->render('index', compact('searchModel', 'dataProvider', 'summary'));
    }

    public function actionCreate()
    {
        $fiscalYear = FinanceLoan::currentFiscalYear();
        $model = new FinanceLoan([
            'fiscal_year' => $fiscalYear,
            'contract_no' => FinanceLoan::nextContractNo($fiscalYear),
            'status' => FinanceLoan::STATUS_REQUESTED,
            'source_ref_type' => 'manual',
            'borrowed_at' => date('Y-m-d'),
        ]);
        return $this->saveForm($model, 'เพิ่มใบยืมเงิน');
    }

    public function actionUpdate($id)
    {
        return $this->saveForm($this->findModel($id), 'แก้ไขใบยืมเงิน');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // ใบที่เริ่มส่งใช้หรือออกหนังสือติดตามไปแล้วมีร่องรอยอยู่ในเอกสารที่พิมพ์ออกไป
        // ลบทิ้งเงียบ ๆ ไม่ได้ ต้องใช้สถานะ "ยกเลิก" เพื่อให้ยังตรวจย้อนหลังได้
        if ($model->getSettlements()->exists() || $model->getFollowups()->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะใบยืม ' . $model->contract_no . ' มีรายการส่งใช้หรือประวัติการติดตามแล้ว — ให้เปลี่ยนสถานะเป็น “ยกเลิก” แทน');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $contractNo = $model->contract_no;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบใบยืม ' . $contractNo . ' เรียบร้อย');
        return $this->redirect(['index']);
    }

    /**
     * เดินสถานะตามลำดับงาน — เห็นชอบ อนุมัติ รับเช็ค ยกเลิก
     *
     * ไม่ให้เลือกสถานะได้อิสระจาก dropdown เพราะการข้ามขั้นทำให้ตามย้อนหลังไม่ได้
     * ว่าใครอนุมัติเมื่อไร ส่วน “ล้างใบยืม” ไม่มีปุ่ม ระบบเลื่อนให้เองเมื่อส่งใช้ครบ
     */
    public function actionTransition($id, $to)
    {
        $model = $this->findModel($id);
        $blocker = $model->transitionBlocker($to);
        if ($blocker) {
            Yii::$app->session->setFlash('error', $blocker);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $changes = ['status' => $to];
        if ($to === FinanceLoan::STATUS_RECEIVED) {
            $postedDate = trim((string) Yii::$app->request->post('received_at', ''));
            $model->received_at = $model->received_at ?: ($postedDate ?: date('Y-m-d'));
            $changes['received_at'] = $model->received_at;
        }
        // สถานะที่เพิ่งเปลี่ยนอาจทำให้จุดตั้งต้นนับวันครบกำหนดพร้อมใช้ เช่น กติกาที่
        // นับจากวันรับเงิน จะคำนวณได้ก็ต่อเมื่อบันทึกรับเช็คแล้วเท่านั้น
        $model->status = $to;
        $model->applyDueRule();
        $changes['due_at'] = $model->due_at;
        $changes['due_days'] = $model->due_days;
        $changes['due_basis'] = $model->due_basis;
        $model->updateAttributes($changes);

        Yii::$app->session->setFlash('success', 'เปลี่ยนสถานะใบยืม ' . $model->contract_no . ' เป็น “' . $model->statusLabel() . '” แล้ว');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /** บันทึกการส่งใช้ครั้งใหม่ */
    public function actionSettle($id)
    {
        $loan = $this->findModel($id);
        // ส่งใช้ก่อนรับเงินไม่ได้ ถ้าเปิดให้ทำ ระบบจะเลื่อนสถานะไป “ล้างใบยืม”
        // ข้ามขั้นอนุมัติและรับเช็คไปเลย แล้วประวัติจะขาดหายทั้งสองขั้น
        if (!in_array($loan->status, [FinanceLoan::STATUS_RECEIVED, FinanceLoan::STATUS_CLEARED, FinanceLoan::STATUS_COMPLETED], true)) {
            Yii::$app->session->setFlash('error', 'บันทึกการส่งใช้ได้หลังจากบันทึกรับเช็คแล้วเท่านั้น — สถานะปัจจุบันคือ “' . $loan->statusLabel() . '”');
            return $this->redirect(['view', 'id' => $loan->id]);
        }
        $settlement = new FinanceLoanSettlement([
            'loan_id' => $loan->id,
            'seq' => FinanceLoanSettlement::nextSeq($loan->id),
            'settled_at' => date('Y-m-d'),
        ]);
        return $this->settlementForm($loan, $settlement, 'บันทึกการส่งใช้');
    }

    /** แก้ไขการส่งใช้ที่บันทึกไว้แล้ว */
    public function actionSettleUpdate($id)
    {
        $settlement = $this->findSettlement($id);
        return $this->settlementForm($settlement->loan, $settlement, 'แก้ไขการส่งใช้ครั้งที่ ' . (int) $settlement->seq);
    }

    public function actionSettleDelete($id)
    {
        $settlement = $this->findSettlement($id);
        $loan = $settlement->loan;
        $seq = (int) $settlement->seq;
        if ((new FinanceLoanSettlementService())->delete($settlement)) {
            Yii::$app->session->setFlash('success', 'ลบการส่งใช้ครั้งที่ ' . $seq . ' และคำนวณยอดคงค้างใหม่แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ลบไม่สำเร็จ ระบบยังไม่ได้แก้ไขข้อมูล');
        }
        return $this->redirect(['view', 'id' => $loan->id]);
    }

    /**
     * นำเข้าทะเบียนเดิมจากไฟล์ Excel — อ่านและตรวจก่อน ยังไม่บันทึก
     *
     * ผลตรวจเก็บเป็นไฟล์ใน @runtime ไม่ใช่ใน session เพราะ session ของระบบนี้เก็บลง
     * ฐานข้อมูลคอลัมน์ TEXT (64KB) ซึ่งไฟล์ทะเบียนจริงหลายร้อยแถวล้นแน่นอน
     */
    public function actionImport()
    {
        $this->purgeStalePreviews();
        $model = new FinanceLoanImportForm();
        $preview = $this->loadPreview();

        if (Yii::$app->request->isPost) {
            $this->clearPreview();
            $preview = null;
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $path = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'finance-loan-' . Yii::$app->security->generateRandomString(12) . '.' . $model->file->extension;
                if (!$model->file->saveAs($path)) {
                    Yii::$app->session->setFlash('error', 'บันทึกไฟล์ที่อัปโหลดไม่สำเร็จ กรุณาลองอีกครั้ง');
                } else {
                    try {
                        $preview = (new FinanceLoanImportService())->preview($path, $model->file->name, (int) $model->fiscal_year, $model->sheet);
                        $preview = $this->storePreview($preview);
                    } catch (\Throwable $e) {
                        Yii::error($e, __METHOD__);
                        Yii::$app->session->setFlash('error', $e instanceof \RuntimeException ? $e->getMessage() : 'อ่านไฟล์ไม่สำเร็จ กรุณาตรวจสอบรูปแบบไฟล์แล้วลองอีกครั้ง');
                    } finally {
                        @unlink($path);
                    }
                }
            }
        } elseif ($preview) {
            $model->fiscal_year = $preview['fiscal_year'];
            $model->sheet = $preview['sheet'];
        }

        return $this->render('import', compact('model', 'preview'));
    }

    public function actionConfirmImport()
    {
        $preview = $this->loadPreview();
        $token = (string) Yii::$app->request->post('preview_token', '');
        if (!$preview || $token === '' || !hash_equals((string) $preview['token'], $token)) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลตัวอย่าง กรุณาเลือกไฟล์อีกครั้ง');
            return $this->redirect(['import']);
        }

        $fiscalYear = (int) $preview['fiscal_year'];
        $result = (new FinanceLoanImportService())->save($preview, 'IMPORT-' . $fiscalYear . '-' . date('YmdHis'));
        if ($result['success']) {
            $this->clearPreview();
            Yii::$app->session->setFlash('success', sprintf(
                'นำเข้าสำเร็จ %s ใบยืม พร้อมรายการส่งใช้ %s รายการ',
                number_format($result['saved']),
                number_format($result['settlements'])
            ));
            return $this->redirect(['index', 'FinanceLoanSearch[fiscal_year]' => $fiscalYear]);
        }

        Yii::error($result['error'], __METHOD__);
        Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ ระบบยังไม่ได้บันทึกข้อมูล — ' . $result['error']);
        return $this->redirect(['import']);
    }

    public function actionDeleteImportPreview()
    {
        $this->clearPreview();
        return $this->redirect(['import']);
    }

    /**
     * ทะเบียนคุมลูกหนี้เงินยืมรายเดือน — รูปแบบเดียวกับที่ส่งจังหวัด
     */
    public function actionRegister($fiscal_year = null, $month = null)
    {
        $fiscalYear = (int) ($fiscal_year ?: FinanceLoan::currentFiscalYear());
        $query = FinanceLoan::find()
            ->where(['fiscal_year' => $fiscalYear])
            ->with(['items', 'settlements'])
            ->orderBy(['borrowed_at' => SORT_ASC, 'contract_seq' => SORT_ASC, 'id' => SORT_ASC]);
        if ($month) {
            // เดือนของทะเบียนยึดวันที่ยืม ตามที่หัวตารางเขียนว่า "ประจำเดือน"
            $query->andWhere(['between', 'borrowed_at', date('Y-m-01', strtotime($month . '-01')), date('Y-m-t', strtotime($month . '-01'))]);
        }
        $loans = $query->all();
        $months = FinanceLoan::find()
            ->select(["DATE_FORMAT(borrowed_at, '%Y-%m') AS ym"])
            ->where(['fiscal_year' => $fiscalYear])
            ->andWhere(['not', ['borrowed_at' => null]])
            ->distinct()
            ->orderBy(['ym' => SORT_ASC])
            ->column();

        return $this->render('register', compact('loans', 'fiscalYear', 'month', 'months'));
    }

    /** ทะเบียนลูกหนี้ค้างชำระ เรียงตามค้างนานที่สุด */
    public function actionOutstanding()
    {
        $loans = FinanceLoanFollowupService::overdueLoans();
        $upcoming = FinanceLoan::findOutstanding()
            ->andWhere(['between', 'due_at', date('Y-m-d'), date('Y-m-d', strtotime('+7 day'))])
            ->orderBy(['due_at' => SORT_ASC])
            ->all();
        $missing = FinanceLoan::findOutstanding()->andWhere(['due_at' => null])->orderBy(['borrowed_at' => SORT_ASC])->all();

        return $this->render('outstanding', compact('loans', 'upcoming', 'missing'));
    }

    /**
     * ออกหนังสือติดตามฉบับใหม่
     *
     * เลขครั้งที่นับเฉพาะหนังสือ ไม่นับข้อความแจ้งเตือนอัตโนมัติ เพราะเลขบนหัวหนังสือ
     * ต้องเดินตามจำนวนหนังสือที่ออกจริงเท่านั้น
     */
    public function actionFollowup($id)
    {
        $loan = $this->findModel($id);
        if ($loan->isClosed() || (float) $loan->outstanding_amount <= 0) {
            Yii::$app->session->setFlash('error', 'ใบยืม ' . $loan->contract_no . ' ไม่มียอดค้างแล้ว จึงไม่ต้องออกหนังสือติดตาม');
            return $this->redirect(['view', 'id' => $loan->id]);
        }

        $letter = new FinanceLoanFollowup([
            'loan_id' => $loan->id,
            'channel' => FinanceLoanFollowup::CHANNEL_LETTER,
            'stage' => FinanceLoanFollowup::STAGE_MANUAL,
            'letter_seq' => FinanceLoanFollowup::nextLetterSeq((int) $loan->id),
            'letter_date' => date('Y-m-d'),
            'new_due_at' => date('Y-m-d', strtotime('+15 day')),
        ]);

        if ($letter->load(Yii::$app->request->post()) && (new FinanceLoanFollowupService())->issueLetter($loan, $letter)) {
            Yii::$app->session->setFlash('success', 'ออกหนังสือติดตามครั้งที่ ' . (int) $letter->letter_seq . ' แล้ว — กด “พิมพ์หนังสือ” ในรายการติดตามเพื่อตรวจแก้ก่อนพิมพ์');
            return $this->redirect(['view', 'id' => $loan->id]);
        }

        return $this->render('followup', compact('loan', 'letter'));
    }

    public function actionFollowupDelete($id)
    {
        $followup = FinanceLoanFollowup::findOne($id);
        if (!$followup || !$followup->loan) {
            throw new NotFoundHttpException('ไม่พบรายการติดตามที่ต้องการ');
        }
        $loanId = $followup->loan_id;
        (new FinanceLoanFollowupService())->deleteFollowup($followup);
        Yii::$app->session->setFlash('success', 'ลบรายการติดตามเรียบร้อย');
        return $this->redirect(['view', 'id' => $loanId]);
    }

    private function settlementForm(FinanceLoan $loan, FinanceLoanSettlement $settlement, string $title)
    {
        if ($settlement->load(Yii::$app->request->post())) {
            $settlement->loan_id = $loan->id;
            if ((new FinanceLoanSettlementService())->save($settlement)) {
                $loan->refresh();
                $message = 'บันทึกการส่งใช้ครั้งที่ ' . (int) $settlement->seq . ' เรียบร้อย';
                if ($loan->status === FinanceLoan::STATUS_CLEARED) {
                    $message .= ' · ส่งใช้ครบแล้ว ระบบเปลี่ยนสถานะเป็น “ล้างใบยืม” ให้อัตโนมัติ';
                }
                Yii::$app->session->setFlash('success', $message);
                return $this->redirect(['view', 'id' => $loan->id]);
            }
        }
        return $this->render('settlement', compact('loan', 'settlement', 'title'));
    }

    private function findSettlement($id): FinanceLoanSettlement
    {
        $settlement = FinanceLoanSettlement::findOne($id);
        if (!$settlement || !$settlement->loan) {
            throw new NotFoundHttpException('ไม่พบรายการส่งใช้ที่ต้องการ');
        }
        return $settlement;
    }

    /**
     * บันทึกหัวสัญญาพร้อมบรรทัดประมาณการในธุรกรรมเดียว
     *
     * ยอดเงินยืมเป็นผลรวมของบรรทัด ไม่ใช่ช่องที่ผู้ใช้กรอก ถ้าหัวกับบรรทัดบันทึกแยกกัน
     * แล้วอันใดอันหนึ่งล้ม จะเหลือใบยืมที่ยอดไม่ตรงกับรายการ ซึ่งเป็นข้อมูลที่ผิดแบบ
     * มองด้วยตาไม่เห็น จึงต้องอยู่ในธุรกรรมเดียวกันเสมอ
     */
    private function saveForm(FinanceLoan $model, string $title)
    {
        $items = $model->isNewRecord ? [] : $model->items;
        // สถานะไม่ได้อยู่ในฟอร์มแล้ว แต่ยังเป็น safe attribute ตามกฎการตรวจสอบ
        // ถ้าไม่ล็อกไว้ การยิง POST ตรง ๆ จะเปลี่ยนสถานะได้โดยข้ามปุ่มเดินขั้นตอน
        $lockedStatus = $model->isNewRecord ? null : $model->status;
        if (Yii::$app->request->isPost) {
            $posted = $this->postedItems();
            $items = $posted['models'];
            $loaded = $model->load(Yii::$app->request->post());
            if ($lockedStatus !== null) {
                $model->status = $lockedStatus;
            }
            if ($loaded && $model->validate() && !$posted['errors']) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->save(false);
                    $this->syncItems($model, $posted['rows']);
                    $model->recalcTotals();
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกใบยืม ' . $model->contract_no . ' เรียบร้อย');
                    return $this->redirect(['view', 'id' => $model->id]);
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    Yii::error($e, __METHOD__);
                    Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ ระบบยังไม่ได้แก้ไขข้อมูล — ' . $e->getMessage());
                }
            } elseif ($posted['errors']) {
                Yii::$app->session->setFlash('error', 'ตรวจสอบบรรทัดประมาณการอีกครั้ง: ' . implode(' · ', array_unique($posted['errors'])));
            }
        }
        return $this->render('form', compact('model', 'items', 'title'));
    }

    /**
     * อ่านบรรทัดประมาณการที่ส่งมา พร้อมตรวจความถูกต้องก่อนแตะฐานข้อมูล
     *
     * แถวที่ว่างทั้งแถวถูกทิ้งเงียบ ๆ เพราะฟอร์มมีปุ่มเพิ่มแถว ผู้ใช้มักกดเผื่อไว้
     * แล้วไม่ได้กรอก การเด้ง error ใส่ทุกครั้งจะน่ารำคาญโดยไม่ได้ช่วยอะไร
     *
     * @return array{rows: array, models: FinanceLoanItem[], errors: string[]}
     */
    private function postedItems(): array
    {
        $rows = [];
        $models = [];
        $errors = [];
        foreach ((array) Yii::$app->request->post('LoanItem', []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $isBlank = !array_filter([
                $row['item_kind_id'] ?? '', $row['label'] ?? '', $row['persons'] ?? '',
                $row['units'] ?? '', $row['rate'] ?? '', $row['amount'] ?? '',
            ], static fn($value) => trim((string) $value) !== '' && (string) $value !== '0');
            if ($isBlank) {
                continue;
            }
            $item = new FinanceLoanItem();
            $item->setAttributes([
                'item_kind_id' => $row['item_kind_id'] ?: null,
                'label' => $row['label'] ?? null,
                'persons' => $row['persons'] !== '' ? $row['persons'] : null,
                'units' => $row['units'] !== '' ? $row['units'] : null,
                'rate' => $row['rate'] !== '' ? $row['rate'] : null,
                'amount' => $row['amount'] !== '' ? $row['amount'] : 0,
                'note' => $row['note'] ?? null,
                'sort_order' => count($rows) * 10,
            ], false);
            $item->loan_id = 0; // ยังไม่มีใบยืมตอนตรวจ ใส่ค่าหลอกให้ผ่านกฎ required
            if (!$item->validate(['label', 'persons', 'units', 'rate', 'amount', 'item_kind_id'])) {
                $errors = array_merge($errors, array_values($item->getFirstErrors()));
            }
            $row['id'] = $row['id'] ?? null;
            $row['sort_order'] = $item->sort_order;
            $rows[] = $row;
            $models[] = $item;
        }
        return ['rows' => $rows, 'models' => $models, 'errors' => $errors];
    }

    /** ปรับบรรทัดให้ตรงกับที่ส่งมา — แก้ของเดิม เพิ่มของใหม่ ลบที่หายไป */
    private function syncItems(FinanceLoan $loan, array $rows): void
    {
        $existing = [];
        foreach ($loan->getItems()->all() as $item) {
            $existing[$item->id] = $item;
        }
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $item = ($id && isset($existing[$id])) ? $existing[$id] : new FinanceLoanItem(['loan_id' => $loan->id]);
            unset($existing[$id]);
            $item->setAttributes([
                'item_kind_id' => $row['item_kind_id'] ?: null,
                'label' => trim((string) ($row['label'] ?? '')) ?: null,
                'persons' => ($row['persons'] ?? '') !== '' ? $row['persons'] : null,
                'units' => ($row['units'] ?? '') !== '' ? $row['units'] : null,
                'rate' => ($row['rate'] ?? '') !== '' ? $row['rate'] : null,
                'amount' => ($row['amount'] ?? '') !== '' ? $row['amount'] : 0,
                'note' => trim((string) ($row['note'] ?? '')) ?: null,
                'sort_order' => (int) $row['sort_order'],
            ], false);
            $item->loan_id = $loan->id;
            if (!$item->save()) {
                throw new \RuntimeException('บรรทัด "' . $item->displayName() . '": ' . implode(' ', $item->getFirstErrors()));
            }
        }
        foreach ($existing as $orphan) {
            $orphan->delete();
        }
    }

    private function findModel($id): FinanceLoan
    {
        $model = FinanceLoan::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบใบยืมเงินที่ต้องการ');
        }
        return $model;
    }

    // ── ที่เก็บผลตรวจไฟล์นำเข้า ─────────────────────────────────

    /** เขียน preview ลงไฟล์ แล้วเก็บเฉพาะ token ไว้ใน session */
    private function storePreview(array $preview): array
    {
        $token = Yii::$app->security->generateRandomString(32);
        $preview['token'] = $token;
        FileHelper::createDirectory($this->previewDir());
        if (file_put_contents($this->previewPath($token), json_encode($preview, JSON_UNESCAPED_UNICODE)) === false) {
            throw new \RuntimeException('บันทึกข้อมูลตัวอย่างไม่สำเร็จ กรุณาลองอีกครั้ง');
        }
        Yii::$app->session->set(self::IMPORT_SESSION, $token);
        return $preview;
    }

    private function loadPreview(): ?array
    {
        $token = (string) Yii::$app->session->get(self::IMPORT_SESSION, '');
        $path = $token === '' ? null : $this->previewPath($token);
        if (!$path || !is_file($path)) {
            Yii::$app->session->remove(self::IMPORT_SESSION);
            return null;
        }
        $preview = json_decode((string) file_get_contents($path), true);
        if (!is_array($preview) || !isset($preview['rows'])) {
            $this->clearPreview();
            return null;
        }
        $preview['token'] = $token;
        return $preview;
    }

    private function clearPreview(): void
    {
        $token = (string) Yii::$app->session->get(self::IMPORT_SESSION, '');
        if ($token !== '' && ($path = $this->previewPath($token))) {
            @unlink($path);
        }
        Yii::$app->session->remove(self::IMPORT_SESSION);
    }

    /** เก็บกวาดไฟล์ตัวอย่างที่ผู้ใช้ทิ้งค้างไว้ */
    private function purgeStalePreviews(): void
    {
        $deadline = time() - self::PREVIEW_TTL;
        foreach (glob($this->previewDir() . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
            if (@filemtime($file) < $deadline) {
                @unlink($file);
            }
        }
    }

    private function previewDir(): string
    {
        return Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'finance-loan-import';
    }

    /** token มาจาก session เท่านั้น แต่ยังตรวจรูปแบบก่อนนำไปประกอบ path */
    private function previewPath(string $token): ?string
    {
        return preg_match('/^[A-Za-z0-9_-]{32}$/', $token)
            ? $this->previewDir() . DIRECTORY_SEPARATOR . $token . '.json'
            : null;
    }
}
