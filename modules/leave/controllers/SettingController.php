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
use app\modules\leave\models\LeaveType;
use yii\helpers\Url;

const LEAVE_FORM_TEMPLATE_NAME  = 'leave_form_template';
// เก็บไฟล์ใน filemanager fileupload เพื่อ persist ผ่าน docker volume
const LEAVE_TEMPLATE_STORE_DIR  = 'modules/filemanager/fileupload/leave_templates';

/**
 * การตั้งค่า — แบบฟอร์มใบลา (อัปโหลด PDF + กำหนดตำแหน่ง)
 * รองรับ template กลาง (default) และ template เฉพาะแต่ละประเภทการลา
 */
class SettingController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        // serve PDF ไม่ต้องเช็ค permission (ใช้ใน iframe — เฉพาะ login แล้ว)
        if (in_array($action->id, ['leave-pdf', 'serve-template'])) {
            return true;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    // ─────────────────────────────────────────────
    //  หน้าจัดการ template (listing + per-type)
    // ─────────────────────────────────────────────

    /**
     * ถ้าไม่ส่ง $code → แสดง listing ประเภทการลาทั้งหมด
     * ถ้าส่ง $code  → แสดงหน้าจัดการ template + positions ของประเภทนั้น
     */
    public function actionLeaveTemplate($code = null)
    {
        if ($code === null) {
            // ── หน้า listing ──
            $leaveTypes = LeaveType::find()
                ->where(['name' => 'leave_type', 'active' => 1])
                ->orderBy(['code' => SORT_ASC])
                ->all();

            $templateStatus = [];
            foreach ($leaveTypes as $lt) {
                $templateStatus[$lt->code] = $this->hasTemplateFile($lt->code);
            }

            return $this->render('leave-template', [
                'leaveTypes'     => $leaveTypes,
                'templateStatus' => $templateStatus,
                'hasDefault'     => $this->hasTemplateFile('default'),
                'defaultUrl'     => $this->hasTemplateFile('default')
                    ? Url::to(['/leave/setting/serve-template', 'code' => 'default'])
                    : null,
            ]);
        }

        // ── หน้า per-type ──
        $hasTemplate = $this->hasTemplateFile($code);
        $leaveType   = $code !== 'default'
            ? LeaveType::findOne(['name' => 'leave_type', 'code' => $code])
            : null;

        return $this->render('leave-template-detail', [
            'code'        => $code,
            'leaveType'   => $leaveType,
            'hasTemplate' => $hasTemplate,
            'templateUrl' => $hasTemplate
                ? Url::to(['/leave/setting/serve-template', 'code' => $code])
                : null,
        ]);
    }

    /**
     * Serve PDF template ให้ iframe (ดึงไฟล์จาก filemanager storage)
     */
    public function actionServeTemplate($code = 'default')
    {
        $path = $this->getTemplatePath($code);
        if (!is_file($path)) {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์เทมเพลต');
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="template-' . $code . '.pdf"');
        Yii::$app->response->content = file_get_contents($path);
        return Yii::$app->response;
    }

    // ─────────────────────────────────────────────
    //  อัปโหลด template
    // ─────────────────────────────────────────────

    /**
     * อัปโหลด template PDF สำหรับ code ที่กำหนด
     * - AJAX → JSON
     * - form POST → redirect
     */
    public function actionUploadTemplate($code = 'default')
    {
        $isAjax = Yii::$app->request->getIsAjax()
            || Yii::$app->request->getHeaders()->get('X-Requested-With') === 'XMLHttpRequest';

        if (!Yii::$app->request->isPost) {
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
            }
            return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
        }

        $file = $this->getUploadedPdfFile();
        if ($file === null) {
            $err = 'กรุณาเลือกไฟล์ PDF';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
        }

        if (!$this->validatePdfFile($file)) {
            $err = 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
        }

        $path = $this->getTemplatePath($code);
        FileHelper::createDirectory(dirname($path));

        if (!$file->saveAs($path)) {
            $err = 'บันทึกไฟล์ไม่สำเร็จ';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
        }

        $this->ensureConfigRecord($code);

        if ($isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'อัปโหลดเทมเพลต PDF เรียบร้อย'];
        }
        Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF เรียบร้อย');
        return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
    }

    /**
     * ลบ template เฉพาะประเภท (กลับไปใช้ default)
     * ไม่อนุญาตลบ default
     */
    public function actionDeleteTemplate($code)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($code === 'default') {
            return ['success' => false, 'error' => 'ไม่สามารถลบ template กลางได้'];
        }
        $path = $this->getTemplatePath($code);
        if (is_file($path)) {
            @unlink($path);
        }
        return ['success' => true, 'message' => 'ลบเรียบร้อย — จะใช้ template กลางแทน'];
    }

    // ─────────────────────────────────────────────
    //  กำหนดตำแหน่งข้อมูลบน PDF
    // ─────────────────────────────────────────────

    /**
     * หน้ากำหนดตำแหน่ง — รองรับ per-type ด้วย $code
     */
    public function actionPositions($code = 'default')
    {
        // ถ้าไม่มี template เฉพาะ ใช้ default
        $effectiveCode = $this->hasTemplateFile($code) ? $code : 'default';

        if (!$this->hasTemplateFile($effectiveCode)) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['/leave/setting/leave-template', 'code' => $code]);
        }

        $config      = $this->getLeaveFormConfig($code);
        $items       = $this->getLeaveFormItems($code);
        $fieldLabels = $this->getDefaultFields();

        $leaveType = $code !== 'default'
            ? LeaveType::findOne(['name' => 'leave_type', 'code' => $code])
            : null;

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
            'code'        => $code,
            'leaveType'   => $leaveType,
            'config'      => $config,
            'items'       => $items,
            'fieldLabels' => $fieldLabels,
            'templateUrl' => Url::to(['/leave/setting/serve-template', 'code' => $effectiveCode]),
            'recentLeaves'=> $recentLeaves,
            'usingDefault'=> ($effectiveCode === 'default' && $code !== 'default'),
        ]);
    }

    /**
     * บันทึกตำแหน่ง (AJAX) — per-type
     */
    public function actionSavePositions($code = 'default')
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
        $config   = $this->getLeaveFormConfig($code);
        $items    = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) continue;
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) continue;
            $items[] = [
                'id'       => $itemId,
                'key'      => $key,
                'x'        => (float) ($pos['x'] ?? 0),
                'y'        => (float) ($pos['y'] ?? 0),
                'fontSize' => (int) ($pos['fontSize'] ?? 15),
                'bold'     => (int) ($pos['bold'] ?? 0),
                'enabled'  => (int) ($pos['enabled'] ?? 1),
            ];
        }
        $config['items'] = $items;
        $cat = $this->getConfigRecord($code);
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    // ─────────────────────────────────────────────
    //  สร้าง PDF ใบลา
    // ─────────────────────────────────────────────

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

        // ── Fallback: หา template + positions ที่เหมาะกับประเภทการลานี้ ──
        $tmpl = $this->getTemplateForLeave($model);
        if ($tmpl === null) {
            throw new \yii\web\NotFoundHttpException(
                'ยังไม่มีเทมเพลต PDF กรุณาอัปโหลดที่การตั้งค่าแบบฟอร์มใบลา'
            );
        }

        $author = $model->getAvatar($model->emp_id, '');
        $values = [
            'emp_fullname'     => $author['fullname'] ?? ($model->employee->fullname ?? ''),
            'department'       => $author['department'] ?? ($model->employee ? $model->employee->departmentName() : ''),
            'leave_type_title' => $model->leaveType ? $model->leaveType->title : '',
            'date_start'       => $model->date_start ? ThaiDateHelper::formatThaiDate($model->date_start) : '',
            'date_end'         => $model->date_end   ? ThaiDateHelper::formatThaiDate($model->date_end)   : '',
            'total_days'       => (string) ($model->total_days ?? ''),
            'reason'           => $model->data_json['reason'] ?? '',
            'address'          => $model->data_json['address'] ?? '',
            'contact_phone'    => $model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '',
            'place_go'         => $model->data_json['place_go'] ?? '',
            'create_date'      => $model->created_at ? ThaiDateHelper::formatThaiDate($model->created_at, 'long') : '',
        ];

        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            throw new \yii\web\ServerErrorHttpException('ระบบสร้าง PDF ยังไม่พร้อม');
        }

        define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));
        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($tmpl['templatePath']);
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        $pdf->AddFont('THSarabunNew', '',  'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
        $pdf->SetTextColor(0, 0, 0);

        $ptToMm = 25.4 / 72;
        foreach ($tmpl['items'] as $item) {
            if (empty($item['enabled'])) continue;
            $key      = $item['key'] ?? '';
            $x        = (float) ($item['x'] ?? 0);
            $y        = (float) ($item['y'] ?? 0);
            $fontSize = (int) ($item['fontSize'] ?? 15);
            $bold     = !empty($item['bold']);
            $text     = isset($values[$key]) ? trim((string) $values[$key]) : '';
            if ($text === '') continue;
            $pdf->SetFont('THSarabunNew', $bold ? 'B' : '', $fontSize);
            $pdf->SetXY($x, $y + $fontSize * $ptToMm * 0.45);
            $pdf->Write(0, iconv('UTF-8', 'cp874//IGNORE', $text));
        }

        $filename = 'leave-' . (int) $model->id . '.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $pdf->Output('S');
        return Yii::$app->response;
    }

    // ─────────────────────────────────────────────
    //  ตั้งค่าผู้อนุมัติ
    // ─────────────────────────────────────────────

    public function actionApprovers()
    {
        return $this->render('approvers');
    }

    // ─────────────────────────────────────────────
    //  Helper: Template file & URL
    // ─────────────────────────────────────────────

    /**
     * Path จริงในระบบไฟล์สำหรับ template PDF
     * เก็บใน modules/filemanager/fileupload/leave_templates/{code}/template.pdf
     * ซึ่ง persist ผ่าน Docker volume ./:/app
     */
    protected function getTemplatePath(string $code = 'default'): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '', $code) ?: 'default';
        return Yii::getAlias('@app') . '/' . LEAVE_TEMPLATE_STORE_DIR . '/' . $safe . '/template.pdf';
    }

    protected function hasTemplateFile(string $code = 'default'): bool
    {
        return is_file($this->getTemplatePath($code));
    }

    /**
     * fallback: หา template ที่เหมาะกับประเภทการลา
     * ลำดับ: per-type → default → null
     */
    protected function getTemplateForLeave(Leave $model): ?array
    {
        $ltCode = $model->leaveType->code ?? null;

        // ลอง per-type ก่อน
        if ($ltCode && $this->hasTemplateFile($ltCode)) {
            return [
                'templatePath' => $this->getTemplatePath($ltCode),
                'items'        => $this->getLeaveFormItems($ltCode),
            ];
        }
        // fallback → default
        if ($this->hasTemplateFile('default')) {
            return [
                'templatePath' => $this->getTemplatePath('default'),
                'items'        => $this->getLeaveFormItems('default'),
            ];
        }
        return null;
    }

    // ─────────────────────────────────────────────
    //  Helper: Config (Categorise)
    // ─────────────────────────────────────────────

    /**
     * ดึง config สำหรับ code ที่กำหนด
     * Categorise: name='leave_form_template', code='{code}'
     */
    protected function getConfigRecord(string $code = 'default'): Categorise
    {
        $cat = Categorise::findOne(['name' => LEAVE_FORM_TEMPLATE_NAME, 'code' => $code]);
        if (!$cat) {
            $defaults = $this->getDefaultFields();
            $items = [];
            foreach ($defaults as $key => $def) {
                $items[] = [
                    'id'       => 'legacy_' . $key,
                    'key'      => $key,
                    'x'        => (float) ($def['x']        ?? 0),
                    'y'        => (float) ($def['y']        ?? 0),
                    'fontSize' => (int)   ($def['fontSize'] ?? 15),
                    'bold'     => (int)   ($def['bold']     ?? 0),
                    'enabled'  => (int)   ($def['enabled']  ?? 1),
                ];
            }
            $cat = new Categorise();
            $cat->name      = LEAVE_FORM_TEMPLATE_NAME;
            $cat->code      = $code;
            $cat->title     = $code === 'default' ? 'ฟอร์มใบลา (default)' : 'ฟอร์มใบลา (' . $code . ')';
            $cat->data_json = json_encode(['items' => $items]);
            $cat->save(false);
        }
        return $cat;
    }

    protected function ensureConfigRecord(string $code = 'default'): void
    {
        $this->getConfigRecord($code);
    }

    protected function getLeaveFormConfig(string $code = 'default'): array
    {
        $cat  = $this->getConfigRecord($code);
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

    protected function getLeaveFormItems(string $code = 'default'): array
    {
        $config   = $this->getLeaveFormConfig($code);
        $defaults = $this->getDefaultFields();

        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key    = $item['key'] ?? '';
                $list[] = [
                    'id'       => $item['id']       ?? uniqid('item_'),
                    'key'      => $key,
                    'x'        => (float) ($item['x']        ?? 0),
                    'y'        => (float) ($item['y']        ?? 0),
                    'fontSize' => (int)   ($item['fontSize'] ?? 15),
                    'bold'     => !empty($item['bold']),
                    'enabled'  => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label'    => $defaults[$key]['label'] ?? $key,
                ];
            }
            return $list;
        }

        $fields = $config['fields'] ?? $defaults;
        $items  = [];
        foreach ($fields as $key => $f) {
            $items[] = [
                'id'       => 'legacy_' . $key,
                'key'      => $key,
                'x'        => (float) ($f['x']        ?? 0),
                'y'        => (float) ($f['y']        ?? 0),
                'fontSize' => (int)   ($f['fontSize'] ?? 15),
                'bold'     => !empty($f['bold']),
                'enabled'  => isset($f['enabled']) ? (int) $f['enabled'] : 1,
                'label'    => $defaults[$key]['label'] ?? $key,
            ];
        }
        return $items;
    }

    // ─────────────────────────────────────────────
    //  Helper: File upload
    // ─────────────────────────────────────────────

    protected function getUploadedPdfFile(): ?UploadedFile
    {
        $postName = Yii::$app->request->post('name');
        $names = ['template_pdf', 'file', 'pdf_file', 'upload'];
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
        // magic bytes %PDF — ถ้าผ่านก็พอ
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

    // ─────────────────────────────────────────────
    //  ค่าเริ่มต้นฟิลด์
    // ─────────────────────────────────────────────

    protected function getDefaultFields(): array
    {
        return [
            'emp_fullname'     => ['label' => 'ชื่อ-นามสกุลผู้ขอลา',  'x' => 30,  'y' => 50,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'department'       => ['label' => 'หน่วยงาน/แผนก',         'x' => 30,  'y' => 58,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'leave_type_title' => ['label' => 'ประเภทการลา',            'x' => 30,  'y' => 66,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_start'       => ['label' => 'วันที่เริ่มลา',          'x' => 30,  'y' => 74,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_end'         => ['label' => 'วันที่สิ้นสุด',          'x' => 80,  'y' => 74,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'total_days'       => ['label' => 'จำนวนวัน',               'x' => 30,  'y' => 82,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'reason'           => ['label' => 'เหตุผลการลา',            'x' => 30,  'y' => 90,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'address'          => ['label' => 'ที่อยู่ที่ติดต่อได้',    'x' => 30,  'y' => 98,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'contact_phone'    => ['label' => 'เบอร์โทรติดต่อ',         'x' => 30,  'y' => 106, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'place_go'         => ['label' => 'สถานที่ไป',              'x' => 30,  'y' => 114, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'create_date'      => ['label' => 'วันที่ยื่นคำขอ',         'x' => 30,  'y' => 122, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
        ];
    }
}

