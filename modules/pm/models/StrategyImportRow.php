<?php
namespace app\modules\pm\models;
use yii\db\ActiveRecord;
class StrategyImportRow extends ActiveRecord {
    public static function tableName(): string{return '{{%pm_strategy_import_row}}';}
    public function rules():array{return [[['batch_id','sheet_name','row_no','payload_json'],'required'],[['batch_id','row_no'],'integer'],[['payload_json','errors_json'],'safe'],['status','in','range'=>['valid','warning','error','imported']],['sheet_name','string','max'=>100]];}
    public function getBatch(){return $this->hasOne(StrategyImportBatch::class,['id'=>'batch_id']);}
    public function beforeSave($insert):bool{if(is_array($this->payload_json))$this->payload_json=json_encode($this->payload_json,JSON_UNESCAPED_UNICODE);if(is_array($this->errors_json))$this->errors_json=json_encode($this->errors_json,JSON_UNESCAPED_UNICODE);return parent::beforeSave($insert);}
    public function payload():array{return is_array($this->payload_json)?$this->payload_json:(json_decode((string)$this->payload_json,true)?:[]);}
    public function errors():array{return is_array($this->errors_json)?$this->errors_json:(json_decode((string)$this->errors_json,true)?:[]);}
}
