<?php
namespace app\modules\pm\models;
class StrategySuccessFactor extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_success_factor}}'; }
    public function rules(): array { return [[['goal_id','name'],'required'],[['goal_id','sort_order'],'integer'],['is_active','boolean'],['name','string'],['code','string','max'=>50],['factor_type','in','range'=>['success_factor','rca']]]; }
    public function attributeLabels(): array { return ['code'=>'รหัส','name'=>'รายละเอียด','factor_type'=>'ประเภท','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function richTextAttributes(): array { return ['name']; }
    public function getGoal(){return $this->hasOne(StrategyGoal::class,['id'=>'goal_id']);}
}
