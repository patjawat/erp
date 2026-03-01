<?php

namespace app\modules\leave\models;

use Yii;
use app\components\AppHelper;

/**
 * โมเดลสำหรับฟอร์มสร้างใบลา (ใช้กับ Kartik ActiveForm + Ajax Validation)
 */
class LeaveCreateForm extends \yii\base\Model
{
    public $leave_type_id;
    public $date_start;
    public $date_end;
    public $reason;
    public $address;
    public $contact_phone;
    public $place_go;
    public $leave_time_type = 1;
    /** @var int|null ใช้ในการตรวจสอบวันลาซ้ำ (เซ็ตจาก controller) */
    public $emp_id;

    public function rules()
    {
        return [
            [['leave_type_id', 'date_start', 'date_end', 'reason', 'address'], 'required'],
            [['leave_type_id'], 'string', 'max' => 32],
            [['reason', 'address', 'contact_phone', 'place_go'], 'string'],
            [['leave_time_type'], 'number', 'min' => 0.5, 'max' => 1],
            [['leave_time_type'], 'default', 'value' => 1],
            [['emp_id'], 'safe'],
            [['date_end'], 'validateDateRange'],
            [['date_start'], 'validateOverlap'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'leave_type_id' => 'ประเภทการลา',
            'date_start' => 'ตั้งแต่วันที่',
            'date_end' => 'ถึงวันที่',
            'reason' => 'สาเหตุการลา',
            'address' => 'ที่อยู่ที่ติดต่อได้',
            'contact_phone' => 'เบอร์โทรติดต่อ',
            'place_go' => 'สถานที่ไป',
            'leave_time_type' => 'ลักษณะการลา',
        ];
    }

    /**
     * วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น (รองรับวันที่ภาษาไทย)
     */
    public function validateDateRange($attribute, $params)
    {
        $start = trim((string) $this->date_start);
        $end = trim((string) $this->date_end);
        if ($start === '' || $end === '') {
            return;
        }
        $startG = AppHelper::convertToGregorian($start);
        $endG = AppHelper::convertToGregorian($end);
        if (!$startG || !$endG) {
            return;
        }
        if (strtotime($endG) < strtotime($startG)) {
            $this->addError($attribute, 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
        }
    }

    /**
     * ตรวจสอบวันลาซ้ำกับรายการเดิม (ต้องเซ็ต emp_id ก่อน validate)
     */
    public function validateOverlap($attribute, $params)
    {
        if ($this->emp_id === null || $this->emp_id === '') {
            return;
        }
        $start = trim((string) $this->date_start);
        $end = trim((string) $this->date_end);
        if ($start === '' || $end === '') {
            return;
        }
        $startG = AppHelper::convertToGregorian($start);
        $endG = AppHelper::convertToGregorian($end);
        if (!$startG || !$endG || strtotime($endG) < strtotime($startG)) {
            return;
        }
        $exists = Leave::find()
            ->where(['emp_id' => (int) $this->emp_id])
            ->andWhere(['<=', 'date_start', $endG])
            ->andWhere(['>=', 'date_end', $startG])
            ->andWhere(['NOT IN', 'status', ['Cancel']])
            ->exists();
        if ($exists) {
            $this->addError($attribute, 'คุณลาในวันนี้แล้ว (ช่วงวันที่ซ้ำกับรายการเดิม)');
        }
    }
}
