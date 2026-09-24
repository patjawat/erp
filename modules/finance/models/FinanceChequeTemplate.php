<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * แม่แบบพิมพ์เช็ค แยกตามธนาคาร
 *
 * พิกัดฟิลด์เก็บใน layout_json เป็นเปอร์เซ็นต์ (0-100) ของขนาดแผ่นเช็ค
 * — รูปแบบเดียวกับโมดูล pdfTemplate เพื่อให้ FPDI overlay (เฟส 1) นำไปใช้ต่อได้ทันที
 *
 * @property int $id
 * @property string|null $bank_code
 * @property string $bank_name
 * @property string $name
 * @property string $page_width_mm
 * @property string $page_height_mm
 * @property string|null $layout_json
 * @property string|null $background_path
 * @property string $calibrate_offset_x
 * @property string $calibrate_offset_y
 * @property int $is_active
 */
class FinanceChequeTemplate extends ActiveRecord
{
    /** ฟิลด์มาตรฐานบนหน้าเช็ค — key => label */
    public const FIELDS = [
        'cheque_date' => 'วันที่สั่งจ่าย',
        'payee' => 'จ่ายให้ (ชื่อผู้รับ)',
        'amount_text' => 'จำนวนเงินตัวอักษร',
        'amount_number' => 'จำนวนเงินตัวเลข',
        'ac_payee' => 'A/C PAYEE ONLY (ขีดคร่อม)',
        'strike_bearer' => 'ขีดฆ่า "หรือผู้ถือ" (เส้น)',
    ];

    /** ฟิลด์ที่วาดเป็น "เส้น" ไม่ใช่ข้อความ (ช่อง %ช่อง = ความยาวเส้น % ของแผ่น) */
    public const LINE_FIELDS = ['strike_bearer'];

    public static function tableName(): string
    {
        return '{{%finance_cheque_template}}';
    }

    /** พิกัดตั้งต้นสำหรับแม่แบบใหม่ (ค่าเดา ต้องปรับกับเช็คจริงบนหน้า calibrate) */
    public static function defaultLayout(): array
    {
        return [
            ['key' => 'cheque_date',   'x' => 78, 'y' => 14, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'payee',         'x' => 22, 'y' => 33, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'amount_text',   'x' => 17, 'y' => 47, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'amount_number', 'x' => 90, 'y' => 47, 'font_size' => 18, 'align' => 'R', 'bold' => 1, 'enabled' => 1],
            ['key' => 'ac_payee',      'x' => 40, 'y' => 60, 'font_size' => 14, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            // strike_bearer: x = ปลายเส้น (ขวาสุด), y = ระดับบรรทัดจ่าย ; เริ่มลากจากท้ายชื่อผู้รับอัตโนมัติ
            ['key' => 'strike_bearer', 'x' => 99, 'y' => 30, 'font_size' => 14, 'align' => 'L', 'bold' => 0, 'pitch' => 0, 'enabled' => 1],
        ];
    }

    public function rules(): array
    {
        return [
            [['bank_name', 'name'], 'required'],
            [['is_active'], 'integer'],
            [['page_width_mm', 'page_height_mm', 'calibrate_offset_x', 'calibrate_offset_y'], 'number'],
            [['layout_json'], 'safe'],
            [['bank_code'], 'string', 'max' => 20],
            [['bank_name'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 150],
            [['background_path', 'note'], 'string', 'max' => 255],
            [['is_active'], 'default', 'value' => 1],
            [['page_width_mm'], 'default', 'value' => 178],
            [['page_height_mm'], 'default', 'value' => 82],
            [['calibrate_offset_x', 'calibrate_offset_y'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'bank_code' => 'รหัสธนาคาร',
            'bank_name' => 'ธนาคาร',
            'name' => 'ชื่อแม่แบบ',
            'page_width_mm' => 'กว้าง (มม.)',
            'page_height_mm' => 'สูง (มม.)',
            'calibrate_offset_x' => 'ชดเชยแนวนอน (มม.)',
            'calibrate_offset_y' => 'ชดเชยแนวตั้ง (มม.)',
            'is_active' => 'ใช้งาน',
            'note' => 'หมายเหตุ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null;
        $now = time();
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }

    /** ผังฟิลด์ที่ decode แล้ว — [{key,x,y,font_size,align,bold,enabled}, ...] */
    public function layout(): array
    {
        if (empty($this->layout_json)) {
            return [];
        }
        try {
            $data = Json::decode($this->layout_json);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** id => label สำหรับ dropdown เฉพาะแม่แบบที่ใช้งาน */
    public static function activeList(): array
    {
        $rows = self::find()->where(['is_active' => 1])->orderBy(['bank_name' => SORT_ASC, 'name' => SORT_ASC])->all();
        return ArrayHelper::map($rows, 'id', fn ($t) => $t->bank_name . ' — ' . $t->name);
    }
}
