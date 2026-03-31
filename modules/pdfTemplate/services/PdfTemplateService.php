<?php

namespace app\modules\pdfTemplate\services;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\pdfTemplate\models\PdfTemplate;
use app\modules\pdfTemplate\models\PdfTemplateField;
use app\modules\pdfTemplate\services\FieldValueResolver;
use setasign\Fpdi\Fpdi;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Service for PDF template layout: save/load normalized coordinates and convert to PDF units.
 * All coordinates are stored as percentage (0–1) of page dimensions for resolution independence.
 */
class PdfTemplateService
{
    /** Map Thai labels to field keys so layout saved with label still finds HR data keyed by field name */
    private const LABEL_TO_KEY = [
        'ชื่อหน่วยงาน' => 'organization_name',
        'หนังสืออ้างอิง' => 'reference_document',
        'เลขที่หนังสือ' => 'document_number',
        'ปีงบประมาณ' => 'thai_year',
        'ข้อความกำหนดเอง' => 'custom_text',
        'ชื่อผู้รับผิดชอบ' => 'officer_name',
        'ชื่อผู้ขอ' => 'officer_name',
        'ตำแหน่งผู้ขอ' => 'officer_position',
        'ลายเซ็นผู้ขอ' => 'officer_signature',
        'ชื่อสกุลผู้มอบหมายงาน' => 'assigned_to_fullname',
        'ตำแหน่งผู้มอบหมายงาน' => 'assigned_to_position',
        'ลายเซ็นผู้มอบหมายงาน' => 'assigned_to_signature',
        'วันที่เอกสาร' => 'document_date',
        'เรื่อง' => 'topic',
        'สถานที่' => 'location',
        'สถานที่จัดงาน' => 'location',
        'หน่วยงานที่จัด' => 'location_org',
        'จังหวัด' => 'province_name',
        'พาหนะเดินทาง' => 'vehicle_type_title',
        'ทะเบียนพาหนะเดินทาง' => 'license_plate',
        'ระยะทาง' => 'distance',
        'รวมค่าใช้จ่าย' => 'total_expense',
        'ค่าลงทะเบียน' => 'registration_amount',
        'ค่าที่พัก' => 'accommodation_amount',
        'ค่ายานพาหนะ' => 'vehicle_amount',
        'ค่าเบี้ยเลี้ยง' => 'allowance_amount',
        'ค่าอื่น ๆ' => 'other_amount',
        'วันที่เริ่ม' => 'date_start',
        'วันที่สิ้นสุด' => 'date_end',
        'คณะเดินทาง' => 'travel_party',
        'รายการคณะเดินทาง (loop)' => 'travel_party_list',
        'วันออกเดินทาง' => 'vehicle_date_start',
        'เวลาออกเดินทาง' => 'vehicle_time_start',
        'วันกลับ' => 'vehicle_date_end',
        'เวลากลับ' => 'vehicle_time_end',
        'นับวัน' => 'trip_days',
        'ผู้อนุมัติ (ชื่อ-นามสกุล)' => 'approver_fullname',
        'ผู้อนุมัติ (ตำแหน่ง)' => 'approver_position',
        'ผู้อนุมัติ (วันที่อนุมัติ)' => 'approver_approve_date',
        'ผู้อนุมัติ (ลายเซ็น)' => 'approver_signature',
        'สถานะผู้อนุมัติ' => 'approval_status',
        // Booking vehicle (central) common/legacy Thai labels
        'ชื่อผู้ขอ' => 'officer_name',
        'ชื่อผู้ขอใช้รถ' => 'officer_name',
        'ตำแหน่งผู้ขอ' => 'officer_position',
        'สังกัดผู้ขอ' => 'officer_department',
        'หน่วยงานผู้ขอ' => 'officer_department',
        'เบอร์โทรผู้ขอ' => 'phone',
        'เวลาไป' => 'time_go',
        'เวลากลับ' => 'time_back',
        'ทะเบียนรถ' => 'license_plate',
        'ประเภทรถ' => 'vehicle_type',
        'สถานที่ไป' => 'location',
        'เหตุผลการใช้รถ' => 'reason',
        'จำนวนผู้โดยสาร' => 'passenger',
        'ชื่อพนักงานขับ' => 'driver_name',
        'ชื่อหัวหน้ารับรอง' => 'leader_name',
        'ลายเซ็นผู้ขอใช้รถ' => 'emp_signature',
        'ลายเซ็นหัวหน้ารับรอง' => 'leader_signature',
        'ลายเซ็นพนักงานขับ' => 'driver_signature',
        // Leave legacy signature keys from older template configs
        'emp_sign' => 'emp_signature',
        'send_sign' => 'send_signature',
        'leader_sign' => 'approver_2_signature',
        'hr_sign' => 'approver_3_signature',
        'direc_sign' => 'approver_4_signature',
    ];

