<?php
namespace app\modules\pm\models;

/** มาตรการ — การดำเนินการรูปธรรมภายใต้กลยุทธ์หนึ่ง */
class StrategyMeasure extends StrategyRecord {
    public static function tableName(): string { return '{{%pm_strategy_measure}}'; }
    public function rules(): array { return [[['goal_id','fiscal_year','name'],'required'],[['goal_id','tactic_id','fiscal_year','sort_order'],'integer'],['is_active','boolean'],['name','string'],['code','string','max'=>50],['tactic_id','exist','targetClass'=>StrategyTactic::class,'targetAttribute'=>'id','skipOnEmpty'=>true,'message'=>'ไม่พบกลยุทธ์ที่เลือก']]; }
    public function attributeLabels(): array { return ['code'=>'รหัสมาตรการ','name'=>'มาตรการ','tactic_id'=>'กลยุทธ์','fiscal_year'=>'ปีงบประมาณ (พ.ศ.)','sort_order'=>'ลำดับ','is_active'=>'ใช้งาน']; }
    public function richTextAttributes(): array { return ['name']; }

    /** เป้าประสงค์ยึดตามกลยุทธ์เสมอเมื่อระบุกลยุทธ์ไว้ กันข้อมูลสองชั้นไม่ตรงกัน */
    public function beforeSave($insert): bool
    {
        if ($this->tactic_id && ($goalId = $this->tactic?->goal_id)) $this->goal_id = $goalId;
        return parent::beforeSave($insert);
    }

    public function getGoal(){return $this->hasOne(StrategyGoal::class,['id'=>'goal_id']);}
    public function getTactic(){return $this->hasOne(StrategyTactic::class,['id'=>'tactic_id']);}
}
