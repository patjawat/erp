<?php
namespace app\modules\pm\models;
class StrategyGoal extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_goal}}'; }
    public function rules(): array { return [[['issue_id','code','name'],'required'],[['issue_id','sort_order'],'integer'],['is_active','boolean'],['code','string','max'=>50],['name','string']]; }
    public function attributeLabels(): array { return ['code'=>'รหัสเป้าประสงค์','name'=>'เป้าประสงค์','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function getIssue(){return $this->hasOne(StrategyIssue::class,['id'=>'issue_id']);}
    public function getIndicators(){return $this->hasMany(StrategyIndicator::class,['goal_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC]);}
}
