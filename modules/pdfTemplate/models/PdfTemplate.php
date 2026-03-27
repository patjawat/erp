<?php

namespace app\modules\pdfTemplate\models;

use yii\db\ActiveRecord;

/**
 * PDF template record.
 *
 * @property int $id
 * @property string $name
 * @property string $file_path path เดิม (ใช้เมื่อ upload_id เป็น null)
 * @property int|null $upload_id FK ไปยัง filemanager uploads.id
 * @property string|null $use_for_context ใช้กับ context ใด เช่น development = ใบขอไปราชการ
 * @property string|null $data_source_id แหล่งข้อมูลที่เลือกใน editor เช่น hr.development, leave
 * @property string $page_width
 * @property string $page_height
 * @property string $created_at
 * @property string $updated_at
 * @property PdfTemplateField[] $fields
 */
class PdfTemplate extends ActiveRecord
{
    const CONTEXT_DEVELOPMENT = 'development';
    const CONTEXT_LEAVE = 'leave';
    /** ใบลาพักผ่อน (LT4) — ใช้เทมเพลตแยกจาก ลาป่วย/คลอด/กิจ */
    const CONTEXT_LEAVE_REST = 'leave.rest';
    /** ใบงานซ่อม (Helpdesk2) */
    const CONTEXT_HELPDESK2_REPAIR = 'helpdesk2.repair';
    /** ใบส่งซ่อม (Helpdesk2) */
    const CONTEXT_HELPDESK2_REPAIR_NOTICE = 'helpdesk2.repair.notice';
    /** ขอใช้รถยนต์ส่วนกลาง (Booking / Vehicle) */
    const CONTEXT_BOOKING_VEHICLE_CENTRAL = 'booking.vehicle.central';
    /** ขอใช้รถยนต์เดินทางไปราชการ (Booking / Vehicle) */
    const CONTEXT_BOOKING_VEHICLE_OFFICIAL = 'booking.vehicle.official';

    public static function tableName(): string
    {
        return '{{%pdf_templates}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['file_path'], 'required', 'when' => function ($model) {
                return empty($model->upload_id);
            }],
            [['page_width', 'page_height'], 'number', 'min' => 0],
            [['name'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 512],
            [['upload_id'], 'integer'],
            [['use_for_context', 'data_source_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * หาเทมเพลตที่ตั้งใช้กับ context (เช่น ใบขอไปราชการ).
     * ถ้าไม่มี คืนเทมเพลตแรกตาม id.
     */
    public static function findForContext(string $context): ?self
    {
        $template = static::find()->where(['use_for_context' => $context])->one();
        if ($template) {
            return $template;
        }
        return static::find()->orderBy(['id' => SORT_ASC])->one();
    }

    public function getFields(): \yii\db\ActiveQuery
    {
        return $this->hasMany(PdfTemplateField::class, ['template_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    public function getPageWidthMm(): float
    {
        return (float) ($this->page_width ?? 210);
    }

    public function getPageHeightMm(): float
    {
        return (float) ($this->page_height ?? 297);
    }
}
