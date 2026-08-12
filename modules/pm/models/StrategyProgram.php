<?php
namespace app\modules\pm\models;
class StrategyProgram extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_program}}'; }
    public function rules(): array { return [[['plan_id','fiscal_year','name'],'required'],[['plan_id','measure_id','fiscal_year','owner_org_id','sort_order'],'integer'],['is_active','boolean'],['name','string'],[['code'],'string','max'=>50],['owner_text','string','max'=>255]]; }
    public function attributeLabels(): array { return ['code'=>'รหัสแผนงาน','name'=>'ชื่อแผนงานหลัก','fiscal_year'=>'ปีงบประมาณ (พ.ศ.)','measure_id'=>'มาตรการอ้างอิง','owner_text'=>'ผู้รับผิดชอบ','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function richTextAttributes(): array { return ['name']; }
    public function getPlan(){return $this->hasOne(StrategyPlan::class,['id'=>'plan_id']);}
    public function getMeasure(){return $this->hasOne(StrategyMeasure::class,['id'=>'measure_id']);}
}