    /**
     * Save layout: array of field configs with x_percent, y_percent, etc.
     *
     * @param int $templateId
     * @param array $fields Each: field, page, x_percent, y_percent, width_percent?, height_percent?, font_size?
     * @return bool
     */
    public function saveLayout(int $templateId, array $fields): bool
    {
        $template = PdfTemplate::findOne($templateId);
        if (!$template) {
            return false;
        }
        PdfTemplateField::deleteAll(['template_id' => $templateId]);
        $sort = 0;
        foreach ($fields as $item) {
            $field = new PdfTemplateField();
            $field->template_id = $templateId;
            $field->field_name = $item['field'] ?? $item['field_name'] ?? '';
            $field->sort = $sort++;
            $pos = [
                'field' => $field->field_name,
                'page' => (int) ($item['page'] ?? 1),
                'x_percent' => $this->clamp01((float) ($item['x_percent'] ?? 0)),
                'y_percent' => $this->clamp01((float) ($item['y_percent'] ?? 0)),
                'width_percent' => $this->clamp01((float) ($item['width_percent'] ?? 0.2)),
                'height_percent' => $this->clamp01((float) ($item['height_percent'] ?? 0.03)),
                'font_size' => (int) ($item['font_size'] ?? 14),
                'font_bold' => !empty($item['font_bold']) ? 1 : 0,
                'alignment' => $item['alignment'] ?? 'L',
            ];
            if (!empty($item['source']) && is_string($item['source'])) {
                $pos['source'] = $item['source'];
            }
            if (!empty($item['date_format']) && is_string($item['date_format'])) {
                $pos['date_format'] = $item['date_format'];
            }
            if (isset($item['line_height_percent']) && (is_numeric($item['line_height_percent']) || $item['line_height_percent'] === '')) {
                $pos['line_height_percent'] = $item['line_height_percent'] !== '' ? $this->clamp01((float) $item['line_height_percent']) : 0.04;
            }
            if (isset($item['position_x_percent']) && (is_numeric($item['position_x_percent']) || $item['position_x_percent'] === '')) {
                $pos['position_x_percent'] = $item['position_x_percent'] !== '' ? $this->clamp01((float) $item['position_x_percent']) : 0.5;
            }
            if (!empty($item['approval_display_style']) && is_string($item['approval_display_style'])) {
                $pos['approval_display_style'] = in_array($item['approval_display_style'], ['checkmark', 'circle', 'text'], true) ? $item['approval_display_style'] : 'text';
            }
            if (isset($item['approval_level']) && ($item['approval_level'] === '' || (is_numeric($item['approval_level']) && (int) $item['approval_level'] >= 1 && (int) $item['approval_level'] <= 4))) {
                $pos['approval_level'] = $item['approval_level'] !== '' ? (int) $item['approval_level'] : 1;
            }
            if (isset($item['approval_show_when']) && in_array($item['approval_show_when'], ['approve', 'reject'], true)) {
                $pos['approval_show_when'] = $item['approval_show_when'];
            }
            $field->position_json = json_encode($pos);
            $field->save(false);
        }
        return true;
    }

    /**
     * Load layout for template as array of field configs (for editor and PDF generation).
     *
     * @param int $templateId
     * @return array
     */
    public function loadLayout(int $templateId): array
    {
        $rows = PdfTemplateField::find()
            ->where(['template_id' => $templateId])
            ->orderBy(['sort' => SORT_ASC])
            ->all();
        $out = [];
        foreach ($rows as $row) {
            $pos = $row->getPosition();
            $pos['field_name'] = $row->field_name;
            $out[] = $pos;
        }
        return $out;
    }

    /**
     * ส่งออก config ตำแหน่งฟิลด์เป็น array สำหรับนำไปใช้กับเทมเพลตอื่น (export/import).
     * รูปแบบเดียวกับที่ editor ส่งไป save-layout เพื่อให้นำเข้าได้ทันที
     *
     * @param int $templateId
     * @return array{template_name: string, data_source_id: string|null, page_width: float, page_height: float, exported_at: string, fields: array}
     */
    public function exportLayoutConfig(int $templateId): array
    {
        $template = PdfTemplate::findOne($templateId);
        if (!$template) {
            return ['fields' => [], 'template_name' => '', 'data_source_id' => null, 'page_width' => 210, 'page_height' => 297, 'exported_at' => date('c')];
        }
        $layout = $this->loadLayout($templateId);
        $fields = [];
        foreach ($layout as $item) {
            $fields[] = [
                'field' => $item['field'] ?? $item['field_name'] ?? '',
                'field_name' => $item['field_name'] ?? $item['field'] ?? '',
                'source' => $item['source'] ?? ($item['field'] ?? ''),
                'page' => (int) ($item['page'] ?? 1),
                'x_percent' => (float) ($item['x_percent'] ?? 0),
                'y_percent' => (float) ($item['y_percent'] ?? 0),
                'width_percent' => (float) ($item['width_percent'] ?? 0.2),
                'height_percent' => (float) ($item['height_percent'] ?? 0.03),
                'font_size' => (int) ($item['font_size'] ?? 14),
                'font_bold' => !empty($item['font_bold']) ? 1 : 0,
                'alignment' => $item['alignment'] ?? 'L',
                'date_format' => $item['date_format'] ?? '',
                'line_height_percent' => isset($item['line_height_percent']) ? (float) $item['line_height_percent'] : 0.04,
                'position_x_percent' => isset($item['position_x_percent']) ? (float) $item['position_x_percent'] : 0.5,
                'approval_display_style' => $item['approval_display_style'] ?? 'text',
                'approval_show_when' => $item['approval_show_when'] ?? '',
                'approval_level' => isset($item['approval_level']) ? (int) $item['approval_level'] : 1,
            ];
        }
        return [
            'template_name' => $template->name,
            'data_source_id' => $template->data_source_id,
            'page_width' => (float) $template->page_width,
            'page_height' => (float) $template->page_height,
            'exported_at' => date('c'),
            'fields' => $fields,
        ];
    }

