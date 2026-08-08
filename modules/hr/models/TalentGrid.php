<?php

namespace app\modules\hr\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * การจัดวางบุคลากรในตารางจำแนกศักยภาพ 9 Box ประจำปีงบประมาณ
 *
 * แกนนอน = ผลการปฏิบัติงาน (performance) แกนตั้ง = ศักยภาพ (potential)
 * เลขกล่องนับจากซ้ายล่างไปขวาบน: box_no = (potential - 1) * 3 + performance
 *
 * @property int $id
 * @property int $fiscal_year ปีงบประมาณ พ.ศ.
 * @property int $emp_id
 * @property int $performance 1=ต่ำ 2=ปานกลาง 3=สูง
 * @property int $potential 1=ต่ำ 2=ปานกลาง 3=สูง
 * @property int $box_no 1-9
 * @property string|null $note
 * @property string|null $assessed_at
 * @property Employees $employee
 */
class TalentGrid extends ActiveRecord
{
    public const LEVEL_LOW = 1;
    public const LEVEL_MEDIUM = 2;
    public const LEVEL_HIGH = 3;

    public const ZONE_RISK = 'risk';
    public const ZONE_WATCH = 'watch';
    public const ZONE_SOLID = 'solid';
    public const ZONE_STAR = 'star';

    public static function tableName()
    {
        return '{{%hr_talent_grid}}';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => static fn () => date('Y-m-d H:i:s')],
            ['class' => BlameableBehavior::class],
        ];
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'emp_id', 'performance', 'potential'], 'required'],
            [['fiscal_year', 'emp_id', 'performance', 'potential', 'box_no', 'created_by', 'updated_by'], 'integer'],
            [['performance', 'potential'], 'in', 'range' => [self::LEVEL_LOW, self::LEVEL_MEDIUM, self::LEVEL_HIGH]],
            [['note'], 'string'],
            [['assessed_at', 'created_at', 'updated_at'], 'safe'],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['emp_id'], 'unique', 'targetAttribute' => ['fiscal_year', 'emp_id'], 'message' => 'บุคลากรคนนี้ถูกจัดวางในปีงบประมาณนี้แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'emp_id' => 'บุคลากร',
            'performance' => 'ผลการปฏิบัติงาน',
            'potential' => 'ศักยภาพ',
            'box_no' => 'กล่อง',
            'note' => 'บันทึกเพิ่มเติม',
            'assessed_at' => 'วันที่ประเมิน',
        ];
    }

    public function beforeSave($insert)
    {
        // box_no เป็นค่าที่คำนวณได้เสมอ ไม่ให้กรอกเองเพื่อกันข้อมูลขัดกัน
        $this->box_no = self::boxNo((int) $this->performance, (int) $this->potential);
        return parent::beforeSave($insert);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public static function boxNo(int $performance, int $potential): int
    {
        return ($potential - 1) * 3 + $performance;
    }

    public static function levelOptions(): array
    {
        return [
            self::LEVEL_LOW => 'ต่ำ',
            self::LEVEL_MEDIUM => 'ปานกลาง',
            self::LEVEL_HIGH => 'สูง',
        ];
    }

    /** นิยามกล่องทั้ง 9 — ชื่อกลุ่ม เกณฑ์ที่ตกกล่องนี้ กลุ่มสรุป และแนวทางดำเนินการ */
    public static function boxMeta(): array
    {
        return [
            1 => ['name' => 'ต้องปรับปรุงเร่งด่วน', 'criteria' => 'ผลงานต่ำ ศักยภาพต่ำ', 'zone' => self::ZONE_RISK,
                'action' => 'จัดทำแผนพัฒนาเร่งด่วน หรือทบทวนการมอบหมายงาน'],
            2 => ['name' => 'ผลงานไม่สม่ำเสมอ', 'criteria' => 'ผลงานปานกลาง ศักยภาพต่ำ', 'zone' => self::ZONE_RISK,
                'action' => 'กำหนดเป้าหมายระยะสั้นและติดตามผลอย่างใกล้ชิด'],
            3 => ['name' => 'ผู้ปฏิบัติงานที่ไว้วางใจได้', 'criteria' => 'ผลงานสูง ศักยภาพต่ำ', 'zone' => self::ZONE_WATCH,
                'action' => 'คงไว้ในงานที่ชำนาญ และเสริมความเชี่ยวชาญเฉพาะทาง'],
            4 => ['name' => 'ต้องพัฒนาผลการปฏิบัติงาน', 'criteria' => 'ผลงานต่ำ ศักยภาพปานกลาง', 'zone' => self::ZONE_WATCH,
                'action' => 'วิเคราะห์สาเหตุที่ผลงานยังไม่เป็นไปตามเป้า และให้คำปรึกษารายบุคคล'],
            5 => ['name' => 'กำลังหลักขององค์กร', 'criteria' => 'ผลงานปานกลาง ศักยภาพปานกลาง', 'zone' => self::ZONE_SOLID,
                'action' => 'พัฒนาอย่างต่อเนื่องและมอบหมายงานที่ท้าทายมากขึ้น'],
            6 => ['name' => 'ผู้ปฏิบัติงานดีเด่น', 'criteria' => 'ผลงานสูง ศักยภาพปานกลาง', 'zone' => self::ZONE_SOLID,
                'action' => 'มอบหมายเป็นพี่เลี้ยงและขยายขอบเขตความรับผิดชอบ'],
            7 => ['name' => 'ศักยภาพสูงแต่ผลงานยังไม่ปรากฏ', 'criteria' => 'ผลงานต่ำ ศักยภาพสูง', 'zone' => self::ZONE_WATCH,
                'action' => 'ทบทวนความเหมาะสมของตำแหน่งงานและปัจจัยแวดล้อมในการทำงาน'],
            8 => ['name' => 'บุคลากรศักยภาพสูง', 'criteria' => 'ผลงานปานกลาง ศักยภาพสูง', 'zone' => self::ZONE_STAR,
                'action' => 'เร่งรัดการพัฒนาเพื่อเตรียมความพร้อมสู่ตำแหน่งที่สูงขึ้น'],
            9 => ['name' => 'ผู้นำในอนาคต', 'criteria' => 'ผลงานสูง ศักยภาพสูง', 'zone' => self::ZONE_STAR,
                'action' => 'บรรจุในแผนสืบทอดตำแหน่งและวางมาตรการรักษาไว้กับองค์กร'],
        ];
    }

    /** กลุ่มสรุปภาพรวม เรียงจากกลุ่มที่ต้องดูแลไปถึงกลุ่มศักยภาพสูง */
    public static function zoneMeta(): array
    {
        return [
            self::ZONE_RISK => ['label' => 'กลุ่มเสี่ยง', 'hint' => 'ต้องดำเนินการโดยเร็ว', 'color' => '#ef4444'],
            self::ZONE_WATCH => ['label' => 'กลุ่มเฝ้าติดตาม', 'hint' => 'ติดตามและให้คำปรึกษา', 'color' => '#f7cb45'],
            self::ZONE_SOLID => ['label' => 'กลุ่มมั่นคง', 'hint' => 'กำลังหลักในการปฏิบัติงาน', 'color' => '#4ade80'],
            self::ZONE_STAR => ['label' => 'กลุ่มศักยภาพสูง', 'hint' => 'เตรียมสู่ตำแหน่งที่สูงขึ้น', 'color' => '#16a34a'],
        ];
    }

    public function boxInfo(): array
    {
        return self::boxMeta()[(int) $this->box_no] ?? self::boxMeta()[5];
    }
}
