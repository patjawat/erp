<?php
namespace app\modules\attendance\models;

class WorkSchedule extends \yii\db\ActiveRecord
{
    public static function tableName() { return '{{%attendance_schedule}}'; }
    public function rules()
    {
        return [
            ['name','trim'],
            [['name', 'start_time', 'end_time', 'weekdays', 'grace_minutes', 'window_minutes'], 'required'],
            ['name', 'string', 'max' => 150],
            [['start_time', 'end_time'], 'match', 'pattern' => '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D'],
            ['end_time', 'compare', 'compareAttribute' => 'start_time', 'operator' => '>', 'message' => 'เวลาปกติต้องสิ้นสุดหลังเวลาเริ่มในวันเดียวกัน'],
            ['weekdays', 'match', 'pattern' => '/^[1-7](?:,[1-7])*$/D'],
            ['holidays', 'string', 'max' => 20000],
            ['holidays', function ($attribute) {
                foreach (preg_split('/[\s,]+/', trim((string)$this->$attribute), -1, PREG_SPLIT_NO_EMPTY) as $date) {
                    $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                    if (!$d || $d->format('Y-m-d') !== $date) $this->addError($attribute, 'วันหยุดต้องเป็น YYYY-MM-DD หนึ่งวันต่อบรรทัด');
                }
            }],
            ['grace_minutes', 'integer', 'min' => 0, 'max' => 120],
            ['window_minutes', 'integer', 'min' => 1, 'max' => 360],
        ];
    }
    public function attributeLabels() { return ['name'=>'ชื่อชุดเวลา','start_time'=>'เริ่มงาน','end_time'=>'เลิกงาน','weekdays'=>'วันทำงาน (1=จันทร์ ถึง 7=อาทิตย์)','holidays'=>'วันหยุดเพิ่มเติม','grace_minutes'=>'ผ่อนผันสาย (นาที)','window_minutes'=>'ช่วงจับคู่ก่อน/หลังจุดเริ่มหรือจบงาน (นาที)']; }
    public function getWeekdayLabel(): string
    {
        $names=[1=>'จันทร์',2=>'อังคาร',3=>'พุธ',4=>'พฤหัสบดี',5=>'ศุกร์',6=>'เสาร์',7=>'อาทิตย์'];
        return implode(', ',array_map(fn($day)=>$names[(int)$day]??'',explode(',',$this->weekdays)));
    }
}
