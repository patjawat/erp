<?php
namespace app\modules\pm\models;
class StrategyMission extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_mission}}'; }
    public function rules(): array { return [[['plan_id','code','name'],'required'],[['plan_id','sort_order'],'integer'],['is_active','boolean'],['code','string','max'=>50],['name','string']]; }
    public function attributeLabels(): array { return ['code'=>'รหัสพันธกิจ','name'=>'พันธกิจ','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function getPlan(){return $this->hasOne(StrategyPlan::class,['id'=>'plan_id']);}
    public function getIssues(){return $this->hasMany(StrategyIssue::class,['mission_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC]);}
}
