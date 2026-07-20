<?php
namespace app\modules\appreciation\models;
use yii\db\ActiveRecord;
class AppreciationReward extends ActiveRecord
{
    public $imageFile;
    public static function tableName() { return '{{%appreciation_reward}}'; }
    public function rules() { return [[['program_year_id','name','points_cost','stock_qty'], 'required'], [['program_year_id','points_cost','stock_qty'], 'integer', 'min' => 0], [['is_active'], 'boolean'], [['description'], 'string'], [['name'], 'string', 'max' => 255], [['image_url'], 'string', 'max' => 500], [['imageFile'],'file','skipOnEmpty'=>true,'extensions'=>['png','jpg','jpeg','webp'],'checkExtensionByMimeType'=>true,'maxSize'=>5*1024*1024]]; }
    public function beforeSave($insert) { if ($insert) $this->created_at = date('Y-m-d H:i:s'); $this->updated_at = date('Y-m-d H:i:s'); return parent::beforeSave($insert); }
    public function getProgramYear() { return $this->hasOne(AppreciationProgramYear::class, ['id' => 'program_year_id']); }
    public function attributeLabels(){ return ['program_year_id'=>'รอบปี','name'=>'ชื่อของรางวัล','description'=>'รายละเอียด','image_url'=>'รูปภาพ','imageFile'=>'ภาพของรางวัล','points_cost'=>'คะแนนที่ใช้แลก','stock_qty'=>'จำนวนคงเหลือ','is_active'=>'เปิดให้แลก']; }
}
