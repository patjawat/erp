<?php
namespace app\modules\accounting\models;
use Yii;
use yii\db\ActiveRecord;
class AccountingJournalLine extends ActiveRecord
{
    public static function tableName(){return '{{%accounting_journal_line}}';}
    public function rules(){return [[['journal_id','sequence','chart_version_id','chart_account_id','account_code_snapshot','account_name_snapshot','debit_amount','credit_amount'],'required'],[['journal_id','sequence','chart_version_id','chart_account_id','created_by','updated_by'],'integer'],[['debit_amount','credit_amount'],'number','min'=>0],[['account_code_snapshot'],'string','max'=>30],[['account_name_snapshot','description'],'string','max'=>500]];}
    public function beforeSave($insert){if(!parent::beforeSave($insert))return false;$now=date('Y-m-d H:i:s');$uid=Yii::$app->has('user')&&!Yii::$app->user->isGuest?Yii::$app->user->id:null;if($insert){$this->ref=$this->ref?:substr(Yii::$app->security->generateRandomString(),10);$this->created_at=$this->created_at?:$now;$this->created_by=$this->created_by?:$uid;}$this->updated_at=$now;$this->updated_by=$uid;return true;}
}
