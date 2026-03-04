<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use app\modules\helpdesk2\models\RepairFormSetting;
use app\modules\helpdesk2\models\Helpdesk;
use app\components\ThaiDateHelper;

const HELPDESK2_TEMPLATE_STORE_DIR = 'modules/filemanager/fileupload/helpdesk2_templates';

/**
 * การตั้งค่า — แบบฟอร์มใบส่งซ่อม (อัปโหลด PDF + กำหนดตำแหน่ง)
 * template เดียว ไม่แยกประเภท
 */
class SettingController extends Controller
{
    // ─────────────────────────────────────────────
    //  หน้าหลัก — แสดงสถานะ template
    // ─────────────────────────────────────────────

    public function actionIndex()
    {
        $hasTemplate = $this->hasTemplateFile();
        $templateUrl = $hasTemplate
            ? Url::to(['/helpdesk/setting/serve-template'])
            : null;

        return $this->render('index', [
            'hasTemplate' => $hasTemplate,
            'templateUrl' => $templateUrl,
        ]);
    }

    // ─────────────────────────────────────────────
    //  Serve PDF template ให้ iframe
    // ─────────────────────────────────────────────

    public function actionServeTemplate()
    {
        $path = $this->getTemplatePath();
        if (!is_file($path)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต');
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="repair-template.pdf"');
        Yii::$app->response->content = file_get_contents($path);
        return Yii::$app->response;
    }

    // ─────────────────────────────────────────────
    //  อัปโหลด template
    // ─────────────────────────────────────────────

    public function actionUploadTemplate()
    {
        $isAjax = Yii::$app->request->getIsAjax()
            || Yii::$app->request->getHeaders()->get('X-Requested-With') === 'XMLHttpRequest';

        if (!Yii::$app->request->isPost) {
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
            }
            return $this->redirect(['/helpdesk/setting/index']);
        }

        $file = $this->getUploadedPdfFile();
        if ($file === null) {
            $err = 'กรุณาเลือกไฟล์ PDF';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/helpdesk/setting/index']);
        }

        if (!$this->validatePdfFile($file)) {
            $err = 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/helpdesk/setting/index']);
        }

        $path = $this->getTemplatePath();
        FileHelper::createDirectory(dirname($path));

        if (!$file->saveAs($path)) {
            $err = 'บันทึกไฟล์ไม่สำเร็จ';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/helpdesk/setting/index']);
        }

        // ensure config record exists
        RepairFormSetting::getRecord();

        if ($isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'อัปโหลดเทมเพลต PDF เรียบร้อย'];
        }
        Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF เรียบร้อย');
        return $this->redirect(['/helpdesk/setting/index']);
    }

    // ─────────────────────────────────────────────
    //  ลบ template
    // ─────────────────────────────────────────────

    public function actionDeleteTemplate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $path = $this->getTemplatePath();
        if (is_file($path)) {
            @unlink($path);
        }
        return ['success' => true, 'message' => 'ลบเทมเพลตเรียบร้อย'];
    }

    // ─────────────────────────────────────────────
    //  กำหนดตำแหน่งข้อมูลบน PDF
    // ─────────────────────────────────────────────

    public function actionPositions()
    {
        if (!$this->hasTemplateFile()) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['/helpdesk/setting/index']);
        }

        $record      = RepairFormSetting::getRecord();
        $fieldLabels = RepairFormSetting::defaultFields();
        $items       = $this->getFormItems($record);

        return $this->render('positions', [
            'record'      => $record,
            'items'       => $items,
            'fieldLabels' => $fieldLabels,
            'templateUrl' => Url::to(['/helpdesk/setting/serve-template']),
        ]);
    }

    // ─────────────────────────────────────────────
    //  บันทึกตำแหน่ง (AJAX)
    // ─────────────────────────────────────────────

    public function actionSavePositions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $positions = Yii::$app->request->post('positions', []);
        if (empty($positions) && Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw) {
                $body      = json_decode($raw, true);
                $positions = $body['positions'] ?? [];
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }

        $defaults = RepairFormSetting::defaultFields();
        $items    = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) continue;
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) continue;
            $items[] = [
                'id'       => $itemId,
                'key'      => $key,
                'x'        => (float) ($pos['x']        ?? 0),
                'y'        => (float) ($pos['y']        ?? 0),
                'fontSize' => (int)   ($pos['fontSize']  ?? 15),
                'bold'     => (int)   ($pos['bold']      ?? 0),
                'enabled'  => (int)   ($pos['enabled']   ?? 1),
            ];
        }

        $record = RepairFormSetting::getRecord();
        if ($record->saveItems($items)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    // ─────────────────────────────────────────────
    //  เปิด/ปิดการใช้งานแบบฟอร์ม (AJAX toggle)
    // ─────────────────────────────────────────────

    public function actionToggleEnabled()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $record  = RepairFormSetting::getRecord();
        $newVal  = $record->toggleEnabled();
        return ['success' => true, 'enabled' => $newVal];
    }

    // ─────────────────────────────────────────────
    //  พิมพ์ใบส่งซ่อม PDF
    // ─────────────────────────────────────────────

    public function actionPrintForm($id)
    {
        $model = Helpdesk::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }

        if (!$this->hasTemplateFile()) {
            throw new NotFoundHttpException('ยังไม่มีเทมเพลต PDF กรุณาอัปโหลดที่การตั้งค่าแบบฟอร์มใบส่งซ่อม');
        }

        $record      = RepairFormSetting::getRecord();
        $items       = $this->getFormItems($record);
        $templatePath = $this->getTemplatePath();

        // ── รวบรวมค่าที่จะพิมพ์ ──
        $emp    = $model->emp;
        $values = [
            'emp_fullname'   => $emp ? $emp->fullname : '',
            'department'     => $emp ? $emp->departmentName() : '',
            'repair_number'  => $model->repair_number ?? '',
            'device_type'    => $model->deviceType ? $model->deviceType->title : '',
            'asset_number'   => $model->asset_number ?? '',
            'problem_detail' => $model->title ?? '',
            'repair_group'   => $model->viewRepairGroup() ?? '',
            'urgency'        => $model->viewUrgent()['title'] ?? '',
            'contact_phone'  => $emp && isset($emp->phone) ? $emp->phone : '',
            'create_date'    => $model->created_at
                ? ThaiDateHelper::formatThaiDate(explode(' ', $model->created_at)[0])
                : '',
        ];

        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            throw new \yii\web\ServerErrorHttpException('ระบบสร้าง PDF ยังไม่พร้อม');
        }

        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));
        }

        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        $pdf->AddFont('THSarabunNew', '',  'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
        $pdf->SetTextColor(0, 0, 0);

        $ptToMm = 25.4 / 72;
        foreach ($items as $item) {
            if (empty($item['enabled'])) continue;
            $key      = $item['key'] ?? '';
            $x        = (float) ($item['x']        ?? 0);
            $y        = (float) ($item['y']        ?? 0);
            $fontSize = (int)   ($item['fontSize']  ?? 15);
            $bold     = !empty($item['bold']);
            $text     = isset($values[$key]) ? trim((string) $values[$key]) : '';
            if ($text === '') continue;
            $pdf->SetFont('THSarabunNew', $bold ? 'B' : '', $fontSize);
            $pdf->SetXY($x, $y + $fontSize * $ptToMm * 0.45);
            $pdf->Write(0, iconv('UTF-8', 'cp874//IGNORE', $text));
        }

        $filename = 'repair-form-' . (int) $model->id . '.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $pdf->Output('S');
        return Yii::$app->response;
    }

    // ─────────────────────────────────────────────
    //  Helper: items list
    // ─────────────────────────────────────────────

    protected function getFormItems(RepairFormSetting $record): array
    {
        $config   = $record->getConfig();
        $defaults = RepairFormSetting::defaultFields();

        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key    = $item['key'] ?? '';
                $list[] = [
                    'id'       => $item['id']        ?? uniqid('item_'),
                    'key'      => $key,
                    'x'        => (float) ($item['x']        ?? 0),
                    'y'        => (float) ($item['y']        ?? 0),
                    'fontSize' => (int)   ($item['fontSize']  ?? 15),
                    'bold'     => !empty($item['bold']),
                    'enabled'  => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label'    => $defaults[$key]['label'] ?? $key,
                ];
            }
            return $list;
        }

        // ยังไม่มี items — ใช้ค่า default ทั้งหมด
        $list = [];
        foreach ($defaults as $key => $f) {
            $list[] = [
                'id'       => 'legacy_' . $key,
                'key'      => $key,
                'x'        => (float) ($f['x']        ?? 0),
                'y'        => (float) ($f['y']        ?? 0),
                'fontSize' => (int)   ($f['fontSize']  ?? 15),
                'bold'     => !empty($f['bold']),
                'enabled'  => isset($f['enabled']) ? (int) $f['enabled'] : 1,
                'label'    => $f['label'] ?? $key,
            ];
        }
        return $list;
    }

    // ─────────────────────────────────────────────
    //  Helper: Template file path
    // ─────────────────────────────────────────────

    protected function getTemplatePath(): string
    {
        return Yii::getAlias('@app') . '/' . HELPDESK2_TEMPLATE_STORE_DIR . '/template.pdf';
    }

    protected function hasTemplateFile(): bool
    {
        return is_file($this->getTemplatePath());
    }

    // ─────────────────────────────────────────────
    //  Helper: File upload validation
    // ─────────────────────────────────────────────

    protected function getUploadedPdfFile(): ?UploadedFile
    {
        $names = ['template_pdf', 'file', 'pdf_file', 'upload'];
        $postName = Yii::$app->request->post('name');
        if ($postName !== null && (string) $postName !== '') {
            array_unshift($names, $postName);
        }
        foreach (array_unique($names) as $name) {
            $file = UploadedFile::getInstanceByName($name);
            if ($file !== null) return $file;
        }
        if (!empty($_FILES)) {
            foreach (array_keys($_FILES) as $key) {
                $file = UploadedFile::getInstanceByName($key);
                if ($file !== null) return $file;
                $instances = UploadedFile::getInstancesByName($key);
                if (!empty($instances)) return $instances[0];
            }
        }
        return null;
    }

    protected function validatePdfFile($file): bool
    {
        if (!$file || !$file->tempName) return false;
        $path = $file->tempName;
        if (!file_exists($path) || !is_readable($path)) return false;
        $size = @filesize($path);
        if ($size === false || $size <= 0) return false;
        $head = @file_get_contents($path, false, null, 0, 8);
        if ($head !== false && strpos($head, '%PDF') === 0) return true;
        $ext = strtolower((string) ($file->extension ?? ''));
        if ($ext === 'pdf') return true;
        if ($file->name !== null) {
            if (strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) === 'pdf') return true;
        }
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($path);
            if ($mime && in_array($mime, ['application/pdf', 'application/x-pdf'], true)) return true;
        }
        return false;
    }
}
