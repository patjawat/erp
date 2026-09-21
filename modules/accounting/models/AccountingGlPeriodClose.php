<?php
namespace app\modules\accounting\models;

use Yii;
use yii\db\ActiveRecord;

class AccountingGlPeriodClose extends ActiveRecord
{
    public const STATUS_CLOSED = 'closed';
    public static function tableName(){return '{{%accounting_gl_period_close}}';}
    public function rules(){return [[['period_id','fiscal_year','status','journal_count','total_debit','total_credit','snapshot_hash','closed_at'],'required'],[['period_id','fiscal_year','journal_count','closed_by','created_by','updated_by'],'integer'],[['total_debit','total_credit'],'number'],[['status'],'string','max'=>20],[['snapshot_hash','ref'],'string','max'=>64]];}
    public function beforeSave($insert){if(!parent::beforeSave($insert))return false;$now=date('Y-m-d H:i:s');$uid=Yii::$app->has('user')&&!Yii::$app->user->isGuest?Yii::$app->user->id:null;if($insert){$this->ref=$this->ref?:substr(Yii::$app->security->generateRandomString(),10);$this->created_at=$this->created_at?:$now;$this->created_by=$this->created_by?:$uid;}$this->updated_at=$now;$this->updated_by=$uid;return true;}
    public function getPeriod(){return $this->hasOne(AccountingPeriod::class,['id'=>'period_id']);}
}
