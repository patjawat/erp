<?php
namespace app\modules\pm\models;
class StrategyImportBatch extends StrategyRecord {
    public static function tableName(): string{return '{{%pm_strategy_import_batch}}';}
    public function rules():array{return [[['plan_id','original_name'],'required'],[['plan_id','total_rows','valid_rows','error_rows'],'integer'],[['summary_json'],'safe'],['original_name','string','max'=>255],['status','in','range'=>['staged','imported','cancelled']]];}
    public function getPlan(){return $this->hasOne(StrategyPlan::class,['id'=>'plan_id']);}
    public function getRows(){return $this->hasMany(StrategyImportRow::class,['batch_id'=>'id'])->orderBy(['sheet_name'=>SORT_ASC,'row_no'=>SORT_ASC]);}
    public function beforeSave($insert):bool{if(is_array($this->summary_json))$this->summary_json=json_encode($this->summary_json,JSON_UNESCAPED_UNICODE);return parent::beforeSave($insert);}
    public function summary():array{return is_array($this->summary_json)?$this->summary_json:(json_decode((string)$this->summary_json,true)?:[]);}
}
