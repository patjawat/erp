<?php

namespace app\modules\hr\models;

class ProbationTemplate extends ProbationActiveRecord
{
    public static function tableName() { return '{{%probation_template}}'; }
    public function rules() { return [
        [['position_group_id', 'name'], 'required'],
        [['position_group_id', 'revision_no'], 'integer'],
        [['description', 'effective_date'], 'safe'],
        [['name'], 'string', 'max' => 200],
        [['status'], 'in', 'range' => array_keys(self::statusOptions())],
        [['revision_no'], 'unique',
            'targetAttribute' => ['position_group_id', 'revision_no'],
            'message' => 'กลุ่มวิชาชีพนี้มี Revision ดังกล่าวแล้ว',
        ],
    ]; }
    public static function statusOptions() { return ['draft' => 'ฉบับร่าง', 'active' => 'ใช้งาน', 'retired' => 'เลิกใช้งาน']; }
    public function getItems() { return $this->hasMany(ProbationTemplateItem::class, ['template_id' => 'id'])->andWhere(['active' => 1])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
    public function getPositionGroup() { return $this->hasOne(EmployeePositionGroup::class, ['id' => 'position_group_id']); }
    public function getStatusLabel() { return self::statusOptions()[$this->status] ?? $this->status; }
    public function attributeLabels() { return ['name' => 'ชื่อ Template', 'position_group_id' => 'กลุ่มวิชาชีพ', 'revision_no' => 'Revision', 'description' => 'คำอธิบาย']; }
}
