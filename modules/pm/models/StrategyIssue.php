<?php
namespace app\modules\pm\models;
class StrategyIssue extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_issue}}'; }
    public function rules(): array { return [[['mission_id','code','name'],'required'],[['mission_id','sort_order'],'integer'],['is_active','boolean'],['code','string','max'=>50],['name','string'],['code','unique','targetAttribute'=>['mission_id','code'],'message'=>'รหัสประเด็นนี้ถูกใช้แล้วในพันธกิจเดียวกัน']]; }
    public function attributeLabels(): array { return ['code'=>'รหัสประเด็น','name'=>'ประเด็นยุทธศาสตร์','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function getMission(){return $this->hasOne(StrategyMission::class,['id'=>'mission_id']);}
    public function getGoals(){return $this->hasMany(StrategyGoal::class,['issue_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC]);}
}
