<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocSearch;
use app\modules\purchase\models\DocTemplate;
use app\modules\purchase\models\Order;
use app\modules\purchase\components\DocMergeEngine;
use app\modules\purchase\components\LegacyDocCatalog;
use app\modules\purchase\components\DocRenderer;
use app\modules\purchase\components\DocWordExporter;

/**
 * สร้างเอกสารจากแม่แบบ แก้ไขบนจอ แล้วพิมพ์
 *
 * ระบบพิมพ์เดิมของ ERP (/ms-word/purchase_1..12 ที่เรียกจากหน้าใบขอซื้อ) ยังอยู่ครบ
 * และไม่ได้ถูกแตะ controller นี้เป็นทางที่สองที่เดินขนานกันไป ต่างกันที่ทางเดิมได้
 * ไฟล์ .docx ที่ต้องเอาไปเปิด Word ก่อนแก้ ส่วนทางนี้แก้บนจอได้เลยแล้วค่อยพิมพ์
 *
 * สิทธิ์: role 'purchase' เท่านั้น (ชุดเดียวกับงานสัญญาและงานหลักประกัน)
 * route ชุดนี้อยู่ใน allow list ของ AccessControl ระดับแอป และกันสิทธิ์เองที่ behaviors()
 */
class DocController extends Controller
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
                    'save' => ['POST'],
                    'reset' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $year = (int) AppHelper::YearBudget();

        $templates = DocTemplate::find()
            ->where(['active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // เตรียมรายการเรื่องอ้างอิงเฉพาะชนิดที่การ์ดบนหน้านี้ใช้จริง ไม่ดึงมาทุกชนิด
        // เพราะแต่ละชนิดคือ query ไปอีกตาราง ถ้าไม่มีการ์ดไหนใช้ก็ไม่ต้องเสียเวลาโหลด
        $refTypes = [];
        foreach ($templates as $template) {
            if ($template->ref_type !== DocTemplate::REF_NONE) {
                $refTypes[$template->ref_type] = true;
            }
        }

        $refOptions = [];
        $refDefault = [];
        foreach (array_keys($refTypes) as $refType) {
            $refOptions[$refType] = self::refOptions($refType, $year);
            // ค่าเริ่มต้นคือรายการล่าสุด ผู้ใช้จึงกดการ์ดได้เลยโดยไม่ต้องเลือกอะไรก่อน
            $refDefault[$refType] = $refOptions[$refType][0]['id'] ?? null;
        }

        // เอกสารชุดเดิมที่ยังไม่ได้แปลงเป็นแม่แบบ HTML ต้องขึ้นเป็นการ์ดด้วย ไม่ใช่ซ่อนไว้
        // ไม่งั้นหน้านี้แสดงเอกสารไม่ครบตามที่งานพัสดุมีจริง แล้วผู้ใช้ต้องกลับไปหาเอกสาร
        // ที่เหลือจากเมนูในหน้ารายการจัดซื้อจัดจ้างอีกทาง ซึ่งเท่ากับต้องจำว่าใบไหนอยู่ที่ไหน
        $legacy = [];
        foreach (LegacyDocCatalog::resolved() as $doc) {
            if (!$doc['converted']) {
                $legacy[] = $doc;
            }
        }

        return $this->render('index', [
            'templates' => $templates,
            'legacy' => $legacy,
            'legacyProgress' => LegacyDocCatalog::progress(),
            'refOptions' => $refOptions,
            'refDefault' => $refDefault,
            'year' => $year,
        ]);
    }

    /**
     * ทะเบียนเอกสารที่สร้างแล้ว
     *
     * แยกออกจากหน้าแรกเพราะคนเปิดเมนูนี้ตั้งใจมา "พิมพ์เอกสาร" ไม่ใช่มา "ดูทะเบียน"
     * เอาทะเบียนขึ้นก่อนทำให้ต้องกดเพิ่มอีกชั้นทุกครั้งกว่าจะได้เริ่มงานจริง
     */
    public function actionRegister()
    {
        $searchModel = new DocSearch();
        if (!$this->request->get('DocSearch')) {
            $searchModel->thai_year = (int) AppHelper::YearBudget();
        }
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('register', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'counters' => $searchModel->counters(),
        ]);
    }

    /**
     * กดการ์ดเดียวแล้วได้กระดาษเลย — สร้างเอกสารแล้วตอบเป็นหน้าแก้ไขในทีเดียว
     *
     * ตอบด้วยโครงสร้าง JSON แบบเดียวกับ actionEditor เพื่อให้การ์ดใช้ class
     * open-modal ของ ERP ได้ตรง ๆ ไม่ต้องโหลดหน้าใหม่แล้วค่อยเปิด modal ทีหลัง
     * (วิธีเดิมที่ redirect กลับไปหน้าทะเบียนพร้อม ?open= เป็นต้นเหตุของอาการ
     * backdrop ค้างจนหน้าเว็บกดไม่ได้ ทางนี้ตัดปัญหานั้นไปทั้งกอง)
     *
     * ถ้ามีร่างของแม่แบบ+เรื่องเดียวกันที่ผู้ใช้คนนี้สร้างไว้แล้วและยังไม่เคยแก้หรือพิมพ์
     * จะหยิบใบนั้นมาใช้ต่อ ไม่สร้างใบใหม่ซ้อน — กันร่างเปล่ากองในทะเบียนเวลาผู้ใช้
     * กดการ์ดหลายรอบ และทำให้การเรียกซ้ำด้วย GET ไม่สร้างข้อมูลงอกทุกครั้ง
     */
    public function actionQuick($template_id, $ref_id = null)
    {
        $template = DocTemplate::findOne(['id' => (int) $template_id, 'active' => 1]);
        if ($template === null) {
            throw new NotFoundHttpException('ไม่พบแม่แบบเอกสารที่เลือก');
        }

        $refId = ($template->ref_type === DocTemplate::REF_NONE || $ref_id === null || $ref_id === '')
            ? null
            : (int) $ref_id;

        if ($template->ref_type !== DocTemplate::REF_NONE && $refId === null) {
            return $this->asJson([
                'status' => 'error',
                'message' => 'กรุณาเลือก' . $template->refTypeName() . 'ที่ด้านบนก่อนกดสร้างเอกสารใบนี้',
            ]);
        }

        $model = $this->reusableDraft($template, $refId) ?: $this->buildDoc($template, $refId);

        if ($model->hasErrors()) {
            return $this->asJson([
                'status' => 'error',
                'message' => implode(' ', array_merge(...array_values($model->getErrors()))),
            ]);
        }

        return $this->asJson($this->editorResponse($model));
    }

    /**
     * ร่างเดิมที่ยังไม่ถูกแตะ ใช้ซ้ำได้
     *
     * "ยังไม่ถูกแตะ" คือยังไม่เคยพิมพ์และเนื้อความยังเท่ากับที่ merge มาตอนสร้าง
     * ถ้าผู้ใช้เคยแก้ไปแล้วต้องถือว่าเป็นงานของเขา ห้ามเอามาทับด้วยการ merge ใหม่
     */
    private function reusableDraft(DocTemplate $template, ?int $refId): ?Doc
    {
        return Doc::find()
            ->where([
                'deleted_at' => null,
                'template_id' => $template->id,
                'ref_id' => $refId,
                'status' => Doc::STATUS_DRAFT,
                'created_by' => Yii::$app->user->id,
                'printed_at' => null,
                'print_count' => 0,
            ])
            ->andWhere(['=', 'created_at', new \yii\db\Expression('updated_at')])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    /** สร้างเอกสารใบใหม่จากแม่แบบ + เรื่องอ้างอิง */
    private function buildDoc(DocTemplate $template, ?int $refId): Doc
    {
        $model = new Doc([
            'thai_year' => (int) AppHelper::YearBudget(),
            'doc_date' => date('Y-m-d'),
            'status' => Doc::STATUS_DRAFT,
            'template_id' => $template->id,
            'template_code' => $template->code,
            'title' => $template->name,
            'ref_type' => $template->ref_type,
            'ref_id' => $refId,
            'orientation' => $template->orientation,
            'emblem' => $template->emblem,
            'font_size' => $template->font_size,
            'margin_json' => $template->margins(),
        ]);

        $emp = UserHelper::GetEmployee();
        if ($emp) {
            $model->emp_id = $emp->id;
            $model->department_id = $emp->department;
        }

        $model->body_html = DocMergeEngine::merge(
            $template->body_html,
            DocMergeEngine::payload($model, $model->refModel())
        );

        $model->save();

        return $model;
    }

    /**
     * สร้างเอกสารฉบับใหม่จากแม่แบบ
     *
     * ตอนกดบันทึก ระบบ merge ค่าจากเรื่องต้นทางลง body_html ทันทีแล้วหยุดไว้ที่นั่น
     * ไม่ได้เก็บแต่ template_id ไว้ render ใหม่ตอนพิมพ์ เพราะเอกสารที่ลงนามไปแล้ว
     * ต้องพิมพ์ซ้ำได้เหมือนฉบับเดิมเสมอ แม้แม่แบบจะถูกแก้ไปแล้วก็ตาม
     */
    public function actionCreate($template_id = null, $ref_type = null, $ref_id = null)
    {
        $model = new Doc([
            'thai_year' => (int) AppHelper::YearBudget(),
            'doc_date' => date('Y-m-d'),
            'status' => Doc::STATUS_DRAFT,
            'ref_type' => $ref_type ?: DocTemplate::REF_ORDER,
            'ref_id' => $ref_id ? (int) $ref_id : null,
            'template_id' => $template_id ? (int) $template_id : null,
        ]);

        $emp = UserHelper::GetEmployee();
        if ($emp) {
            $model->emp_id = $emp->id;
            $model->department_id = $emp->department;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            $template = DocTemplate::findOne(['id' => $model->template_id, 'active' => 1]);

            if ($template === null) {
                $model->addError('template_id', 'กรุณาเลือกแม่แบบเอกสาร');
            } else {
                // ค่าหน้ากระดาษยึดตามแม่แบบตอนสร้าง แล้วผู้ใช้ปรับต่อได้บนหน้าแก้ไข
                $model->template_code = $template->code;
                $model->ref_type = $template->ref_type;
                $model->orientation = $template->orientation;
                $model->emblem = $template->emblem;
                $model->font_size = $template->font_size;
                $model->margin_json = $template->margins();
                if (empty($model->title)) {
                    $model->title = $template->name;
                }

                if ($template->ref_type !== DocTemplate::REF_NONE && empty($model->ref_id)) {
                    $model->addError('ref_id', 'กรุณาเลือก' . $template->refTypeName() . 'ที่จะใช้เป็นข้อมูลตั้งต้น');
                } else {
                    $model->body_html = DocMergeEngine::merge(
                        $template->body_html,
                        DocMergeEngine::payload($model, $model->refModel())
                    );

                    if ($model->save()) {
                        Yii::$app->session->setFlash(
                            'success',
                            'สร้างเอกสาร "' . $model->title . '" เรียบร้อย — กดที่ชื่อเอกสารเพื่อแก้ไขก่อนพิมพ์'
                        );

                        return $this->redirect(['register', 'open' => $model->id]);
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'templates' => DocTemplate::find()
                ->where(['active' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
        ]);
    }

    /**
     * หน้าแก้ไขเอกสารบนกระดาษ A4 — ตอบเป็น JSON ให้ตัวเปิด modal ของ ERP
     *
     * ส่ง initCallback กลับไปด้วยเพราะแถบเครื่องมือต้องผูก event หลังจากเนื้อหา
     * ถูกฉีดเข้า modal แล้ว ถ้าผูกตอน render จะยังไม่มี element ให้ผูก
     */
    public function actionEditor($id)
    {
        $model = $this->findModel($id);

        if (!$this->request->isAjax) {
            // เปิดตรงจาก URL (เช่นกดปุ่มย้อนกลับของเบราว์เซอร์) ต้องไม่ได้ JSON เปล่า ๆ
            return $this->redirect(['register']);
        }

        return $this->asJson($this->editorResponse($model));
    }

    /**
     * โครงสร้าง JSON ของหน้าแก้ไข ใช้ร่วมกันระหว่าง actionEditor กับ actionQuick
     *
     * เขียนที่เดียวเพราะสองทางเข้าต้องได้หน้าแก้ไขหน้าตาเหมือนกันเป๊ะ ถ้าแยกกันเขียน
     * วันหนึ่งจะมีทางใดทางหนึ่งได้ปุ่มหรือ initCallback ไม่ครบแล้วแถบเครื่องมือตาย
     */
    private function editorResponse(Doc $model): array
    {
        return [
            'status' => 'success',
            'title' => '<i class="bi bi-file-earmark-text me-1"></i>' . \yii\helpers\Html::encode($model->title)
                . ' <span class="badge text-bg-warning-subtle text-warning-emphasis ms-1">แก้ไขได้</span>',
            'content' => $this->renderAjax('editor', ['model' => $model]),
            'footer' => $this->renderAjax('_editor_footer', ['model' => $model]),
            'initCallback' => 'erpDocEditorInit',
        ];
    }

    /**
     * บันทึกเนื้อเอกสารที่แก้บนจอ
     *
     * ทั้งปุ่มพริ้นท์และปุ่มส่งออก Word เรียกตัวนี้ก่อนเสมอ ไม่ใช่เฉพาะปุ่มบันทึกร่าง
     * เพราะสิ่งที่ผู้ใช้เห็นบนจอคือสิ่งที่เขาคาดว่าจะได้บนกระดาษ ถ้าพิมพ์จาก body_html
     * ที่ยังไม่รวมการแก้ครั้งล่าสุด เขาจะได้กระดาษที่ไม่ตรงกับจอโดยไม่มีสัญญาณเตือน
     */
    public function actionSave($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($model->status === Doc::STATUS_FINAL) {
            return [
                'status' => 'error',
                'message' => 'เอกสารฉบับนี้ออกเลขแล้ว หากต้องแก้ไขให้เปลี่ยนสถานะกลับเป็นร่างก่อน',
            ];
        }

        $post = $this->request->post();
        $model->body_html = $post['body_html'] ?? $model->body_html;

        // สามค่านี้มาจากแถบเครื่องมือ ไม่ใช่จากฟอร์ม จึงรับเฉพาะตัวที่ส่งมาจริง
        foreach (['emblem', 'font_size', 'doc_no'] as $field) {
            if (array_key_exists($field, $post)) {
                $model->$field = $post[$field];
            }
        }

        if (!$model->save()) {
            return [
                'status' => 'error',
                'message' => 'บันทึกไม่สำเร็จ: ' . implode(' ', array_merge(...array_values($model->getErrors()))),
            ];
        }

        return ['status' => 'success', 'message' => 'บันทึกร่างเรียบร้อย'];
    }

    /**
     * ดึงข้อความจากแม่แบบมาทับของเดิม — ปุ่ม "รีเซ็ต"
     *
     * merge ค่าใหม่ทั้งฉบับ การแก้มือทั้งหมดที่ทำไว้จะหายไป ฝั่งหน้าจอจึงต้องถาม
     * ยืนยันก่อนเรียก ปุ่มนี้มีไว้สำหรับกรณีแก้จนเอกสารเพี้ยนแล้วอยากเริ่มใหม่
     */
    public function actionReset($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        $template = DocTemplate::findOne(['id' => $model->template_id]);
        if ($template === null) {
            return [
                'status' => 'error',
                'message' => 'แม่แบบต้นทางของเอกสารฉบับนี้ถูกลบไปแล้ว จึงไม่มีข้อความตั้งต้นให้ดึงกลับมา',
            ];
        }

        $model->body_html = DocMergeEngine::merge(
            $template->body_html,
            DocMergeEngine::payload($model, $model->refModel())
        );

        if (!$model->save()) {
            return ['status' => 'error', 'message' => 'รีเซ็ตไม่สำเร็จ'];
        }

        // ส่งเนื้อเอกสารที่เรนเดอร์แล้วกลับไปด้วย เพื่อให้หน้าแก้ไขวางทับได้ทันที
        // ไม่ต้องปิด modal แล้วเปิดใหม่ (ซึ่งทำให้ผู้ใช้เสียตำแหน่งที่กำลังดูอยู่)
        return [
            'status' => 'success',
            'message' => 'ดึงข้อความจากแม่แบบกลับมาแล้ว',
            'body_html' => DocRenderer::body($model),
        ];
    }

    /** เปิด PDF ในแท็บใหม่เพื่อให้ผู้ใช้สั่งพิมพ์ — ไม่ได้บังคับดาวน์โหลด */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $model->markPrinted();

        $pdf = DocRenderer::pdf($model);

        return Yii::$app->response->sendContentAsFile($pdf, $model->safeFileName('pdf'), [
            'mimeType' => 'application/pdf',
            'inline' => true,
        ]);
    }

    public function actionWord($id)
    {
        $model = $this->findModel($id);
        $model->markPrinted();

        return DocWordExporter::send($model);
    }

    /** สลับร่าง/ออกเลขแล้ว — เอกสารที่ออกเลขแล้วถูกล็อกไม่ให้แก้เนื้อความ */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes([
            'status' => $model->status === Doc::STATUS_FINAL ? Doc::STATUS_DRAFT : Doc::STATUS_FINAL,
        ]);

        Yii::$app->session->setFlash('success', 'เปลี่ยนสถานะเป็น "' . $model->statusName() . '" เรียบร้อย');

        return $this->redirect(['register']);
    }

    /**
     * ลบแบบ soft delete
     *
     * ไม่ลบจริงเพราะทะเบียนเอกสารเป็นร่องรอยว่าหน่วยงานออกหนังสือฉบับใดไปเมื่อไร
     * ถ้าลบจริง เลขที่หนังสือที่ใช้ไปแล้วจะหายจากระบบทั้งที่กระดาษยังอยู่ในแฟ้ม
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes([
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => Yii::$app->user->id,
        ]);

        Yii::$app->session->setFlash('success', 'ลบเอกสาร "' . $model->title . '" ออกจากทะเบียนเรียบร้อย');

        return $this->redirect(['register']);
    }

    /**
     * รายการเรื่องต้นทางให้เลือกในหน้าสร้างเอกสาร
     *
     * แยกเป็น action ของตัวเองเพราะรายการขึ้นกับแม่แบบที่เลือก ถ้า render ทุกชุด
     * มาพร้อมหน้าตั้งแต่แรก หน้าสร้างเอกสารจะต้องโหลดใบขอซื้อทั้งปีมารอไว้เปล่า ๆ
     */
    public function actionRefOptions($ref_type, $thai_year = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $year = $thai_year ? (int) $thai_year : (int) AppHelper::YearBudget();

        return [
            'status' => 'success',
            'ref_type' => $ref_type,
            'options' => self::refOptions($ref_type, $year),
        ];
    }

    /**
     * @return array<int, array{id:int, label:string}>
     */
    public static function refOptions(string $refType, int $year): array
    {
        $out = [];

        switch ($refType) {
            case DocTemplate::REF_ORDER:
                // ใบขอซื้อของ ERP นี้เก็บหัวเรื่องกับรายการลูกในตารางเดียวกัน
                // หัวเรื่องคือแถวที่ name='order' ตามที่ ContractController ใช้อยู่
                $rows = Order::find()
                    ->where(['name' => 'order', 'deleted_at' => null])
                    ->andFilterWhere(['thai_year' => $year])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(300)
                    ->all();
                foreach ($rows as $row) {
                    $no = $row->pr_number ?: $row->po_number ?: ('#' . $row->id);
                    $type = $row->data_json['product_type_name'] ?? '';
                    $out[] = [
                        'id' => (int) $row->id,
                        'label' => trim($no . ($type ? ' — ' . $type : '')),
                    ];
                }
                break;

            case DocTemplate::REF_CONTRACT:
                $rows = Contract::find()
                    ->where(['deleted_at' => null])
                    ->andFilterWhere(['thai_year' => $year])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(300)
                    ->all();
                foreach ($rows as $row) {
                    $no = $row->contract_no ?: $row->doc_no ?: ('#' . $row->id);
                    $out[] = ['id' => (int) $row->id, 'label' => trim($no . ' — ' . $row->title)];
                }
                break;

            case DocTemplate::REF_BOND:
                $rows = Bond::find()
                    ->where(['deleted_at' => null])
                    ->andFilterWhere(['thai_year' => $year])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(300)
                    ->all();
                foreach ($rows as $row) {
                    $no = $row->doc_no ?: ('#' . $row->id);
                    $out[] = ['id' => (int) $row->id, 'label' => trim($no . ' — ' . $row->title)];
                }
                break;
        }

        return $out;
    }

    protected function findModel($id)
    {
        $model = Doc::findOne(['id' => $id, 'deleted_at' => null]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }

        return $model;
    }
}
