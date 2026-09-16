<?php

namespace app\modules\complaint\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveQuery;

/**
 * การดำเนินงาน (ขั้น 4) — timeline 1:หลาย ต่อเรื่องร้องเรียน
 *
 * @property int         $id
 * @property int         $complaint_id
 * @property string|null $action_date
 * @property int|null    $action_level
 * @property string|null $action_kind
 * @property string|null $title
 * @property string|null $detail
 * @property int|null    $action_by
 */
class ComplaintAction extends ComplaintActiveRecord
{
    /**
     * ประเภทการดำเนินงาน — คีย์คงที่ (เพื่อให้ KPI จับ milestone ได้แน่นอน) + ป้ายไทย
     * คีย์ start/review/reply ใช้คำนวณ CC03/CC04/CC05
     */
    public const KINDS = [
        'start' => 'เริ่มดำเนินการ',
        'review' => 'ตรวจสอบข้อเท็จจริง/RCA',
        'rrt' => 'ทีม RRT/ลงหน้างาน',
        'committee' => 'ประชุมคณะกรรมการ',
        'reply' => 'ตอบกลับผู้ร้อง',
        'meeting' => 'ประชุม/เยียวยา',
        'improve' => 'ปรับปรุงแก้ไข',
        'outcome' => 'สรุปผล',
    ];

    public static function tableName(): string
    {
        return '{{%complaint_action}}';
    }

    /** ป้ายไทยของ action_kind (รองรับค่าเดิมที่อาจเป็นข้อความไทยอยู่แล้ว) */
    public function kindLabel(): string
    {
        if ($this->action_kind === null || $this->action_kind === '') {
            return 'ดำเนินการ';
        }
        return self::KINDS[$this->action_kind] ?? $this->action_kind;
    }

    public function rules(): array
    {
        return [
            [['complaint_id'], 'required'],
            [['complaint_id', 'action_level', 'action_by'], 'integer'],
            [['action_date'], 'safe'],
            [['action_level'], 'in', 'range' => [1, 2, 3, 4], 'skipOnEmpty' => true],
            [['detail'], 'string'],
            [['title'], 'string', 'max' => 500],
            [['action_kind'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'action_date' => 'วันที่ดำเนินการ',
            'action_level' => 'ระดับ (L)',
            'action_kind' => 'ประเภทการดำเนินงาน',
            'title' => 'หัวข้อ',
            'detail' => 'รายละเอียด',
            'action_by' => 'ผู้ดำเนินการ',
        ];
    }

    public function getComplaint(): ActiveQuery
    {
        return $this->hasOne(Complaint::class, ['id' => 'complaint_id']);
    }

    public function getActor(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'action_by']);
    }
}
