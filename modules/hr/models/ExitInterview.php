<?php

namespace app\modules\hr\models;

class ExitInterview extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview}}'; }
    public function rules()
    {
        return [
            [['emp_id', 'version_id', 'employee_name_snapshot'], 'required'],
            [['emp_id', 'version_id', 'interviewer_id', 'department_id_snapshot'], 'integer'],
            [['exit_date', 'interview_date', 'join_date_snapshot', 'submitted_at', 'consent_at'], 'safe'],
            [['employee_name_snapshot', 'department_name_snapshot', 'position_name_snapshot', 'employee_type_snapshot'], 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['response_source', 'in', 'range' => ['hr_interview', 'self_service', 'excel_import']],
            ['exit_type', 'in', 'range' => array_keys(self::exitTypeOptions())],
        ];
    }
    public static function statusOptions(): array { return ['pending' => 'รอดำเนินการ', 'draft' => 'บันทึกร่าง', 'submitted' => 'ส่งแล้ว', 'declined' => 'ไม่ประสงค์ให้ข้อมูล', 'cancelled' => 'ยกเลิก']; }
    public static function exitTypeOptions(): array { return ['resignation' => 'ลาออก', 'retirement' => 'เกษียณ', 'contract_end' => 'สิ้นสุดสัญญา', 'transfer' => 'ย้ายหรือโอน', 'termination' => 'เลิกจ้างหรือให้ออก', 'other' => 'อื่น ๆ']; }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'emp_id']); }
    public function getVersion() { return $this->hasOne(ExitInterviewTemplateVersion::class, ['id' => 'version_id']); }
    public function getAnswers() { return $this->hasMany(ExitInterviewAnswer::class, ['interview_id' => 'id']); }
    public function getLinks() { return $this->hasMany(ExitInterviewLink::class, ['interview_id' => 'id'])->orderBy(['id' => SORT_DESC]); }
    public function getStatusLabel(): string { return self::statusOptions()[$this->status] ?? $this->status; }
}
