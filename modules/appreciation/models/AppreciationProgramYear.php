<?php
namespace app\modules\appreciation\models;

use yii\db\ActiveRecord;

class AppreciationProgramYear extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    public static function tableName() { return '{{%appreciation_program_year}}'; }
    public static function statusLabels() { return [self::STATUS_DRAFT => 'แบบร่าง', self::STATUS_ACTIVE => 'กำลังใช้งาน', self::STATUS_CLOSED => 'ปิดรอบแล้ว']; }
    public function rules() { return [
        [['year', 'name', 'points_per_thank', 'start_at', 'end_at', 'status'], 'required'],
        [['year', 'points_per_thank'], 'integer', 'min' => 1], [['name'], 'string', 'max' => 255],
        [['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
        [['status'], 'in', 'range' => array_keys(self::statusLabels())],
        [['year'], 'unique'], [['end_at'], 'compare', 'compareAttribute' => 'start_at', 'operator' => '>='],
    ]; }
    public function beforeSave($insert) { if ($insert) $this->created_at = date('Y-m-d H:i:s'); $this->updated_at = date('Y-m-d H:i:s'); return parent::beforeSave($insert); }
    public function attributeLabels(){ return ['year'=>'ปี พ.ศ.','name'=>'ชื่อรอบปี','points_per_thank'=>'คะแนนพื้นฐานต่อคำขอบคุณ','start_at'=>'วันที่เริ่ม','end_at'=>'วันที่สิ้นสุด','status'=>'สถานะ']; }
    public static function active() { return static::find()->where(['status' => self::STATUS_ACTIVE])->andWhere(['<=', 'start_at', date('Y-m-d')])->andWhere(['>=', 'end_at', date('Y-m-d')])->one(); }
    public function getLevels() { return $this->hasMany(AppreciationLevel::class, ['program_year_id' => 'id'])->orderBy(['min_points' => SORT_ASC]); }
}
