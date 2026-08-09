<?php
namespace app\modules\pm\models;
class StrategyIndicator extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_indicator}}'; }
    public function rules(): array { return [[['plan_id','code','name','level'],'required'],[['plan_id','goal_id','parent_id','sort_order'],'integer'],['is_active','boolean'],[['name'],'string'],[['code'],'string','max'=>50],['unit','string','max'=>100],['level','in','range'=>array_keys(self::levelList())]]; }
    public function attributeLabels(): array { return ['code'=>'รหัสตัวชี้วัด','name'=>'ชื่อตัวชี้วัด','level'=>'ระดับ','unit'=>'หน่วยนับ','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public static function levelList(): array { return ['ministry'=>'กระทรวง','region'=>'เขตสุขภาพ','province'=>'จังหวัด','hospital'=>'โรงพยาบาล']; }
    public function getPlan(){return $this->hasOne(StrategyPlan::class,['id'=>'plan_id']);}
    public function getGoal(){return $this->hasOne(StrategyGoal::class,['id'=>'goal_id']);}
    public function getParent(){return $this->hasOne(self::class,['id'=>'parent_id']);}
    /** ตัวชี้วัดรองภายใต้ตัวชี้วัดหลักนี้ */
    public function getChildren(){return $this->hasMany(self::class,['parent_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'code'=>SORT_ASC]);}
    /** กลยุทธ์ที่ใช้ขับเคลื่อนตัวชี้วัดนี้ */
    public function getTactics(){return $this->hasMany(StrategyTactic::class,['indicator_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]);}
    public function getYears(){return $this->hasMany(StrategyIndicatorYear::class,['indicator_id'=>'id'])->orderBy(['fiscal_year'=>SORT_ASC]);}
    public function yearEntry(int $fiscalYear): ?StrategyIndicatorYear { return StrategyIndicatorYear::findOne(['indicator_id'=>$this->id,'fiscal_year'=>$fiscalYear]); }
}
