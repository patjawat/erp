<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use app\models\Categorise;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use app\modules\leave\models\Leave;

const LEAVE_FORM_TEMPLATE_NAME = 'leave_form_template';
const LEAVE_TEMPLATE_RELATIVE_PATH = 'uploads/leave_form_template/template.pdf';

/**
 * การตั้งค่า — แบบฟอร์มใบลา (อัปโหลด PDF + กำหนดตำแหน่ง)
 */
class SettingController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if ($action->id === 'leave-pdf') {
            return true;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    /**
     * ตั้งค่าผู้อนุมัติใบลา — หน้าสรุปและลิงก์ไปจัดการระดับการอนุมัติ (approveV3)
     */
    public function actionApprovers()
    {
        return $this->render('approvers');
    }

    /**
     * ตั้งค่าฟอร์มใบลา — อัปโหลดเทมเพลต PDF และกำหนดตำแหน่งข้อมูล
     */
    public function actionLeaveTemplate()
    {
        $config = $this->getLeaveFormConfig();
        $hasTemplate = $this->hasTemplateFile();

        return $this->render('leave-template', [
            'config' => $config,
            'hasTemplate' => $hasTemplate,
            'templateUrl' => $hasTemplate ? Yii::getAlias('@web') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH . '?t=' . time() : null,
        ]);
    }

    /**
     * อัปโหลดเทมเพลต PDF
     * - ถ้าเป็น AJAX หรือ Accept: application/json จะคืน JSON (success/error)
     * - ถ้าโพสต์ธรรมดาจะ redirect กลับหน้าแบบฟอร์มใบลา
     */
    public function actionUploadTemplate()
    {
        $isAjax = Yii::$app->request->getIsAjax()
            || strpos(Yii::$app->request->getHeaders()->get('Accept', ''), 'application/json') !== false
            || Yii::$app->request->getHeaders()->get('X-Requested-With') === 'XMLHttpRequest';

        if (!Yii::$app->request->isPost) {
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
            }
            return $this->redirect(['leave-template']);
        }