    /**
     * Convert normalized (0–1) to PDF coordinates in mm.
     * pdfX = x_percent * page_width_mm, pdfY = y_percent * page_height_mm.
     *
     * @param float $xPercent 0–1
     * @param float $yPercent 0–1
     * @param float $pageWidthMm
     * @param float $pageHeightMm
     * @return array{x: float, y: float} in mm
     */
    public function convertToPdfCoordinates(
        float $xPercent,
        float $yPercent,
        float $pageWidthMm = 210,
        float $pageHeightMm = 297
    ): array {
        return [
            'x' => $this->clamp01($xPercent) * $pageWidthMm,
            'y' => $this->clamp01($yPercent) * $pageHeightMm,
        ];
    }

    /**
     * Convert a full field position (with width/height) to PDF mm for a given page.
     *
     * @param array $position Must contain x_percent, y_percent, width_percent?, height_percent?, page?
     * @param float $pageWidthMm
     * @param float $pageHeightMm
     * @return array{x: float, y: float, width: float, height: float} in mm
     */
    public function fieldToPdfMm(array $position, float $pageWidthMm = 210, float $pageHeightMm = 297): array
    {
        $x = $this->convertToPdfCoordinates(
            (float) ($position['x_percent'] ?? 0),
            (float) ($position['y_percent'] ?? 0),
            $pageWidthMm,
            $pageHeightMm
        );
        return [
            'x' => $x['x'],
            'y' => $x['y'],
            'width' => $this->clamp01((float) ($position['width_percent'] ?? 0.2)) * $pageWidthMm,
            'height' => $this->clamp01((float) ($position['height_percent'] ?? 0.03)) * $pageHeightMm,
        ];
    }

    /**
     * Data for preview: layout with normalized coords (for JS to scale to canvas).
     *
     * @param int $templateId
     * @return array{layout: array, page_width_mm: float, page_height_mm: float}
     */
    public function renderPreview(int $templateId): array
    {
        $template = PdfTemplate::findOne($templateId);
        if (!$template) {
            return ['layout' => [], 'page_width_mm' => 210, 'page_height_mm' => 297];
        }
        return [
            'layout' => $this->loadLayout($templateId),
            'page_width_mm' => $template->getPageWidthMm(),
            'page_height_mm' => $template->getPageHeightMm(),
        ];
    }

    /**
    /**
     * คืน path สัมบูรณ์ของไฟล์เทมเพลต (จาก filemanager เมื่อมี upload_id หรือจาก file_path เดิม).
     */
    public function getTemplateFilePath(PdfTemplate $template): ?string
    {
        if (!empty($template->upload_id) && class_exists(FileManagerHelper::class)) {
            $path = FileManagerHelper::getFilePath($template->upload_id);
            return $path && is_file($path) ? $path : null;
        }
        if (!empty($template->file_path)) {
            $path = Yii::getAlias('@webroot') . '/' . ltrim($template->file_path, '/');
            return is_file($path) ? $path : null;
        }
        return null;
    }

    /**
     * สร้าง PDF ใบลาจากเทมเพลตที่ตั้งใน /pdf-template/template (leave / leave.rest ตามประเภทการลา)
     *
     * @return string เนื้อหา PDF (binary)
     * @throws NotFoundHttpException ไม่มีโมดูล leave, ข้อมูลใบลา, หรือยังไม่ตั้งเทมเพลต/ไฟล์
     */
    public function generateLeavePdfBinary(int $leaveId): string
    {
        $leaveModule = Yii::$app->getModule('leave');
        if (!$leaveModule) {
            throw new NotFoundHttpException('โมดูล leave ไม่ได้เปิดใช้งาน');
        }
        try {
            $data = $leaveModule->runAction('setting/leave-print-data', ['id' => $leaveId]);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException('โหลดข้อมูลใบลาไม่ได้: ' . $e->getMessage());
        }
        if (is_array($data) && !empty($data['error'])) {
            throw new NotFoundHttpException($data['error']);
        }
        if (!is_array($data)) {
            throw new NotFoundHttpException('โหลดข้อมูลใบลาไม่ได้ (ตอบกลับไม่ถูกต้อง)');
        }
        $leaveTypeId = (string) ($data['leave_type_id'] ?? '');
        $isRest = ($leaveTypeId === 'LT4');
        $context = $isRest ? PdfTemplate::CONTEXT_LEAVE_REST : PdfTemplate::CONTEXT_LEAVE;
        $template = PdfTemplate::find()->where(['use_for_context' => $context])->one();
        if (!$template) {
            $hint = $isRest
                ? 'กรุณาไปที่ /pdf-template แล้วตั้งค่า «เทมเพลตสำหรับใบลาพักผ่อน»'
                : 'กรุณาไปที่ /pdf-template แล้วตั้งค่า «เทมเพลตสำหรับใบลา (ป่วย/คลอด/กิจ)»';
            throw new NotFoundHttpException(
                'ยังไม่มีเทมเพลตสำหรับ' . ($isRest ? 'ใบลาพักผ่อน' : 'ใบลาป่วย/คลอด/กิจ') . ' — ' . $hint
            );
        }
        $path = $this->getTemplateFilePath($template);
        if ($path === null || !is_file($path)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต PDF กรุณาอัปโหลดที่ /pdf-template');
        }

        return $this->generatePdfWithData((int) $template->id, $data);
    }

