<?php
namespace app\modules\accounting\models;
use Yii;
use yii\db\ActiveRecord;
class AccountingFiscalConfig extends ActiveRecord
{
    public static function tableName() { return '{{%accounting_fiscal_config}}'; }
    public function rules() { return [[['fiscal_year','chart_version_id','payable_account_id'],'required'], [['fiscal_year','chart_version_id','payable_account_id','input_vat_account_id','created_by','updated_by'],'integer'], [['note'],'string'], [['fiscal_year'],'unique']]; }
    public function beforeSave($insert) { if (!parent::beforeSave($insert)) return false; $now=date('Y-m-d H:i:s'); $uid=Yii::$app->has('user')&&!Yii::$app->user->isGuest?Yii::$app->user->id:null; if($insert){$this->ref=$this->ref?:substr(Yii::$app->security->generateRandomString(),10);$this->created_at=$this->created_at?:$now;$this->created_by=$this->created_by?:$uid;} $this->updated_at=$now;$this->updated_by=$uid;return true; }
    public function getChartVersion(){return $this->hasOne(AccountingChartVersion::class,['id'=>'chart_version_id']);}
    public function getPayableAccount(){return $this->hasOne(AccountingChartAccount::class,['id'=>'payable_account_id']);}
    public function getInputVatAccount(){return $this->hasOne(AccountingChartAccount::class,['id'=>'input_vat_account_id']);}
}
