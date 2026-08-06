<?php
namespace app\modules\pm\models;
class StrategyMeasure extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_measure}}'; }
    public function rules(): array { return [[['goal_id','fiscal_year','name'],'required'],[['goal_id','fiscal_year','sort_order'],'integer'],['is_active','boolean'],['name','string'],['code','string','max'=>50]]; }
    public function attributeLabels(): array { return ['code'=>'รหัสมาตรการ','name'=>'มาตรการ/กลยุทธ์','fiscal_year'=>'ปีงบประมาณ (พ.ศ.)','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function getGoal(){return $this->hasOne(StrategyGoal::class,['id'=>'goal_id']);}
}
