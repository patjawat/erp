<?php

namespace app\modules\pdfTemplate\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\leave\models\LeaveType;
use app\modules\pdfTemplate\models\PdfTemplate;
use app\modules\pdfTemplate\models\PdfTemplateField;
use app\modules\pdfTemplate\services\PdfTemplateService;
use app\modules\pdfTemplate\Module;

/**
 * PDF Template positioning editor — resolution-independent coordinates.
 */
class TemplateController extends Controller
{
    private PdfTemplateService $templateService;

    public function __construct($id, $module, ?PdfTemplateService $templateService = null, $config = [])
    {
        $this->templateService = $templateService ?? new PdfTemplateService();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'save-layout' => ['POST'],
                    'set-template-for-context' => ['POST'],
                    'upload-template' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * List templates and setting: which template to use for development print.
     */
    public function actionIndex(): string
    {
        $templates = PdfTemplate::find()->orderBy(['id' => SORT_DESC])->all();
        $developmentTemplate = PdfTemplate::findForContext(PdfTemplate::CONTEXT_DEVELOPMENT);
        $leaveTemplate = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_LEAVE])->one();
        $leaveRestTemplate = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_LEAVE_REST])->one();
        $repairNoticeTemplate = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_HELPDESK2_REPAIR_NOTICE])->one();
        $bookingVehicleCentralTemplate = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_BOOKING_VEHICLE_CENTRAL])->one();
        $bookingVehicleOfficialTemplate = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_BOOKING_VEHICLE_OFFICIAL])->one();
        return $this->render('index', [
            'templates' => $templates,
            'developmentTemplateId' => $developmentTemplate ? (int) $developmentTemplate->id : null,
            'leaveTemplateId' => $leaveTemplate ? (int) $leaveTemplate->id : null,
            'leaveRestTemplateId' => $leaveRestTemplate ? (int) $leaveRestTemplate->id : null,
            'repairNoticeTemplateId' => $repairNoticeTemplate ? (int) $repairNoticeTemplate->id : null,
            'bookingVehicleCentralTemplateId' => $bookingVehicleCentralTemplate ? (int) $bookingVehicleCentralTemplate->id : null,
            'bookingVehicleOfficialTemplateId' => $bookingVehicleOfficialTemplate ? (int) $bookingVehicleOfficialTemplate->id : null,
        ]);
    }

    /**
     * ตั้งค่า: ใช้เทมเพลตใดสำหรับ context (เช่น ใบขอไปราชการ).
     */
    public function actionSetTemplateForContext(): \yii\web\Response
    {
        $context = Yii::$app->request->post('context', '');
        $templateId = (int) Yii::$app->request->post('template_id', 0);
        $allowed = [
            PdfTemplate::CONTEXT_DEVELOPMENT,
            PdfTemplate::CONTEXT_LEAVE,
            PdfTemplate::CONTEXT_LEAVE_REST,
            PdfTemplate::CONTEXT_HELPDESK2_REPAIR_NOTICE,
            PdfTemplate::CONTEXT_BOOKING_VEHICLE_CENTRAL,
            PdfTemplate::CONTEXT_BOOKING_VEHICLE_OFFICIAL,
        ];
        if (!in_array($context, $allowed, true)) {
            Yii::$app->session->setFlash('error', 'ไม่รู้จัก context');
            return $this->redirect(['index']);
        }
        PdfTemplate::updateAll(['use_for_context' => null], ['use_for_context' => $context]);
        if ($templateId > 0) {
            $t = PdfTemplate::findOne($templateId);
            if ($t) {
                $t->use_for_context = $context;
                $t->save(false);
            }
        }
        $messages = [
            PdfTemplate::CONTEXT_DEVELOPMENT => 'บันทึกการตั้งค่าเทมเพลตสำหรับใบขอไปราชการแล้ว',
            PdfTemplate::CONTEXT_LEAVE => 'บันทึกการตั้งค่าเทมเพลตสำหรับใบลา (ป่วย/คลอด/กิจ) แล้ว',
            PdfTemplate::CONTEXT_LEAVE_REST => 'บันทึกการตั้งค่าเทมเพลตสำหรับใบลาพักผ่อนแล้ว',
            PdfTemplate::CONTEXT_HELPDESK2_REPAIR_NOTICE => 'บันทึกการตั้งค่าเทมเพลตสำหรับใบส่งซ่อมแล้ว',
            PdfTemplate::CONTEXT_BOOKING_VEHICLE_CENTRAL => 'บันทึกการตั้งค่าเทมเพลตสำหรับขอใช้รถยนต์ส่วนกลางแล้ว',
            PdfTemplate::CONTEXT_BOOKING_VEHICLE_OFFICIAL => 'บันทึกการตั้งค่าเทมเพลตสำหรับขอใช้รถยนต์ส่วนเดินทางไปราชการแล้ว',
        ];
        $msg = $messages[$context] ?? 'บันทึกการตั้งค่าเทมเพลตแล้ว';
        Yii::$app->session->setFlash('success', $msg);
        return $this->redirect(['index']);
    }

    /**
     * เพิ่มเทมเพลตใหม่ (ชื่อ + อัปโหลด PDF ผ่าน filemanager)
     */
    public function actionCreate()
    {
        $model = new PdfTemplate();
        $model->page_width = 210;
        $model->page_height = 297;
        if (Yii::$app->request->isPost) {
            $model->name = Yii::$app->request->post('name', '');
            $file = UploadedFile::getInstanceByName('pdf_file');
            if ($model->name && $file && strtolower($file->extension) === 'pdf') {
                $model->file_path = '';
                $model->save(false);
                $upload = FileManagerHelper::savePdfFile($file, 'pdf_templates', 'template_' . $model->id);
                if ($upload) {
                    $model->upload_id = $upload->id;
                    $model->save(false);
                    Yii::$app->session->setFlash('success', 'เพิ่มเทมเพลตเรียบร้อย');
                    return $this->redirect(['index']);
                }
                $model->delete();
            }
            Yii::$app->session->setFlash('error', 'กรุณากรอกชื่อและเลือกไฟล์ PDF');
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * แก้ไขเทมเพลต (ชื่อ และ/หรือ อัปโหลด PDF ใหม่ ผ่าน filemanager).
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findTemplate($id);
        if (Yii::$app->request->isPost) {
            $model->name = Yii::$app->request->post('name', $model->name);
            $file = UploadedFile::getInstanceByName('pdf_file');
            if ($file && strtolower($file->extension) === 'pdf') {
                $upload = FileManagerHelper::savePdfFile($file, 'pdf_templates', 'template_' . $id);
                if ($upload) {
                    $oldPath = !$model->upload_id && $model->file_path
                        ? Yii::getAlias('@webroot') . '/' . ltrim($model->file_path, '/') : null;
                    $model->upload_id = $upload->id;
                    $model->file_path = '';
                    $model->save(false);
                    if ($oldPath && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            } else {
                $model->save(false);
            }
            Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเทมเพลตแล้ว');
            return $this->redirect(['index']);
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * ลบเทมเพลต (และฟิลด์ที่ผูกไว้); ไฟล์ PDF ใน filemanager หรือ webroot จะถูกลบถ้ามีอยู่.
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findTemplate($id);
        if ($model->upload_id) {
            FileManagerHelper::Deletefile($model->upload_id);
        } elseif ($model->file_path) {
            $filePath = Yii::getAlias('@webroot') . '/' . ltrim($model->file_path, '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        PdfTemplateField::deleteAll(['template_id' => $id]);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบเทมเพลตแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * อัปโหลดไฟล์ PDF ต้นแบบใหม่แทนของเดิม (ใช้ในหน้า editor, ผ่าน filemanager).
     */
    public function actionUploadTemplate(int $template_id): \yii\web\Response
    {
        $template = $this->findTemplate($template_id);
        $file = UploadedFile::getInstanceByName('pdf_file');
        if (!$file || strtolower($file->extension) !== 'pdf') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ PDF');
            return $this->redirect(['editor', 'template_id' => $template_id]);
        }
        $upload = FileManagerHelper::savePdfFile($file, 'pdf_templates', 'template_' . $template_id);
        if (!$upload) {
            Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
            return $this->redirect(['editor', 'template_id' => $template_id]);
        }
        $oldPath = !$template->upload_id && $template->file_path
            ? Yii::getAlias('@webroot') . '/' . ltrim($template->file_path, '/') : null;
        $template->upload_id = $upload->id;
        $template->file_path = '';
        $template->save(false);
        if ($oldPath && is_file($oldPath)) {
            @unlink($oldPath);
        }
        Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF ใหม่เรียบร้อย หน้านี้จะโหลด PDF ใหม่');
        return $this->redirect(['editor', 'template_id' => $template_id]);
    }

    /**
     * ส่งออก config ตำแหน่งฟิลด์เป็นไฟล์ JSON สำหรับเก็บหรือแชร์ให้คนอื่นนำเข้า
     */
    public function actionExportConfig(int $id): Response
    {
        $template = $this->findTemplate($id);
        $config = $this->templateService->exportLayoutConfig($id);
        $filename = 'pdf-template-config-' . preg_replace('/[^a-z0-9_-]/i', '-', $template->name) . '-' . date('Y-m-d') . '.json';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/json; charset=utf-8');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        Yii::$app->response->content = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return Yii::$app->response;
    }

    /**
     * ดาวน์โหลดไฟล์ PDF ต้นฉบับของเทมเพลต (สำหรับใช้เป็นแม่แบบภายนอก / เก็บสำรอง)
     */
    public function actionDownloadTemplateFile(int $id): Response
    {
        $template = $this->findTemplate($id);
        $filePath = null;

        if ($template->upload_id) {
            $filePath = FileManagerHelper::getFilePath($template->upload_id);
        } elseif ($template->file_path) {
            $filePath = Yii::getAlias('@webroot') . '/' . ltrim($template->file_path, '/');
        }

        if (!$filePath || !is_file($filePath)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์ PDF เทมเพลต');
            return $this->redirect(['editor', 'template_id' => $template->id]);
        }

        $filename = 'pdf-template-' . preg_replace('/[^a-z0-9_-]/i', '-', $template->name) . '.pdf';
        return Yii::$app->response->sendFile($filePath, $filename, [
            'mimeType' => 'application/pdf',
            'inline' => false,
        ]);
    }

    /**
     * นำเข้า config ตำแหน่งฟิลด์จากไฟล์ JSON (ที่ส่งออกจากเทมเพลตอื่น).
     * GET: แสดงฟอร์มเลือกเทมเพลต + อัปโหลดไฟล์. POST: รับ template_id + ไฟล์ แล้วบันทึก layout
     */
    public function actionImportConfig()
    {
        $templates = PdfTemplate::find()->orderBy(['id' => SORT_DESC])->all();
        if (Yii::$app->request->isPost) {
            $templateId = (int) Yii::$app->request->post('template_id', 0);
            $template = $templateId > 0 ? PdfTemplate::findOne($templateId) : null;
            if (!$template) {
                Yii::$app->session->setFlash('error', 'กรุณาเลือกเทมเพลตที่จะนำเข้า config');
                return $this->redirect(['import-config']);
            }
            $file = \yii\web\UploadedFile::getInstanceByName('config_file');
            $jsonRaw = null;
            if ($file && $file->extension === 'json') {
                $jsonRaw = file_get_contents($file->tempName);
            } else {
                $jsonRaw = Yii::$app->request->post('config_json', '');
            }
            if ($jsonRaw === null || $jsonRaw === '') {
                Yii::$app->session->setFlash('error', 'กรุณาอัปโหลดไฟล์ JSON หรือวางเนื้อหา config');
                return $this->redirect(['import-config']);
            }
            $config = json_decode($jsonRaw, true);
            if (!is_array($config) || !isset($config['fields']) || !is_array($config['fields'])) {
                Yii::$app->session->setFlash('error', 'รูปแบบไฟล์ไม่ถูกต้อง ต้องมี key "fields" เป็น array');
                return $this->redirect(['import-config']);
            }
            $ok = $this->templateService->saveLayout($template->id, $config['fields']);
            if ($ok && !empty($config['data_source_id']) && is_string($config['data_source_id'])) {
                $registry = $this->getDataSourceRegistry();
                $sources = $registry->getSources();
                $validIds = $sources ? array_column($sources, 'id') : [];
                if (in_array($config['data_source_id'], $validIds, true)) {
                    $template->data_source_id = $config['data_source_id'];
                    $template->save(false);
                }
            }
            if ($ok) {
                Yii::$app->session->setFlash('success', 'นำเข้า config ตำแหน่งฟิลด์ลงเทมเพลต «' . $template->name . '» เรียบร้อย');
                return $this->redirect(['editor', 'template_id' => $template->id]);
            }
            Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ');
        }
        return $this->render('import-config', ['templates' => $templates]);
    }

    /**
     * Editor: PDF preview + draggable fields. Coordinates normalized (0–1).
     */
    public function actionEditor(int $template_id): string
    {
        $template = $this->findTemplate($template_id);
        $layout = $this->templateService->loadLayout($template_id);
        $serveUrl = $this->getServePdfUrl($template);
        $registry = $this->getDataSourceRegistry();
        $dataSources = $registry->getSources();
        $sourceIds = $dataSources ? array_column($dataSources, 'id') : [];
        $selectedSourceId = $template->data_source_id && in_array($template->data_source_id, $sourceIds, true)
            ? $template->data_source_id
            : ($dataSources ? $dataSources[0]['id'] : '');
        $fieldDefinitions = $dataSources ? $registry->getFieldDefinitions($selectedSourceId) : $this->getDefaultFieldDefinitions();
        $developmentPrintDataUrl = \yii\helpers\Url::to(['/hr/development/print-data']);
        $leavePrintDataUrl = \yii\helpers\Url::to(['/leave/setting/leave-print-data']);
        $bookingPrintDataUrl = \yii\helpers\Url::to(['/booking/vehicle/print-data']);
        return $this->render('editor', [
            'template' => $template,
            'layout' => $layout,
            'serveUrl' => $serveUrl,
            'fieldDefinitions' => $fieldDefinitions,
            'dataSources' => $dataSources,
            'selectedSourceId' => $selectedSourceId,
            'fieldsForSourceUrl' => \yii\helpers\Url::to(['fields-for-source', 'template_id' => $template_id]),
            'developmentPrintDataUrl' => $developmentPrintDataUrl,
            'leavePrintDataUrl' => $leavePrintDataUrl,
            'bookingPrintDataUrl' => $bookingPrintDataUrl,
            'leaveTypeOptions' => $this->getLeaveTypeOptions(),
        ]);
    }

    /**
     * API: list data sources (id, label) for dropdown.
     */
    public function actionDataSources(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $registry = $this->getDataSourceRegistry();
        return $registry->getSources();
    }

    /**
     * API: field definitions for a given source (source path + label). For dynamic field list in editor.
     */
    public function actionFieldsForSource(string $source_id, int $template_id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findTemplate($template_id);
        $registry = $this->getDataSourceRegistry();
        $fields = $registry->getFieldDefinitions($source_id);
        return ['fields' => $fields];
    }

    /**
     * Save layout (AJAX). Expects JSON body: { fields: [ { field, page, x_percent, y_percent, ... } ] }.
     */
    public function actionSaveLayout(int $template_id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $template = $this->findTemplate($template_id);
        $body = json_decode(Yii::$app->request->getRawBody(), true) ?: [];
        $fields = $body['fields'] ?? [];
        if (!is_array($fields)) {
            return ['success' => false, 'message' => 'Invalid fields'];
        }
        $ok = $this->templateService->saveLayout($template_id, $fields);
        if ($ok && array_key_exists('data_source_id', $body)) {
            $registry = $this->getDataSourceRegistry();
            $sources = $registry->getSources();
            $validIds = $sources ? array_column($sources, 'id') : [];
            $dsId = $body['data_source_id'];
            if ($dsId === '' || $dsId === null || in_array($dsId, $validIds, true)) {
                $template->data_source_id = $dsId ?: null;
                $template->save(false);
            }
        }
        return ['success' => $ok];
    }

    /**
     * Leave type dropdown options for editor field settings.
     *
     * @return array<int, array{code: string, title: string}>
     */
    private function getLeaveTypeOptions(): array
    {
        $rows = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['code' => SORT_ASC])
            ->all();
        $out = [];
        foreach ($rows as $row) {
            $code = (string) ($row->code ?? '');
            if ($code === '') {
                continue;
            }
            $out[] = [
                'code' => $code,
                'title' => (string) ($row->title ?? $code),
            ];
        }
        return $out;
    }

    /**
     * Preview data (layout + page size) for JS.
     */
    public function actionPreview(int $template_id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findTemplate($template_id);
        return $this->templateService->renderPreview($template_id);
    }

    /**
     * พิมพ์ตัวอย่าง PDF — เติมข้อมูลตัวอย่างลงตามตำแหน่งที่กำหนด แล้วส่งเป็น PDF ให้เปิด/ดาวน์โหลด
     */
    public function actionPrintSample(int $template_id): Response
    {
        $template = $this->findTemplate($template_id);
        try {
            $info = class_exists(\app\components\SiteHelper::class) ? \app\components\SiteHelper::getInfo() : [];
            $sample = [
                'organization_name' => (string) ($info['company_name'] ?? 'ชื่อหน่วยงาน (ตัวอย่าง)'),
                'document_number' => (string) ($info['doc_number'] ?? 'ที่ 1234/2569'),
                'custom_text' => '(ข้อความกำหนดเอง)',
                'officer_name' => 'นายสมชาย ตัวอย่าง',
                'document_date' => date('d/m/Y'),
                'topic' => 'เรื่อง ตัวอย่างรายงานขอไปราชการ',
                'location' => 'กรุงเทพมหานคร',
                'date_start' => '01/04/2569',
                'date_end' => '02/04/2569',
                'travel_party_members' => [
                    ['fullname' => 'นางสาวสมหญิง ตัวอย่าง', 'position' => 'นักวิชาการ'],
                    ['fullname' => 'นายสมศักดิ์ ตัวอย่าง', 'position' => 'เจ้าหน้าที่'],
                ],
                'assigned_to_fullname' => 'นายผู้มอบหมาย ตัวอย่าง',
                'assigned_to_position' => 'ผู้อำนวยการ',
                'assigned_to_signature' => '',
                'approver_1_status' => 'Pass',
                'approver_2_status' => 'Pass',
                'approver_3_status' => 'Reject',
                'approver_4_status' => '',
            ];
            $pdfBinary = $this->templateService->generatePdfWithData($template_id, $sample);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException('สร้าง PDF ไม่ได้: ' . $e->getMessage());
        }
        $filename = 'sample-' . preg_replace('/[^a-z0-9_-]/i', '-', $template->name) . '-' . date('Y-m-d') . '.pdf';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . addslashes($filename) . '"');
        Yii::$app->response->content = $pdfBinary;
        return Yii::$app->response;
    }

    /**
     * พิมพ์ใบลาเป็น PDF จากเทมเพลต (ข้อมูลจาก leave module).
     * พิมพ์ใบลาจากเทมเพลต — ปุ่มหลักใช้ /leave/leave/pdf (เรียก logic เดียวกันผ่าน PdfTemplateService)
     * ใช้เฉพาะเทมเพลตที่ตั้ง use_for_context = leave เท่านั้น (ไม่ fallback ไปเทมเพลตแรก เด็ดขาด)
     */
    public function actionPrintLeave(int $id): Response
    {
        try {
            $pdfBinary = $this->templateService->generateLeavePdfBinary((int) $id);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \yii\web\ServerErrorHttpException('สร้าง PDF ไม่ได้: ' . $e->getMessage());
        }
        $filename = 'leave-' . (int) $id . '.pdf';
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->content = $pdfBinary;
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . addslashes($filename) . '"');
        return $response;
    }

    /**
     * Serve PDF file for canvas/iframe (inline).
     */
    public function actionServePdf(int $id): Response
    {
        $template = $this->findTemplate($id);
        $path = $this->templateService->getTemplateFilePath($template);
        if ($path === null || !is_file($path)) {
            throw new NotFoundHttpException('Template file not found');
        }
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="template-' . $id . '.pdf"');
        Yii::$app->response->content = file_get_contents($path);
        return Yii::$app->response;
    }

    private function findTemplate(int $id): PdfTemplate
    {
        $template = PdfTemplate::findOne($id);
        if (!$template) {
            throw new NotFoundHttpException('Template not found');
        }
        return $template;
    }

    private function getServePdfUrl(PdfTemplate $template): string
    {
        $url = \yii\helpers\Url::to(['/pdf-template/template/serve-pdf', 'id' => $template->id]);
        $path = $this->templateService->getTemplateFilePath($template);
        if ($path && is_file($path)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'v=' . filemtime($path);
        }
        return $url;
    }

    private function getDefaultFieldDefinitions(): array
    {
        return [
            ['source' => 'officer_name', 'field' => 'officer_name', 'label' => 'ชื่อผู้รับผิดชอบ'],
            ['source' => 'document_date', 'field' => 'document_date', 'label' => 'วันที่เอกสาร'],
            ['source' => 'topic', 'field' => 'topic', 'label' => 'เรื่อง'],
            ['source' => 'location', 'field' => 'location', 'label' => 'สถานที่'],
            ['source' => 'date_start', 'field' => 'date_start', 'label' => 'วันที่เริ่ม'],
            ['source' => 'date_end', 'field' => 'date_end', 'label' => 'วันที่สิ้นสุด'],
        ];
    }

    private function getDataSourceRegistry(): \app\modules\pdfTemplate\services\DataSourceRegistry
    {
        $module = $this->module;
        if ($module instanceof Module && $module->dataSourceRegistry !== null) {
            return $module->dataSourceRegistry;
        }
        return new \app\modules\pdfTemplate\services\DataSourceRegistry([]);
    }
}
