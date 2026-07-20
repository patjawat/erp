<?php
namespace app\modules\appreciation\models;
use yii\db\ActiveRecord;
class AppreciationValue extends ActiveRecord
{
    public static function tableName() { return '{{%appreciation_value}}'; }
    public function rules() { return [[['code','name','core_value_name'], 'required'], [['points','sort_order'], 'integer', 'min' => 0], [['is_active'], 'boolean'], [['code','core_value_code'], 'string', 'max' => 64], [['name'], 'string', 'max' => 120], [['core_value_name'], 'string', 'max' => 160], [['icon'], 'string', 'max' => 32], [['description'], 'string', 'max' => 500], [['code'], 'unique']]; }
    public static function activeItems() { return static::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(); }
    public function attributeLabels(){ return ['code'=>'รหัสคำเรียกง่าย','name'=>'คำที่บุคลากรเห็น','core_value_code'=>'รหัสค่านิยมองค์กร','core_value_name'=>'ค่านิยมองค์กรที่เชื่อมโยง','icon'=>'ไอคอน','description'=>'คำอธิบาย','points'=>'คะแนน','sort_order'=>'ลำดับ','is_active'=>'เปิดให้เลือก']; }
}
