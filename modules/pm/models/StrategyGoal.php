<?php
namespace app\modules\pm\models;
class StrategyGoal extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_goal}}'; }
    public function rules(): array { return [[['issue_id','code','name'],'required'],[['issue_id','sort_order'],'integer'],['is_active','boolean'],['code','string','max'=>50],['name','string'],['code','unique','targetAttribute'=>['issue_id','code'],'message'=>'รหัสเป้าประสงค์นี้ถูกใช้แล้วในประเด็นยุทธศาสตร์เดียวกัน']]; }
    public function attributeLabels(): array { return ['code'=>'รหัสเป้าประสงค์','name'=>'เป้าประสงค์','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function getIssue(){return $this->hasOne(StrategyIssue::class,['id'=>'issue_id']);}
    /** กลยุทธ์ทั้งหมดใต้เป้าประสงค์นี้ (ผ่านตัวชี้วัด) — ใช้ตอนรวมยอด ไม่ใช่ตอนแสดงต้นไม้ */
    public function getTactics(){return $this->hasMany(StrategyTactic::class,['goal_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]);}
    /** กลยุทธ์เก่าที่ยังไม่ได้ผูกตัวชี้วัด — ต้องแสดงให้ย้ายได้ ไม่ให้ตกหล่น */
    public function getOrphanTactics(){return $this->hasMany(StrategyTactic::class,['goal_id'=>'id'])->andOnCondition(['indicator_id'=>null])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]);}
    public function getFactors(){return $this->hasMany(StrategySuccessFactor::class,['goal_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]);}
    public function getIndicators(){return $this->hasMany(StrategyIndicator::class,['goal_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'code'=>SORT_ASC]);}
}