        $file = $this->getUploadedPdfFile();
        if ($file === null) {
            $errMsg = 'กรุณาเลือกไฟล์ PDF';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $errMsg];
            }
            Yii::$app->session->setFlash('error', $errMsg);
            return $this->redirect(['leave-template']);
        }
        if (!$this->validatePdfFile($file)) {
            $errMsg = 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $errMsg];
            }
            Yii::$app->session->setFlash('error', $errMsg);
            return $this->redirect(['leave-template']);
        }

        $dir = Yii::getAlias('@webroot') . '/uploads/leave_form_template';
        FileHelper::createDirectory($dir);
        $path = $dir . '/template.pdf';
        if (!$file->saveAs($path)) {
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => 'บันทึกไฟล์ไม่สำเร็จ'];
            }
            Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
            return $this->redirect(['leave-template']);
        }

        $this->ensureConfigRecord();
        if ($isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'อัปโหลดเทมเพลต PDF เรียบร้อย'];
        }
        Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF เรียบร้อย');
        return $this->redirect(['leave-template']);
    }

    /**
     * อัปโหลดเทมเพลต PDF (AJAX) — alias ที่คืน JSON เสมอ
     */
    public function actionUploadTemplateAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
        }
        $file = $this->getUploadedPdfFile();
        if ($file === null) {
            return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
        }
        if (!$this->validatePdfFile($file)) {
            return ['success' => false, 'error' => 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น'];
        }
        $dir = Yii::getAlias('@webroot') . '/uploads/leave_form_template';
        FileHelper::createDirectory($dir);
        $path = $dir . '/template.pdf';
        if (!$file->saveAs($path)) {
            return ['success' => false, 'error' => 'บันทึกไฟล์ไม่สำเร็จ'];
        }
        $this->ensureConfigRecord();
        return ['success' => true, 'message' => 'อัปโหลดเทมเพลต PDF เรียบร้อย'];
    }

    /**
     * ดึงไฟล์ที่อัปโหลด — ลองหลายชื่อ input และ fallback จาก $_FILES
     */
    protected function getUploadedPdfFile()
    {
        $postName = Yii::$app->request->post('name');
        $names = ['template_pdf', 'file', 'pdf_file', 'upload', 'upload_ajax', 'template_pdf[]', 'upload_ajax[]'];
        if ($postName !== null && (string) $postName !== '') {
            array_unshift($names, $postName);
        }
        foreach (array_unique($names) as $name) {
            $file = UploadedFile::getInstanceByName($name);
            if ($file !== null) {
                return $file;
            }
        }
        if (!empty($_FILES)) {
            foreach (array_keys($_FILES) as $key) {
                $file = UploadedFile::getInstanceByName($key);
                if ($file !== null) {
                    return $file;
                }
                $instances = UploadedFile::getInstancesByName($key);
                if (!empty($instances)) {
                    return $instances[0];
                }
            }
        }
        return null;
    }

    /**
     * ตรวจว่าเป็นไฟล์ PDF (รองรับนามสกุล .pdf, MIME type, magic bytes %PDF)
     * ผ่อนปรน is_uploaded_file — บางสภาพแวดล้อม (proxy/FastCGI) อาจไม่ผ่าน
     */
    protected function validatePdfFile($file)
    {
        if (!$file || !$file->tempName) {
            return false;
        }
        $path = $file->tempName;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        $size = @filesize($path);
        if ($size === false || $size <= 0) {
            return false;
        }
        // ตรวจ magic bytes ก่อน — ถ้าเป็น PDF จริงให้ผ่าน
        $head = @file_get_contents($path, false, null, 0, 8);
        if ($head !== false && strpos($head, '%PDF') === 0) {
            return true;
        }
        $ext = strtolower((string) ($file->extension ?? ''));
        if ($ext === 'pdf') {
            return true;
        }
        if ($file->name !== null && $file->name !== '') {
            $nameExt = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
            if ($nameExt === 'pdf') {
                return true;
            }
        }
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($path);
            if ($mime && in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
                return true;
            }
        }
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = @finfo_file($finfo, $path);
            finfo_close($finfo);
            if ($mime && in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * สร้าง PDF ใบลาจากเทมเพลตที่อัปโหลด + ตำแหน่งที่กำหนด (ให้เจ้าของใบลาหรือผู้มีสิทธิ์ leave เรียกได้)
     */
    public function actionLeavePdf($id)
    {
        $model = Leave::find()
            ->andWhere(['id' => (int) $id])
            ->with(['employee', 'leaveType'])
            ->one();
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงาน');
        }
        if ($me->id != $model->emp_id && !Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์พิมพ์ใบลานี้');
        }
        if (!$this->hasTemplateFile()) {
            throw new \yii\web\NotFoundHttpException('ยังไม่มีเทมเพลต PDF กรุณาอัปโหลดที่การตั้งค่าแบบฟอร์มใบลา');
        }

        $templatePath = Yii::getAlias('@webroot') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH;
        $config = $this->getLeaveFormConfig();
        $items = $this->getLeaveFormItems();

        $author = $model->getAvatar($model->emp_id, '');
        $values = [
            'emp_fullname' => $author['fullname'] ?? ($model->employee->fullname ?? ''),
            'department' => $author['department'] ?? ($model->employee ? $model->employee->departmentName() : ''),
            'leave_type_title' => $model->leaveType ? $model->leaveType->title : '',
            'date_start' => $model->date_start ? ThaiDateHelper::formatThaiDate($model->date_start) : '',
            'date_end' => $model->date_end ? ThaiDateHelper::formatThaiDate($model->date_end) : '',
            'total_days' => (string) ($model->total_days ?? ''),
            'reason' => $model->data_json['reason'] ?? '',
            'address' => $model->data_json['address'] ?? '',
            'contact_phone' => $model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '',
            'place_go' => $model->data_json['place_go'] ?? '',
            'create_date' => $model->created_at ? ThaiDateHelper::formatThaiDate($model->created_at, 'long') : '',
        ];

        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            throw new \yii\web\ServerErrorHttpException('ระบบสร้าง PDF ยังไม่พร้อม');
        }

        define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));
        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
        $pdf->SetTextColor(0, 0, 0);

        // ใน FPDF ค่า Y ของ SetXY คือ baseline — เลื่อน Y ลงเล็กน้อยเพื่อให้ระดับข้อความใกล้เคียงตำแหน่งที่กำหนด
        $ptToMm = 25.4 / 72;
        foreach ($items as $item) {
            if (empty($item['enabled'])) {
                continue;
            }
            $key = $item['key'] ?? '';
            $x = (float) ($item['x'] ?? 0);
            $y = (float) ($item['y'] ?? 0);
            $fontSize = (int) ($item['fontSize'] ?? 15);
            $bold = !empty($item['bold']);
            $text = isset($values[$key]) ? trim((string) $values[$key]) : '';
            if ($text === '') {
                continue;
            }
            $style = $bold ? 'B' : '';
            $pdf->SetFont('THSarabunNew', $style, $fontSize);
            $yBaseline = $y + $fontSize * $ptToMm * 0.45;
            $pdf->SetXY($x, $yBaseline);
            $pdf->Write(0, iconv('UTF-8', 'cp874//IGNORE', $text));
        }

        $filename = 'leave-' . (int) $model->id . '.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $pdf->Output('S');
        return Yii::$app->response;
    }

    /**
     * หน้ากำหนดตำแหน่งข้อมูลบน PDF
     */
    public function actionPositions()
    {
        if (!$this->hasTemplateFile()) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['leave-template']);
        }
        $config = $this->getLeaveFormConfig();
        $items = $this->getLeaveFormItems();
        $fieldLabels = $this->getDefaultFields();

        $me = UserHelper::GetEmployee();
        $recentLeaves = [];
        if ($me) {
            $recentLeaves = Leave::find()
                ->where(['emp_id' => $me->id])
                ->orderBy(['id' => SORT_DESC])
                ->limit(5)
                ->with(['leaveType'])
                ->all();
        }

        return $this->render('positions', [
            'config' => $config,
            'items' => $items,
            'fieldLabels' => $fieldLabels,
            'templateUrl' => Yii::getAlias('@web') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH . '?t=' . time(),
            'recentLeaves' => $recentLeaves,
        ]);
    }

    /**
     * บันทึกตำแหน่ง (AJAX) — รองรับ items (id => { key, x, y, fontSize, bold, enabled })
     */
    public function actionSavePositions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $positions = Yii::$app->request->post('positions', []);
        if (empty($positions) && Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw) {
                $body = json_decode($raw, true);
                $positions = $body['positions'] ?? [];
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $defaults = $this->getDefaultFields();
        $config = $this->getLeaveFormConfig();
        $items = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) {
                continue;
            }
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) {
                continue;
            }
            $items[] = [
                'id' => $itemId,
                'key' => $key,
                'x' => (float) ($pos['x'] ?? 0),
                'y' => (float) ($pos['y'] ?? 0),
                'fontSize' => (int) ($pos['fontSize'] ?? 15),
                'bold' => (int) ($pos['bold'] ?? 0),
                'enabled' => (int) ($pos['enabled'] ?? 1),
            ];
        }
        $config['items'] = $items;
        $cat = $this->getConfigRecord();
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    protected function hasTemplateFile()
    {
        $path = Yii::getAlias('@webroot') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH;
        return is_file($path);
    }

    protected function getLeaveFormConfig()
    {
        $cat = $this->getConfigRecord();
        $json = $cat->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $json = is_array($json) ? $json : [];
        if (empty($json['items']) && empty($json['fields'])) {
            $json['fields'] = $this->getDefaultFields();
        }
        return $json;
    }

    /**
     * แปลง config แบบเก่า (fields key=>data) เป็น items (รายการตำแหน่ง — ฟิลด์เดียวกันวางหลายที่ได้)
     */
    protected function fieldsToItems($fields)
    {
        $defaults = $this->getDefaultFields();
        $items = [];
        foreach ($fields as $key => $f) {
            $items[] = [
                'id' => 'legacy_' . $key,
                'key' => $key,
                'x' => (float) ($f['x'] ?? 0),
                'y' => (float) ($f['y'] ?? 0),
                'fontSize' => (int) ($f['fontSize'] ?? 15),
                'bold' => (int) ($f['bold'] ?? 0),
                'enabled' => isset($f['enabled']) ? (int) $f['enabled'] : 1,
            ];
        }
        return $items;
    }

    /**
     * รายการตำแหน่งสำหรับแสดง/แก้ไข — รองรับทั้ง config['items'] และ config['fields'] (แปลงเป็น items)
     */
    protected function getLeaveFormItems()
    {
        $config = $this->getLeaveFormConfig();
        $defaults = $this->getDefaultFields();
        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key = $item['key'] ?? '';
                $list[] = [
                    'id' => $item['id'] ?? uniqid('item_'),
                    'key' => $key,
                    'x' => (float) ($item['x'] ?? 0),
                    'y' => (float) ($item['y'] ?? 0),
                    'fontSize' => (int) ($item['fontSize'] ?? 15),
                    'bold' => !empty($item['bold']),
                    'enabled' => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label' => $defaults[$key]['label'] ?? $key,
                ];
            }
            return $list;
        }
        $fields = $config['fields'] ?? $defaults;
        $items = [];
        foreach ($fields as $key => $f) {
            $items[] = [
                'id' => 'legacy_' . $key,
                'key' => $key,
                'x' => (float) ($f['x'] ?? 0),
                'y' => (float) ($f['y'] ?? 0),
                'fontSize' => (int) ($f['fontSize'] ?? 15),
                'bold' => !empty($f['bold']),
                'enabled' => isset($f['enabled']) ? (int) $f['enabled'] : 1,
                'label' => $defaults[$key]['label'] ?? $key,
            ];
        }
        return $items;
    }

    protected function getConfigRecord()
    {
        $cat = Categorise::findOne(['name' => LEAVE_FORM_TEMPLATE_NAME]);
        if (!$cat) {
            $defaults = $this->getDefaultFields();
            $items = [];
            foreach ($defaults as $key => $def) {
                $items[] = [
                    'id' => 'legacy_' . $key,
                    'key' => $key,
                    'x' => (float) ($def['x'] ?? 0),
                    'y' => (float) ($def['y'] ?? 0),
                    'fontSize' => (int) ($def['fontSize'] ?? 15),
                    'bold' => (int) ($def['bold'] ?? 0),
                    'enabled' => (int) ($def['enabled'] ?? 1),
                ];
            }
            $cat = new Categorise();
            $cat->name = LEAVE_FORM_TEMPLATE_NAME;
            $cat->code = 'default';
            $cat->title = 'ฟอร์มใบลา';
            $cat->data_json = json_encode(['items' => $items]);
            $cat->save(false);
        }
        return $cat;
    }

    protected function ensureConfigRecord()
    {
        $this->getConfigRecord();
    }

    protected function getDefaultFields()
    {
        return [
            'emp_fullname' => ['label' => 'ชื่อ-นามสกุลผู้ขอลา', 'x' => 30, 'y' => 50, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'department' => ['label' => 'หน่วยงาน/แผนก', 'x' => 30, 'y' => 58, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'leave_type_title' => ['label' => 'ประเภทการลา', 'x' => 30, 'y' => 66, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_start' => ['label' => 'วันที่เริ่มลา', 'x' => 30, 'y' => 74, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_end' => ['label' => 'วันที่สิ้นสุด', 'x' => 80, 'y' => 74, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'total_days' => ['label' => 'จำนวนวัน', 'x' => 30, 'y' => 82, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'reason' => ['label' => 'เหตุผลการลา', 'x' => 30, 'y' => 90, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'address' => ['label' => 'ที่อยู่ที่ติดต่อได้', 'x' => 30, 'y' => 98, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'contact_phone' => ['label' => 'เบอร์โทรติดต่อ', 'x' => 30, 'y' => 106, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'place_go' => ['label' => 'สถานที่ไป', 'x' => 30, 'y' => 114, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'create_date' => ['label' => 'วันที่ยื่นคำขอ', 'x' => 30, 'y' => 122, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
        ];
    }
}
