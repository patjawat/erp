<?php
namespace app\modules\helpdesk2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * SLA settings for helpdesk2 (stored in `categorise`)
 *
 * - name: helpdesk2_sla
 * - code: default
 * - data_json: { urgency_hours: { "1": 72, ... } }
 */
class HelpdeskSlaSetting extends ActiveRecord
{
    public const SETTING_NAME = 'helpdesk2_sla';
    public const SETTING_CODE = 'default';

    public static function tableName(): string
    {
        return 'categorise';
    }

    public function rules(): array
    {
        return [
            [['name', 'code', 'title'], 'string', 'max' => 255],
            [['data_json'], 'safe'],
            [['active'], 'integer'],
        ];
    }

    public static function getRecord(): self
    {
        $record = static::findOne(['name' => self::SETTING_NAME, 'code' => self::SETTING_CODE]);
        if (!$record) {
            $record = new static();
            $record->name = self::SETTING_NAME;
            $record->code = self::SETTING_CODE;
            $record->title = 'SLA Helpdesk2';
            $record->data_json = json_encode([
                'urgency_hours' => [
                    // default mapping (backward compatible)
                    '1' => 72, // low
                    '2' => 24, // medium
                    '3' => 4,  // high
                    '4' => 1,  // critical
                    'low' => 72,
                    'medium' => 24,
                    'high' => 4,
                    'critical' => 1,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $record->active = 1;
            $record->save(false);
        }
        return $record;
    }

    public function getConfig(): array
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        return is_array($json) ? $json : [];
    }

    public function setConfig(array $config): bool
    {
        $this->data_json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->save(false);
    }

    // ============================================================
    // Service Catalog (HAIT หมวด 4 ระดับ 2 — SLA รายการบริการ)
    // ============================================================

    /**
     * แคตตาล็อกบริการเริ่มต้น พร้อมข้อตกลงระดับบริการ (SLA) ต่อรายการ
     * อ้างอิงตัวอย่างในคู่มือ TMI บทที่ 4 (OPD 15 นาที / back office 30 นาที ฯลฯ)
     *
     * โครงสร้างแต่ละรายการ:
     *   code         : รหัสบริการ (ไม่ซ้ำ)
     *   title        : ชื่อรายการบริการ
     *   response_min : เวลารับเรื่องที่รับประกัน (นาที) — วัดถึง acknowledged_at
     *   resolve_min  : เวลาแก้ไขที่รับประกัน (นาที) — วัดถึง resolved_at
     *   device_types : device_type_id ที่จับเข้ารายการนี้ (ว่าง = ไม่ผูกอัตโนมัติ)
     *   note         : หมายเหตุ/ข้อยกเว้น
     *
     * @return array<int,array<string,mixed>>
     */
    public static function defaultServiceCatalog(): array
    {
        return [
            [
                'code' => 'hardware',
                'title' => 'แก้ปัญหาฮาร์ดแวร์/เครื่องคอมพิวเตอร์ขัดข้อง',
                'response_min' => 15,
                'resolve_min' => 120,
                'device_types' => ['CAL-11', 'computer', 'printer', 'CAL-01', 'CAL-02'],
                'note' => 'จุดบริการผู้ป่วยนอก 15 นาที / back office 30 นาที',
            ],
            [
                'code' => 'network',
                'title' => 'ระบบเครือข่าย/อินเทอร์เน็ตขัดข้อง',
                'response_min' => 15,
                'resolve_min' => 60,
                'device_types' => ['CAL-07', 'network'],
                'note' => '',
            ],
            [
                'code' => 'software',
                'title' => 'ระบบงาน/ซอฟต์แวร์ขัดข้อง',
                'response_min' => 30,
                'resolve_min' => 240,
                'device_types' => [],
                'note' => '',
            ],
            [
                'code' => 'data_service',
                'title' => 'บริการข้อมูล/ออกรายงาน',
                'response_min' => 60,
                'resolve_min' => 2880,
                'device_types' => [],
                'note' => 'ออกรายงานจากฐานข้อมูลที่มีอยู่แล้วภายใน 2 วันทำการ',
            ],
            [
                'code' => 'new_requirement',
                'title' => 'พัฒนา/ความต้องการใหม่',
                'response_min' => 60,
                'resolve_min' => 4320,
                'device_types' => [],
                'note' => '',
            ],
            [
                'code' => 'other',
                'title' => 'อื่นๆ/ไม่ระบุประเภท',
                'response_min' => 30,
                'resolve_min' => 1440,
                'device_types' => [],
                'note' => 'รายการที่จับเข้าบริการอื่นไม่ได้',
            ],
        ];
    }

    /**
     * ตัวคูณเวลาตามระดับความเร่งด่วน (ยิ่งด่วน ยิ่งสั้น)
     * งานที่ไม่ระบุความเร่งด่วนถือเป็น medium (ตัวคูณ 1.0)
     *
     * @return array<string,float>
     */
    public static function defaultUrgencyMultiplier(): array
    {
        return [
            'critical' => 0.25,
            'high' => 0.5,
            'medium' => 1.0,
            'low' => 2.0,
            // รองรับค่าเดิมที่บันทึกเป็นตัวเลข
            '4' => 0.25,
            '3' => 0.5,
            '2' => 1.0,
            '1' => 2.0,
        ];
    }

    /**
     * แคตตาล็อกบริการที่ตั้งค่าไว้ (fallback → ค่าเริ่มต้น)
     *
     * @return array<int,array<string,mixed>>
     */
    public function getServiceCatalog(): array
    {
        $cfg = $this->getConfig();
        $catalog = $cfg['service_catalog'] ?? null;
        return is_array($catalog) && !empty($catalog) ? $catalog : self::defaultServiceCatalog();
    }

    /**
     * ตัวคูณความเร่งด่วนที่ตั้งค่าไว้ (fallback → ค่าเริ่มต้น)
     *
     * @return array<string,float>
     */
    public function getUrgencyMultiplier(): array
    {
        $cfg = $this->getConfig();
        $mult = $cfg['urgency_multiplier'] ?? null;
        return is_array($mult) && !empty($mult) ? $mult : self::defaultUrgencyMultiplier();
    }
}

