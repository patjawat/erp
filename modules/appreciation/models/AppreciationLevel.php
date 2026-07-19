<?php
namespace app\modules\appreciation\models;
use yii\db\ActiveRecord;
class AppreciationLevel extends ActiveRecord
{
    public static function tableName() { return '{{%appreciation_level}}'; }
    public function rules() { return [[['program_year_id','name','min_points'], 'required'], [['program_year_id','min_points','sort_order'], 'integer', 'min' => 0], [['name'], 'string', 'max' => 100], [['color'], 'string', 'max' => 20]]; }
    public function getProgramYear() { return $this->hasOne(AppreciationProgramYear::class, ['id' => 'program_year_id']); }
    public function attributeLabels(){ return ['program_year_id'=>'รอบปี','name'=>'ชื่อระดับ','min_points'=>'คะแนนขั้นต่ำ','color'=>'สีประจำระดับ','sort_order'=>'ลำดับ']; }
}