    /**
     * สร้าง PDF จากเทมเพลต + ข้อมูลที่ใส่ในแต่ละฟิลด์.
     * ใช้เฉพาะ $data ที่ส่งเข้ามา (ไม่มี sample ใน service).
     * Caller: HR ส่งข้อมูลจริง + __from_hr_print; ปุ่มพิมพ์ตัวอย่างส่งชุดตัวอย่างเป็น $data.
     *
     * @param int $templateId
     * @param array $data คู่ key => value ตามชื่อฟิลด์ใน layout (field / field_name)
     * @return string เนื้อหา PDF (binary)
     */
    public function generatePdfWithData(int $templateId, array $data = []): string
    {
        $template = PdfTemplate::findOne($templateId);
        if (!$template) {
            throw new \RuntimeException('Template not found');
        }
        $templatePath = $this->getTemplateFilePath($template);
        if ($templatePath === null || !is_file($templatePath)) {
            throw new \RuntimeException('Template file not found');
        }
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));
        }
        $pdf = new Fpdi();
        $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
        $pathToUnlink = null;
        $signatureTempPaths = [];
        try {
            $pageCount = $pdf->setSourceFile($templatePath);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'compression') !== false || stripos($msg, 'not supported') !== false) {
                $converted = $this->convertPdfTo14WithGhostscript($templatePath);
                if ($converted !== null) {
                    $pathToUnlink = $converted;
                    $pageCount = $pdf->setSourceFile($converted);
                } else {
                    throw new \RuntimeException(
                        'เทมเพลต PDF ใช้รูปแบบการบีบอัดที่ FPDI รองรับไม่ได้ (รองรับเฉพาะ PDF 1.4). '
                        . 'วิธีแก้: (1) บนเครื่องคุณ ใช้ Ghostscript แปลง: gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=out.pdf ต้นฉบับ.pdf แล้วอัปโหลด out.pdf แทนเทมเพลตเดิม (2) หรือเปิด PDF ใน Adobe Acrobat แล้ว Save As > PDF 1.4 (3) ถ้าใช้ Docker ให้ติดตั้ง Ghostscript ใน image: apt-get install -y ghostscript'
                    );
                }
            } else {
                throw $e;
            }
        }
        $pageCount = is_numeric($pageCount) && (int) $pageCount >= 1 ? (int) $pageCount : 1;
        $layout = $this->loadLayout($templateId);

        if (isset($data['__from_hr_print'])) {
            unset($data['__from_hr_print']);
        }
        $values = $data;

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplIdx);
            $pageW = (float) ($size['width'] ?? $template->getPageWidthMm());
            $pageH = (float) ($size['height'] ?? $template->getPageHeightMm());
            $pdf->AddPage($size['orientation'] ?? 'P', [$pageW, $pageH]);
            $pdf->useTemplate($tplIdx);

            foreach ($layout as $item) {
                $itemPage = (int) ($item['page'] ?? 1);
                if ($itemPage !== $pageNo) {
                    continue;
                }

                $sourcePath = !empty($item['source']) && is_string($item['source']) ? trim($item['source']) : '';
                if ($sourcePath !== '') {
                    $lookupKey = self::LABEL_TO_KEY[$sourcePath] ?? $sourcePath;
                } else {
                    $fieldKey = trim((string) ($item['field'] ?? $item['field_name'] ?? ''));
                    $lookupKey = self::LABEL_TO_KEY[$fieldKey] ?? $fieldKey;
                }

                if ($lookupKey === 'travel_party_list') {
                    $members = isset($values['travel_party_members']) && is_array($values['travel_party_members']) ? $values['travel_party_members'] : [];
                    if (count($members) === 0) {
                        continue;
                    }
                    $lineHeightPercent = isset($item['line_height_percent']) && $item['line_height_percent'] !== '' ? (float) $item['line_height_percent'] : 0.04;
                    $positionXPercent = isset($item['position_x_percent']) && $item['position_x_percent'] !== '' ? (float) $item['position_x_percent'] : 0.5;
                    $startMm = $this->fieldToPdfMm($item, $pageW, $pageH);
                    $fontSize = (int) ($item['font_size'] ?? 14);
                    $fontStyle = !empty($item['font_bold']) ? 'B' : '';
                    $alignment = isset($item['alignment']) && in_array($item['alignment'], ['L', 'C', 'R'], true) ? $item['alignment'] : 'L';
                    $pdf->SetFont('THSarabunNew', $fontStyle, $fontSize);
                    $lineHeightMm = $lineHeightPercent * $pageH;
                    $posXCol = $this->clamp01($positionXPercent) * $pageW;
                    $fullnameBoxWidth = max(1, $posXCol - $startMm['x']);
                    $positionBoxWidth = max(1, ($startMm['x'] + $startMm['width']) - $posXCol);
                    $y = $startMm['y'] + $this->fontSizeToBaselineOffset($fontSize, $pageW);
                    foreach ($members as $row) {
                        $fullname = (string) ($row['fullname'] ?? '');
                        $position = (string) ($row['position'] ?? '');
                        if ($fullname !== '') {
                            $enc = iconv('UTF-8', 'cp874//IGNORE', $fullname);
                            $encStr = $enc !== false ? $enc : $fullname;
                            $tw = $pdf->GetStringWidth($encStr);
                            $xName = $this->alignX($alignment, $startMm['x'], $fullnameBoxWidth, $tw);
                            $pdf->SetXY($xName, $y);
                            $pdf->Write(0, $encStr);
                        }
                        if ($position !== '') {
                            $enc = iconv('UTF-8', 'cp874//IGNORE', $position);
                            $encStr = $enc !== false ? $enc : $position;
                            $tw = $pdf->GetStringWidth($encStr);
                            $xPos = $this->alignX($alignment, $posXCol, $positionBoxWidth, $tw);
                            $pdf->SetXY($xPos, $y);
                            $pdf->Write(0, $encStr);
                        }
                        $y += $lineHeightMm;
                    }
                    continue;
                }

                if ($lookupKey === 'approval_status') {
                    $level = isset($item['approval_level']) && (int) $item['approval_level'] >= 1 && (int) $item['approval_level'] <= 4 ? (int) $item['approval_level'] : 1;
                    $status = (string) ($values['approver_' . $level . '_status'] ?? '');
                    $showWhen = isset($item['approval_show_when']) && in_array($item['approval_show_when'], ['approve', 'reject'], true) ? $item['approval_show_when'] : '';
                    if ($showWhen === 'approve' && $status !== 'Pass' && $status !== 'กำหนด') {
                        continue;
                    }
                    if ($showWhen === 'reject' && $status !== 'Reject') {
                        continue;
                    }
                    $style = isset($item['approval_display_style']) && in_array($item['approval_display_style'], ['checkmark', 'circle', 'text'], true) ? $item['approval_display_style'] : 'text';
                    $approvalOut = $this->formatApprovalStatusForPdf($status, $style);
                    if ($approvalOut !== null) {
                        $mm = $this->fieldToPdfMm($item, $pageW, $pageH);
                        $fontSize = (int) ($item['font_size'] ?? 14);
                        $fontStyle = !empty($item['font_bold']) ? 'B' : '';
                        $alignment = isset($item['alignment']) && in_array($item['alignment'], ['L', 'C', 'R'], true) ? $item['alignment'] : 'L';
                        $yBaseline = $mm['y'] + $this->fontSizeToBaselineOffset($fontSize, $pageW);
                        if ($approvalOut['use_symbol_font']) {
                            $pdf->SetFont('ZapfDingbats', '', $fontSize);
                            $charStr = $approvalOut['char'];
                            $tw = $pdf->GetStringWidth($charStr);
                            $x = $this->alignX($alignment, $mm['x'], $mm['width'], $tw);
                            $pdf->SetXY($x, $yBaseline);
                            $pdf->Write(0, $charStr);
                            $pdf->SetFont('THSarabunNew', $fontStyle, $fontSize);
                        } else {
                            $pdf->SetFont('THSarabunNew', $fontStyle, $fontSize);
                            $encoded = iconv('UTF-8', 'cp874//IGNORE', $approvalOut['text']);
                            $encStr = $encoded !== false ? $encoded : $approvalOut['text'];
                            $tw = $pdf->GetStringWidth($encStr);
                            $x = $this->alignX($alignment, $mm['x'], $mm['width'], $tw);
                            $pdf->SetXY($x, $yBaseline);
                            $pdf->Write(0, $encStr);
                        }
                    }
                    continue;
                }

                $level = null;
                if (in_array($lookupKey, ['approver_fullname', 'approver_position', 'approver_approve_date', 'approver_signature'], true)) {
                    $level = isset($item['approval_level']) && (int) $item['approval_level'] >= 1 && (int) $item['approval_level'] <= 4 ? (int) $item['approval_level'] : 1;
                    $suffix = str_replace('approver_', '', $lookupKey);
                    $text = (string) ($values['approver_' . $level . '_' . $suffix] ?? '');
                } else {
                    $fieldKey = trim((string) ($item['field'] ?? ''));
                    $fieldNameKey = trim((string) ($item['field_name'] ?? ''));
                    $sourceKey = $sourcePath !== '' ? (self::LABEL_TO_KEY[$sourcePath] ?? $sourcePath) : '';
                    $resolvedFromSource = $sourcePath !== '' ? (string) FieldValueResolver::resolve($values, $sourceKey) : '';
                    if ($resolvedFromSource !== '') {
                        $text = $resolvedFromSource;
                    } else {
                        $text = (string) (
                            ($sourceKey !== '' ? ($values[$sourceKey] ?? null) : null)
                            ?? ($lookupKey !== '' ? ($values[$lookupKey] ?? null) : null)
                            ?? ($fieldKey !== '' ? ($values[$fieldKey] ?? null) : null)
                            ?? ($fieldNameKey !== '' ? ($values[$fieldNameKey] ?? null) : null)
                            ?? ''
                        );
                    }
                }
                if ($text === '') {
                    continue;
                }
                $mm = $this->fieldToPdfMm($item, $pageW, $pageH);
                $isSignature = substr($lookupKey, -strlen('_signature')) === '_signature';
                if ($isSignature) {
                    $imagePath = $this->resolveSignatureImagePath($text, $signatureTempPaths);
                    if ($imagePath !== null && is_file($imagePath)) {
                        $pdf->Image($imagePath, $mm['x'], $mm['y'], $mm['width'], $mm['height']);
                        continue;
                    }
                }
                if ($isSignature && is_file($text)) {
                    $pdf->Image($text, $mm['x'], $mm['y'], $mm['width'], $mm['height']);
                    continue;
                }
                $dateFormat = !empty($item['date_format']) && is_string($item['date_format']) ? trim($item['date_format']) : '';
                if ($dateFormat !== '') {
                    $text = $this->formatDateForPdf($text, $dateFormat);
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($text))) {
                    $text = $this->formatDateForPdf($text, 'short');
                }
                $fontSize = (int) ($item['font_size'] ?? 14);
                $fontStyle = !empty($item['font_bold']) ? 'B' : '';
                $alignment = isset($item['alignment']) && in_array($item['alignment'], ['L', 'C', 'R'], true) ? $item['alignment'] : 'L';
                $pdf->SetFont('THSarabunNew', $fontStyle, $fontSize);
                $encoded = iconv('UTF-8', 'cp874//IGNORE', $text);
                $encStr = $encoded !== false ? $encoded : $text;
                $boxWidth = max(1.0, (float) ($mm['width'] ?? 1.0));
                $lineHeight = max(1.2, (float) $fontSize * 0.42); // mm in A4 mode
                // Fixed behavior: single line + ellipsis to avoid vertical overlap.
                $effectiveMaxLines = 1;
                $encStr = $this->truncateTextToLines($pdf, $encStr, $boxWidth, $effectiveMaxLines);
                $pdf->SetXY($mm['x'], $mm['y']);
                $pdf->MultiCell($boxWidth, $lineHeight, $encStr, 0, $alignment, false);
            }
        }
        $output = $pdf->Output('', 'S');
        if ($pathToUnlink !== null && is_file($pathToUnlink)) {
            @unlink($pathToUnlink);
        }
        foreach ($signatureTempPaths as $tempPath) {
            if (is_string($tempPath) && $tempPath !== '' && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
        return $output;
    }

    /**
     * Resolve signature image value to local file path.
     * Supports absolute path, webroot-relative path, full URL, and data URI.
     *
     * @param string $rawValue
     * @param array<int, string> $tempPaths
     * @return string|null
     */
    private function resolveSignatureImagePath(string $rawValue, array &$tempPaths): ?string
    {
        $value = trim($rawValue);
        if ($value === '') {
            return null;
        }
        if (is_file($value)) {
            return $value;
        }
        if (strpos($value, 'data:image/') === 0) {
            if (preg_match('#^data:image/(\w+);base64,(.+)$#', $value, $m)) {
                $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
                $bin = base64_decode($m[2], true);
                if ($bin !== false) {
                    $tmp = tempnam(sys_get_temp_dir(), 'pdf_sig_');
                    if ($tmp !== false) {
                        $tmpPath = $tmp . '.' . $ext;
                        if (@file_put_contents($tmpPath, $bin) !== false) {
                            @unlink($tmp);
                            $tempPaths[] = $tmpPath;
                            return $tmpPath;
                        }
                        @unlink($tmp);
                    }
                }
            }
            return null;
        }
        $parsed = parse_url($value);
        if (is_array($parsed) && isset($parsed['path']) && is_string($parsed['path'])) {
            $webPath = Yii::getAlias('@webroot') . '/' . ltrim($parsed['path'], '/');
            if (is_file($webPath)) {
                return $webPath;
            }
        }
        if (strpos($value, '/') === 0) {
            $webPath = Yii::getAlias('@webroot') . '/' . ltrim($value, '/');
            if (is_file($webPath)) {
                return $webPath;
            }
        }
        return null;
    }

    /**
     * แปลง PDF เป็นเวอร์ชัน 1.4 ด้วย Ghostscript (ถ้ามีในระบบ) เพื่อให้ FPDI อ่านได้.
     * ลองสองแบบ: (1) ส่งเนื้อหา PDF ผ่าน stdin ให้ gs (2) ส่ง path ไฟล์ให้ gs
     *
     * @param string $inputPath path สัมบูรณ์ไปยังไฟล์ PDF
     * @return string|null path ไปยังไฟล์ชั่วคราวที่แปลงแล้ว หรือ null ถ้าแปลงไม่ได้
     */
    private function convertPdfTo14WithGhostscript(string $inputPath): ?string
    {
        $inputPath = realpath($inputPath) ?: $inputPath;
        if (!is_file($inputPath) || !is_readable($inputPath)) {
            return null;
        }
        $candidates = [];
        $param = trim((string) (Yii::$app->params['ghostscript.binary'] ?? ''));
        if ($param !== '') {
            $candidates[] = $param;
        }
        if (DIRECTORY_SEPARATOR === '/') {
            $candidates[] = '/usr/bin/gs';
        }
        $candidates[] = 'gs';
        $tmpDir = is_dir(Yii::getAlias('@runtime')) ? Yii::getAlias('@runtime') : sys_get_temp_dir();
        if (!is_writable($tmpDir)) {
            $tmpDir = sys_get_temp_dir();
        }
        $tmp = $tmpDir . '/fpdi_' . uniqid('', true) . '.pdf';
        $env = ['PATH' => '/usr/bin:/bin', 'HOME' => $tmpDir];

        $gsArgsStdin = [
            '-dBATCH', '-dNOPAUSE', '-q', '-dNOSAFER',
            '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-dPDFSETTINGS=/default',
            '-sOutputFile=' . $tmp,
            '-',
        ];
        $inputBytes = @file_get_contents($inputPath);
        if ($inputBytes !== false && strlen($inputBytes) > 0) {
            foreach ($candidates as $gsBinary) {
                $result = $this->runGsWithStdin($gsBinary, $gsArgsStdin, $inputBytes, $env);
                if ($result !== null) {
                    return $result;
                }
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
                $tmp = $tmpDir . '/fpdi_' . uniqid('', true) . '.pdf';
                $gsArgsStdin[7] = '-sOutputFile=' . $tmp;
            }
        }

        $gsArgsPath = [
            '-dBATCH', '-dNOPAUSE', '-q', '-dNOSAFER',
            '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-dPDFSETTINGS=/default',
            '-sOutputFile=' . $tmp,
            $inputPath,
        ];
        foreach ($candidates as $gsBinary) {
            $proc = @proc_open(
                array_merge([$gsBinary], $gsArgsPath),
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                null,
                $env
            );
            if (!is_resource($proc)) {
                continue;
            }
            fclose($pipes[0]);
            stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);
            if ($code === 0 && is_file($tmp) && filesize($tmp) >= 100) {
                return $tmp;
            }
            if (is_file($tmp)) {
                @unlink($tmp);
            }
            $tmp = $tmpDir . '/fpdi_' . uniqid('', true) . '.pdf';
        }

        $df = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (function_exists('exec') && !in_array('exec', $df, true)) {
            $outputArg = escapeshellarg($tmp);
            $inputArg = escapeshellarg($inputPath);
            foreach ($candidates as $gs) {
                $gsArg = escapeshellarg($gs);
                $cmd = $gsArg . ' -dBATCH -dNOPAUSE -q -dNOSAFER -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/default -sOutputFile=' . $outputArg . ' ' . $inputArg . ' 2>/dev/null';
                @exec($cmd, $out, $code);
                if ($code === 0 && is_file($tmp) && filesize($tmp) >= 100) {
                    return $tmp;
                }
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
                $tmp = $tmpDir . '/fpdi_' . uniqid('', true) . '.pdf';
            }
        }
        if (function_exists('shell_exec') && !in_array('shell_exec', $df, true)) {
            $outputArg = escapeshellarg($tmp);
            $inputArg = escapeshellarg($inputPath);
            foreach ($candidates as $gs) {
                $gsArg = escapeshellarg($gs);
                $cmd = $gsArg . ' -dBATCH -dNOPAUSE -q -dNOSAFER -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/default -sOutputFile=' . $outputArg . ' ' . $inputArg . ' 2>/dev/null';
                @shell_exec($cmd);
                if (is_file($tmp) && filesize($tmp) >= 100) {
                    return $tmp;
                }
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
                $tmp = $tmpDir . '/fpdi_' . uniqid('', true) . '.pdf';
            }
        }
        return null;
    }

    /**
     * รัน Ghostscript โดยส่ง PDF ผ่าน stdin (ใช้ - เป็น input).
     */
    private function runGsWithStdin(string $gsBinary, array $gsArgs, string $stdinContent, array $env): ?string
    {
        $proc = @proc_open(
            array_merge([$gsBinary], $gsArgs),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );
        if (!is_resource($proc)) {
            return null;
        }
        $written = @fwrite($pipes[0], $stdinContent);
        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        $outPath = null;
        foreach ($gsArgs as $arg) {
            if (strpos($arg, '-sOutputFile=') === 0) {
                $outPath = substr($arg, 13);
                break;
            }
        }
        if ($written !== false && $code === 0 && $outPath !== null && is_file($outPath) && filesize($outPath) >= 100) {
            return $outPath;
        }
        return null;
    }

    /**
     * แปลงไฟล์เทมเพลต PDF เป็นเวอร์ชัน 1.4 แทนที่ไฟล์เดิม (เรียกหลังอัปโหลดเทมเพลต).
     * ถ้าแปลงได้ ไฟล์ที่ path จะถูกแทนที่ด้วยเวอร์ชัน 1.4
     *
     * @param string $path path สัมบูรณ์ไปยังไฟล์ PDF
     * @return bool true ถ้าแปลงและแทนที่ไฟล์สำเร็จ
     */
    public function convertTemplateFileToPdf14(string $path): bool
    {
        $converted = $this->convertPdfTo14WithGhostscript($path);
        if ($converted === null || !is_file($converted)) {
            return false;
        }
        $ok = @copy($converted, $path);
        @unlink($converted);
        return $ok;
    }

    /**
     * แปลงสถานะ approve (กำหนด / Pass / Reject) เป็นข้อความหรือสัญลักษณ์สำหรับ PDF.
     * แสดงเมื่อสถานะเป็น กำหนด, Pass หรือ Reject.
     * รูปแบบ text = ข้อความไทย (ฟอนต์ THSarabunNew).
     * รูปแบบ checkmark/circle = สัญลักษณ์จาก ZapfDingbats.
     *
     * @return array{text: string, use_symbol_font: bool, char: string}|null null ถ้าไม่มีสถานะที่รองรับ
     */
    private function formatApprovalStatusForPdf(string $status, string $style): ?array
    {
        $status = trim($status);
        if ($status === 'Pass') {
            if ($style === 'checkmark') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(52)]; // ✔
            }
            if ($style === 'circle') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(108)]; // ●
            }
            return ['text' => 'อนุมัติ', 'use_symbol_font' => false, 'char' => ''];
        }
        if ($status === 'Reject') {
            if ($style === 'checkmark') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(55)]; // ✗
            }
            if ($style === 'circle') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(109)]; // ○-like
            }
            return ['text' => 'ไม่อนุมัติ', 'use_symbol_font' => false, 'char' => ''];
        }
        if ($status === 'กำหนด') {
            if ($style === 'checkmark') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(52)]; // ✔
            }
            if ($style === 'circle') {
                return ['text' => '', 'use_symbol_font' => true, 'char' => \chr(108)]; // ●
            }
            return ['text' => 'กำหนด', 'use_symbol_font' => false, 'char' => ''];
        }
        return null;
    }

    private function clamp01(float $v): float
    {
        return max(0.0, min(1.0, $v));
    }

    /**
     * คำนวณ X สำหรับจัดแนวข้อความภายในกล่อง (ซ้าย/กลาง/ขวา).
     *
     * @param string $alignment L | C | R
     * @param float $boxX ตำแหน่ง x ด้านซ้ายของกล่อง
     * @param float $boxWidth ความกว้างกล่อง
     * @param float $textWidth ความกว้างข้อความ (จาก GetStringWidth)
     * @return float ตำแหน่ง x ที่ใช้กับ SetXY
     */
    private function alignX(string $alignment, float $boxX, float $boxWidth, float $textWidth): float
    {
        $align = strtoupper(trim($alignment));
        if ($align === 'C' && $boxWidth > $textWidth) {
            return $boxX + ($boxWidth - $textWidth) / 2;
        }
        if ($align === 'R' && $boxWidth > $textWidth) {
            return $boxX + $boxWidth - $textWidth;
        }
        return $boxX;
    }

    /**
     * Offset (same unit as page) so that SetXY(x, y + offset) places the visual top of text at y.
     * FPDF Write() uses Y as baseline; ascent ~0.8 of font height.
     * Unit: if page is in points (width > 400) use pt, else mm.
     *
     * @param int $fontSizePt
     * @param float $pageW page width (to detect unit: pt ~595, mm ~210)
     * @return float offset to add to y in same unit as page
     */
    private function fontSizeToBaselineOffset(int $fontSizePt, float $pageW = 210): float
    {
        $ascentRatio = 0.8;
        if ($pageW > 400) {
            return $fontSizePt * $ascentRatio;
        }
        $mmPerPt = 0.3528;
        return $fontSizePt * $mmPerPt * $ascentRatio;
    }

    /**
     * จัดรูปแบบวันที่สำหรับแสดงใน PDF.
     * รองรับ: raw (คงค่าเดิม), day_only (01), month_only (12),
     * month_name_short (ธ.ค.), month_name_full (ธันวาคม),
     * day_month (1 ม.ค.), year_only (2569),
     * numeric (01/01/2569), short (1 ม.ค. 2569), medium_p (1 มกราคม พ.ศ. 2569), month_year (มกราคม 2569), medium, long.
     * ค่าที่รับควรเป็น Y-m-d หรือค่าที่ strtotime แปลงได้
     */
    private function formatDateForPdf(string $value, string $format): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if ($format === 'raw') {
            return $value;
        }
        $ts = is_numeric($value) ? (int) $value : @strtotime($value);
        if ($ts === false || $ts <= 0) {
            return $value;
        }
        $day = (int) date('j', $ts);
        $month = (int) date('n', $ts);
        $yearThai = (int) date('Y', $ts) + 543;
        $thaiMonthsShort = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
        ];
        $thaiMonthsFull = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        switch ($format) {
            case 'day_only':
                return sprintf('%02d', $day);
            case 'month_only':
                return sprintf('%02d', $month);
            case 'month_name_short':
                return $thaiMonthsShort[$month];
            case 'month_name_full':
                return $thaiMonthsFull[$month];
            case 'day_month':
                return $day . ' ' . $thaiMonthsShort[$month];
            case 'year_only':
                return (string) $yearThai;
            case 'numeric':
                return sprintf('%02d/%02d/%04d', $day, $month, $yearThai);
            case 'short':
                return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $yearThai;
            case 'medium_p':
                return $day . ' ' . $thaiMonthsFull[$month] . ' พ.ศ. ' . $yearThai;
            case 'month_year':
                return $thaiMonthsFull[$month] . ' ' . $yearThai;
            case 'medium':
            case 'long':
                return \app\components\ThaiDateHelper::formatThaiDate($ts, $format);
            default:
                return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $yearThai;
        }
    }

    /**
     * Wrap and truncate text to max lines, append "..." when overflow.
     */
    private function truncateTextToLines(Fpdi $pdf, string $text, float $boxWidth, int $maxLines): string
    {
        $text = trim($text);
        if ($text === '' || $maxLines <= 0) {
            return '';
        }
        $parts = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = [];
        foreach ($parts as $part) {
            $wrapped = $this->wrapTextByWidth($pdf, (string) $part, $boxWidth);
            if (empty($wrapped)) {
                $wrapped = [''];
            }
            foreach ($wrapped as $line) {
                $lines[] = $line;
                if (count($lines) >= $maxLines) {
                    break 2;
                }
            }
        }
        $allWrapped = [];
        foreach ($parts as $part) {
            $wrapped = $this->wrapTextByWidth($pdf, (string) $part, $boxWidth);
            if (empty($wrapped)) {
                $wrapped = [''];
            }
            $allWrapped = array_merge($allWrapped, $wrapped);
        }
        if (count($allWrapped) > $maxLines && !empty($lines)) {
            $last = (string) $lines[count($lines) - 1];
            $ellipsis = '...';
            while ($last !== '' && $pdf->GetStringWidth($last . $ellipsis) > $boxWidth) {
                $last = substr($last, 0, -1);
            }
            $lines[count($lines) - 1] = $last . $ellipsis;
        }
        return implode("\n", $lines);
    }

    /**
     * Wrap single paragraph by width (no newline preserved).
     *
     * @return string[]
     */
    private function wrapTextByWidth(Fpdi $pdf, string $text, float $boxWidth): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }
        $tokens = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $line = '';
        foreach ($tokens as $token) {
            $candidate = $line === '' ? $token : $line . $token;
            if ($pdf->GetStringWidth($candidate) <= $boxWidth) {
                $line = $candidate;
                continue;
            }
            if ($line !== '') {
                $lines[] = trim($line);
                $line = ltrim($token);
                if ($pdf->GetStringWidth($line) <= $boxWidth) {
                    continue;
                }
            } else {
                $line = $token;
            }
            while ($line !== '' && $pdf->GetStringWidth($line) > $boxWidth) {
                $chunk = '';
                $len = strlen($line);
                for ($i = 0; $i < $len; $i++) {
                    $next = $chunk . substr($line, $i, 1);
                    if ($pdf->GetStringWidth($next) > $boxWidth) {
                        break;
                    }
                    $chunk = $next;
                }
                if ($chunk === '') {
                    break;
                }
                $lines[] = trim($chunk);
                $line = ltrim(substr($line, strlen($chunk)));
            }
        }
        if ($line !== '') {
            $lines[] = trim($line);
        }
        return $lines;
    }
}
