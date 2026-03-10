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

        // โหลดและบันทึกตาม effectiveCode เพื่อให้ date_format และตำแหน่งตรงกับ template ที่ใช้พิมพ์ PDF
        $config      = $this->getLeaveFormConfig($effectiveCode);
        $items       = $this->getLeaveFormItems($effectiveCode);
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
            'code'          => $code,
            'effectiveCode' => $effectiveCode,
            'leaveType'     => $leaveType,
            'config'        => $config,
            'items'         => $items,
            'fieldLabels'   => $fieldLabels,
            'signatureKeys' => $this->getSignatureKeys(),
            'templateUrl'   => Url::to(['/leave/setting/serve-template', 'code' => $effectiveCode]),
            'recentLeaves'  => $recentLeaves,
            'usingDefault'  => ($effectiveCode === 'default' && $code !== 'default'),
        ]);
    }

    /**
     * บันทึกตำแหน่ง (AJAX) — per-type
     */
    public function actionSavePositions($code = 'default')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $positions  = Yii::$app->request->post('positions', []);
        $dateFormat = Yii::$app->request->post('date_format');
        if (Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw && is_string($raw) && preg_match('/^\s*\{/', $raw)) {
                $body = json_decode($raw, true);
                if (is_array($body)) {
                    if (!empty($body['positions'])) {
                        $positions = $body['positions'];
                    }
                    if (isset($body['date_format']) && $body['date_format'] !== '') {
                        $dateFormat = (string) $body['date_format'];
                    }
                }
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $defaults = $this->getDefaultFields();
        $sigKeys  = $this->getSignatureKeys();
        $config   = $this->getLeaveFormConfig($code);
        if ($dateFormat !== null && in_array($dateFormat, ['short', 'medium', 'long', 'numeric'], true)) {
            $config['date_format'] = $dateFormat;
        }
        $items = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) continue;
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) continue;
            $row = [
                'id'       => $itemId,
                'key'      => $key,
                'x'        => (float) ($pos['x'] ?? 0),
                'y'        => (float) ($pos['y'] ?? 0),
                'fontSize' => (int) ($pos['fontSize'] ?? 15),
                'bold'     => (int) ($pos['bold'] ?? 0),
                'enabled'  => (int) ($pos['enabled'] ?? 1),
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width']  = (float) ($pos['width'] ?? $defaults[$key]['width'] ?? 35);
                $row['height'] = (float) ($pos['height'] ?? $defaults[$key]['height'] ?? 15);
            }
            $items[] = $row;
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
        // กันไม่ให้ FPDF หรือ code อื่นส่ง output ก่อน headers (แก้ HeadersAlreadySentException)
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $model = Leave::find()
            ->andWhere(['id' => (int) $id])
            ->with(['employee', 'leaveType'])
            ->one();
        if ($model === null) {
            ob_end_clean();
            throw new \yii\web\NotFoundHttpException('ไม่พบรายการที่ต้องการ');
        }
        $me = UserHelper::GetEmployee();
        if (!$me) {
            ob_end_clean();
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงาน');
        }
        // พิมพ์ได้ทุกสถานะ (ไม่ต้องรอ ผอ. อนุมัติ) — ตรวจเฉพาะว่าเป็นเจ้าของหรือมีสิทธิ์ leave
        if ($me->id != $model->emp_id && !Yii::$app->user->can('leave')) {
            ob_end_clean();
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์พิมพ์ใบลานี้');
        }

        // ── Fallback: หา template + positions ที่เหมาะกับประเภทการลานี้ ──
        $tmpl = $this->getTemplateForLeave($model);
        if ($tmpl === null) {
            ob_end_clean();
            throw new \yii\web\NotFoundHttpException(
                'ยังไม่มีเทมเพลต PDF กรุณาอัปโหลดที่การตั้งค่าแบบฟอร์มใบลา'
            );
        }

        $dateFmt = $tmpl['date_format'] ?? 'medium';
        $fmtDate = function ($date) use ($dateFmt) {
            return $this->formatDateForLeavePdf($date, $dateFmt);
        };

        $author = $model->getAvatar($model->emp_id, '');
        $values = [
            'emp_fullname'     => $author['fullname'] ?? ($model->employee->fullname ?? ''),
            'department'       => $author['department'] ?? ($model->employee ? $model->employee->departmentName() : ''),
            'emp_position'     => $author['position_name'] ?? ($model->employee ? $model->employee->positionName() : ''),
            'leave_type_title' => $model->leaveType ? $model->leaveType->title : '',
            'date_start'       => $model->date_start ? $fmtDate($model->date_start) : '',
            'date_end'         => $model->date_end   ? $fmtDate($model->date_end)   : '',
            'total_days'       => (string) ($model->total_days ?? ''),
            'reason'           => $model->data_json['reason'] ?? '',
            'address'          => $model->data_json['address'] ?? '',
            'contact_phone'    => $model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '',
            'place_go'         => $model->data_json['place_go'] ?? '',
            'create_date'      => $model->created_at ? $fmtDate($model->created_at) : '',
        ];
        for ($level = 1; $level <= 8; $level++) {
            $checker = $model->checkerName($level);
            $approveDateRaw = $model->getApproveDateRaw($level);
            $values['approve_date_' . $level]     = $approveDateRaw ? $fmtDate($approveDateRaw) : ($checker['approve_date'] ?? '');
            $values['approve_' . $level . '_name'] = $checker['fullname'] ?? '';
            $values['approve_' . $level . '_position'] = $checker['position'] ?? '';
        }
        $statsRows = $model->getLeaveStatsInFiscalYear();
        $usedBefore = 0;
        $totalDays = 0;
        if (!empty($statsRows)) {
            $row = $statsRows[0];
            $usedBefore = (float) ($row['last_days'] ?? 0);
            $totalDays = (float) ($row['total_days'] ?? 0);
        }
        $values['leave_stats_used_before'] = (string) $usedBefore;
        $values['leave_stats_total'] = (string) $totalDays;
        $lastLeave = $model->getLastLeaveBeforeThis();
        $values['last_leave_date_start'] = $lastLeave && $lastLeave->date_start ? $fmtDate($lastLeave->date_start) : '';
        $values['last_leave_date_end'] = $lastLeave && $lastLeave->date_end ? $fmtDate($lastLeave->date_end) : '';

        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            ob_end_clean();
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
        $sigKeys = $this->getSignatureKeys();
        foreach ($tmpl['items'] as $item) {
            if (empty($item['enabled'])) continue;
            $key = $item['key'] ?? '';
            if (in_array($key, $sigKeys, true)) continue;
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

        foreach ($tmpl['items'] as $item) {
            if (empty($item['enabled'])) continue;
            $key = $item['key'] ?? '';
            if (!in_array($key, $sigKeys, true)) continue;
            $x      = (float) ($item['x'] ?? 0);
            $y      = (float) ($item['y'] ?? 0);
            $width  = (float) ($item['width'] ?? 35);
            $height = (float) ($item['height'] ?? 15);
            $imgPath = null;
            if ($key === 'signature_applicant') {
                $sigData = isset($model->data_json['signature_data']) ? trim((string) $model->data_json['signature_data']) : '';
                if ($sigData !== '' && preg_match('#^data:image/(\w+);base64,(.+)$#', $sigData, $m)) {
                    $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
                    $bin = base64_decode($m[2], true);
                    if ($bin !== false) {
                        $tmp = tempnam(sys_get_temp_dir(), 'leave_sig_') . '.' . $ext;
                        if (@file_put_contents($tmp, $bin)) {
                            $imgPath = $tmp;
                        }
                    }
                }
            } else {
                $level = (int) str_replace('signature_approve_', '', $key);
                if ($level >= 1) {
                    $checker = $model->checkerName($level);
                    $sigPath = isset($checker['signature']) ? $checker['signature'] : '';
                    if ($sigPath !== '' && is_file($sigPath)) {
                        $imgPath = $sigPath;
                    }
                }
            }
            if ($imgPath !== null) {
                try {
                    $pdf->Image($imgPath, $x, $y, $width, $height);
                } catch (\Throwable $e) {
                }
                if (strpos($imgPath, sys_get_temp_dir()) === 0) {
                    @unlink($imgPath);
                }
            }
        }

        $filename = 'leave-' . (int) $model->id . '.pdf';
        $pdfOutput = $pdf->Output('S');
        $buffered = ob_get_clean();
        if ($buffered !== false && $buffered !== '' && substr($buffered, 0, 4) === '%PDF') {
            $body = $buffered;
        } else {
            $body = $pdfOutput;
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $body;
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
            $config = $this->getLeaveFormConfig($ltCode);
            return [
                'templatePath' => $this->getTemplatePath($ltCode),
                'items'        => $this->getLeaveFormItems($ltCode),
                'date_format'  => $config['date_format'] ?? 'medium',
            ];
        }
        // fallback → default
        if ($this->hasTemplateFile('default')) {
            $config = $this->getLeaveFormConfig('default');
            return [
                'templatePath' => $this->getTemplatePath('default'),
                'items'        => $this->getLeaveFormItems('default'),
                'date_format'  => $config['date_format'] ?? 'medium',
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
            $sigKeys  = $this->getSignatureKeys();
            $items = [];
            foreach ($defaults as $key => $def) {
                $row = [
                    'id'       => 'legacy_' . $key,
                    'key'      => $key,
                    'x'        => (float) ($def['x']        ?? 0),
                    'y'        => (float) ($def['y']        ?? 0),
                    'fontSize' => (int)   ($def['fontSize'] ?? 15),
                    'bold'     => (int)   ($def['bold']     ?? 0),
                    'enabled'  => (int)   ($def['enabled']  ?? 1),
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width']  = (float) ($def['width']  ?? 35);
                    $row['height'] = (float) ($def['height'] ?? 15);
                }
                $items[] = $row;
            }
            $cat = new Categorise();
            $cat->name      = LEAVE_FORM_TEMPLATE_NAME;
            $cat->code      = $code;
            $cat->title     = $code === 'default' ? 'ฟอร์มใบลา (default)' : 'ฟอร์มใบลา (' . $code . ')';
            $cat->data_json = json_encode(['items' => $items, 'date_format' => 'medium']);
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

        $sigKeys = $this->getSignatureKeys();
        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key    = $item['key'] ?? '';
                $row = [
                    'id'       => $item['id']       ?? uniqid('item_'),
                    'key'      => $key,
                    'x'        => (float) ($item['x']        ?? 0),
                    'y'        => (float) ($item['y']        ?? 0),
                    'fontSize' => (int)   ($item['fontSize'] ?? 15),
                    'bold'     => !empty($item['bold']),
                    'enabled'  => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label'    => $defaults[$key]['label'] ?? $key,
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width']  = (float) ($item['width']  ?? $defaults[$key]['width']  ?? 35);
                    $row['height'] = (float) ($item['height'] ?? $defaults[$key]['height'] ?? 15);
                }
                $list[] = $row;
            }
            return $list;
        }

        $fields  = $config['fields'] ?? $defaults;
        $sigKeys  = $this->getSignatureKeys();
        $items  = [];
        foreach ($fields as $key => $f) {
            $row = [
                'id'       => 'legacy_' . $key,
                'key'      => $key,
                'x'        => (float) ($f['x']        ?? 0),
                'y'        => (float) ($f['y']        ?? 0),
                'fontSize' => (int)   ($f['fontSize'] ?? 15),
                'bold'     => !empty($f['bold']),
                'enabled'  => isset($f['enabled']) ? (int) $f['enabled'] : 1,
                'label'    => $defaults[$key]['label'] ?? $key,
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width']  = (float) ($f['width']  ?? 35);
                $row['height'] = (float) ($f['height'] ?? 15);
            }
            $items[] = $row;
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

    /**
     * แปลงวันที่สำหรับแสดงบน PDF ตามรูปแบบที่เลือก
     * @param string|int|\DateTime|null $date
     * @param string $format short=12 ม.ค. 2569, medium=12 มกราคม 2569, long=วันอาทิตย์ที่ 12 มกราคม พ.ศ. 2569, numeric=12/01/2569
     * @return string
     */
    protected function formatDateForLeavePdf($date, string $format): string
    {
        if (empty($date)) {
            return '';
        }
        $ts = is_numeric($date) ? (int) $date : strtotime($date);
        if ($ts === false) {
            return '';
        }
        if ($format === 'numeric') {
            return date('d/m/', $ts) . (date('Y', $ts) + 543);
        }
        return ThaiDateHelper::formatThaiDate($date, $format);
    }

    // ─────────────────────────────────────────────
    //  ค่าเริ่มต้นฟิลด์
    // ─────────────────────────────────────────────

    protected function getDefaultFields(): array
    {
        return [
            'emp_fullname'         => ['label' => 'ชื่อ-นามสกุลผู้ขอลา',   'x' => 30,  'y' => 50,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'department'           => ['label' => 'หน่วยงาน/แผนก',          'x' => 30,  'y' => 58,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'emp_position'         => ['label' => 'ตำแหน่งผู้ขอลา',          'x' => 120, 'y' => 50,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'leave_type_title'     => ['label' => 'ประเภทการลา',             'x' => 30,  'y' => 66,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_start'           => ['label' => 'วันที่เริ่มลา',           'x' => 30,  'y' => 74,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'date_end'             => ['label' => 'วันที่สิ้นสุด',           'x' => 80,  'y' => 74,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'total_days'           => ['label' => 'จำนวนวัน',                'x' => 30,  'y' => 82,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'reason'               => ['label' => 'เหตุผลการลา',             'x' => 30,  'y' => 90,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'address'              => ['label' => 'ที่อยู่ที่ติดต่อได้',     'x' => 30,  'y' => 98,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'contact_phone'        => ['label' => 'เบอร์โทรติดต่อ',          'x' => 30,  'y' => 106, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'place_go'             => ['label' => 'สถานที่ไป',               'x' => 30,  'y' => 114, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'create_date'           => ['label' => 'วันที่ยื่นคำขอ',          'x' => 30,  'y' => 122, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'leave_stats_used_before' => ['label' => 'ลามาแล้ว', 'x' => 30,  'y' => 132, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'leave_stats_total'       => ['label' => 'รวมเป็น', 'x' => 30,  'y' => 145, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'last_leave_date_start'    => ['label' => 'ลาครั้งก่อน ตั้งแต่วันที่', 'x' => 30,  'y' => 155, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'last_leave_date_end'      => ['label' => 'ลาครั้งก่อน ถึงวันที่',     'x' => 90,  'y' => 155, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'approve_date_1'        => ['label' => 'วันที่อนุมัติระดับ 1',    'x' => 30,  'y' => 218, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_date_2'        => ['label' => 'วันที่อนุมัติระดับ 2',    'x' => 80,  'y' => 218, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_date_3'        => ['label' => 'วันที่อนุมัติระดับ 3',    'x' => 130, 'y' => 218, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_date_4'        => ['label' => 'วันที่อนุมัติระดับ 4',    'x' => 180, 'y' => 218, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_date_5'        => ['label' => 'วันที่อนุมัติระดับ 5',    'x' => 30,  'y' => 236, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_date_6'        => ['label' => 'วันที่อนุมัติระดับ 6',    'x' => 80,  'y' => 236, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_date_7'        => ['label' => 'วันที่อนุมัติระดับ 7',    'x' => 130, 'y' => 236, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_date_8'        => ['label' => 'วันที่อนุมัติระดับ 8',    'x' => 180, 'y' => 236, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_1_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 1',   'x' => 30,  'y' => 210, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_1_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 1', 'x' => 30,  'y' => 214, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'approve_2_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 2',   'x' => 80,  'y' => 210, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_2_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 2', 'x' => 80,  'y' => 214, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'approve_3_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 3',   'x' => 130, 'y' => 210, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_3_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 3', 'x' => 130, 'y' => 214, 'fontSize' => 11, 'bold' => 0, 'enabled' => 1],
            'approve_4_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 4',   'x' => 180, 'y' => 210, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_4_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 4', 'x' => 180, 'y' => 214, 'fontSize' => 11, 'bold' => 0, 'enabled' => 0],
            'approve_5_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 5',   'x' => 30,  'y' => 228, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_5_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 5', 'x' => 30,  'y' => 232, 'fontSize' => 11, 'bold' => 0, 'enabled' => 0],
            'approve_6_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 6',   'x' => 80,  'y' => 228, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_6_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 6', 'x' => 80,  'y' => 232, 'fontSize' => 11, 'bold' => 0, 'enabled' => 0],
            'approve_7_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 7',   'x' => 130, 'y' => 228, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_7_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 7', 'x' => 130, 'y' => 232, 'fontSize' => 11, 'bold' => 0, 'enabled' => 0],
            'approve_8_name'        => ['label' => 'ชื่อผู้อนุมัติระดับ 8',   'x' => 180, 'y' => 228, 'fontSize' => 12, 'bold' => 0, 'enabled' => 0],
            'approve_8_position'    => ['label' => 'ตำแหน่งผู้อนุมัติระดับ 8', 'x' => 180, 'y' => 232, 'fontSize' => 11, 'bold' => 0, 'enabled' => 0],
            'signature_applicant'   => ['label' => 'ลายเซ็นผู้ขอลา',         'x' => 30,  'y' => 200, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'signature_approve_1'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 1', 'x' => 30,  'y' => 230, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'signature_approve_2'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 2', 'x' => 80,  'y' => 230, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'signature_approve_3'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 3', 'x' => 130, 'y' => 230, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'signature_approve_4'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 4', 'x' => 180, 'y' => 230, 'fontSize' => 15, 'bold' => 0, 'enabled' => 0, 'width' => 35, 'height' => 15],
            'signature_approve_5'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 5', 'x' => 30,  'y' => 248, 'fontSize' => 15, 'bold' => 0, 'enabled' => 0, 'width' => 35, 'height' => 15],
            'signature_approve_6'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 6', 'x' => 80,  'y' => 248, 'fontSize' => 15, 'bold' => 0, 'enabled' => 0, 'width' => 35, 'height' => 15],
            'signature_approve_7'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 7', 'x' => 130, 'y' => 248, 'fontSize' => 15, 'bold' => 0, 'enabled' => 0, 'width' => 35, 'height' => 15],
            'signature_approve_8'   => ['label' => 'ลายเซ็นผู้อนุมัติระดับ 8', 'x' => 180, 'y' => 248, 'fontSize' => 15, 'bold' => 0, 'enabled' => 0, 'width' => 35, 'height' => 15],
        ];
    }

    /** รายการ key ที่เป็นลายเซ็น (ใช้กำหนดขนาด width x height ได้) — รองรับผู้อนุมัติหลายชั้น */
    protected function getSignatureKeys(): array
    {
        return [
            'signature_applicant',
            'signature_approve_1', 'signature_approve_2', 'signature_approve_3',
            'signature_approve_4', 'signature_approve_5', 'signature_approve_6',
            'signature_approve_7', 'signature_approve_8',
        ];
    }
}

