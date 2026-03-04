<?php

namespace app\modules\helpdesk2\models;

use Yii;

/**
 * Model สำหรับเก็บ config แบบฟอร์มใบส่งซ่อม PDF
 * ใช้ตาราง `categorise` ร่วม — name='helpdesk2_repair_form', code='default'
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $title
 * @property mixed $data_json
 * @property int|null $active
 */
class RepairFormSetting extends \yii\db\ActiveRecord
{
    const SETTING_NAME = 'helpdesk2_repair_form';
    const SETTING_CODE = 'default';

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

    /**
     * ดึง (หรือสร้าง) record config ใบส่งซ่อม
     */
    public static function getRecord(): self
    {
        $record = static::findOne(['name' => self::SETTING_NAME, 'code' => self::SETTING_CODE]);
        if (!$record) {
            $record = new static();
            $record->name      = self::SETTING_NAME;
            $record->code      = self::SETTING_CODE;
            $record->title     = 'แบบฟอร์มใบส่งซ่อม';
            $record->data_json = json_encode(['items' => []]);
            $record->active    = 0;
            $record->save(false);
        }
        return $record;
    }

    /**
     * ตรวจสอบว่าเปิดใช้งานหรือไม่ (active = 1)
     */
    public static function isEnabled(): bool
    {
        $record = static::findOne(['name' => self::SETTING_NAME, 'code' => self::SETTING_CODE]);
        return $record && (int) $record->active === 1;
    }

    /**
     * สลับสถานะเปิด/ปิด แล้วคืนค่าสถานะใหม่
     */
    public function toggleEnabled(): int
    {
        $this->active = (int) $this->active === 1 ? 0 : 1;
        $this->save(false);
        return (int) $this->active;
    }

    /**
     * ดึง config array จาก data_json
     */
    public function getConfig(): array
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        return is_array($json) ? $json : [];
    }

    /**
     * บันทึก items array ลงใน data_json
     */
    public function saveItems(array $items): bool
    {
        $config          = $this->getConfig();
        $config['items'] = $items;
        $this->data_json = json_encode($config);
        return $this->save(false);
    }

    /**
     * รายการฟิลด์เริ่มต้นสำหรับใบส่งซ่อม
     */
    public static function defaultFields(): array
    {
        return [
            'emp_fullname'   => ['label' => 'ชื่อ-นามสกุลผู้แจ้ง',      'x' => 30,  'y' => 50,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'department'     => ['label' => 'หน่วยงาน/แผนก',             'x' => 30,  'y' => 58,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'repair_number'  => ['label' => 'รหัสงานซ่อม',               'x' => 130, 'y' => 50,  'fontSize' => 15, 'bold' => 1, 'enabled' => 1],
            'device_type'    => ['label' => 'ประเภทอุปกรณ์',              'x' => 30,  'y' => 66,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'asset_number'   => ['label' => 'หมายเลขครุภัณฑ์',           'x' => 30,  'y' => 74,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'problem_detail' => ['label' => 'รายละเอียดปัญหา/รายการซ่อม', 'x' => 30, 'y' => 82,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'repair_group'   => ['label' => 'หน่วยงานรับซ่อม',            'x' => 30,  'y' => 90,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'urgency'        => ['label' => 'ความเร่งด่วน',               'x' => 30,  'y' => 98,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'contact_phone'  => ['label' => 'เบอร์ติดต่อ',                'x' => 30,  'y' => 106, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'create_date'    => ['label' => 'วันที่แจ้ง',                  'x' => 30,  'y' => 114, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
        ];
    }
}
