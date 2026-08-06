<?php
namespace app\modules\pm\models;
class StrategyIndicatorValue extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_indicator_value}}'; }
    public function rules(): array { return [[['indicator_id','fiscal_year'],'required'],[['indicator_id','fiscal_year'],'integer'],[['baseline_value','target_value','actual_value'],'number'],[['note'],'string'],['operator','string','max'=>10],[['indicator_id','fiscal_year'],'unique','targetAttribute'=>['indicator_id','fiscal_year']]]; }
    public function attributeLabels(): array { return ['fiscal_year'=>'ปีงบประมาณ (พ.ศ.)','baseline_value'=>'ค่าฐาน','target_value'=>'ค่าเป้าหมาย','actual_value'=>'ผลงานจริง','operator'=>'เงื่อนไข','note'=>'หมายเหตุ']; }
    public function getIndicator(){return $this->hasOne(StrategyIndicator::class,['id'=>'indicator_id']);}
}
