<?php
namespace app\modules\accounting\models;
use Yii;
use yii\db\ActiveRecord;
class AccountingJournalDraft extends ActiveRecord
{
    public const SOURCE_PAYABLE='payable'; public const STATUS_DRAFT='draft';
    public static function tableName(){return '{{%accounting_journal_draft}}';}
    public function rules(){return [[['source_type','source_id','fiscal_year','document_date','document_no','description','status','total_debit','total_credit'],'required'],[['source_id','fiscal_year','created_by','updated_by'],'integer'],[['total_debit','total_credit'],'number','min'=>0],[['document_date'],'date','format'=>'php:Y-m-d'],[['document_no'],'string','max'=>100],[['description'],'string','max'=>500]];}
    public function beforeSave($insert){if(!parent::beforeSave($insert))return false;$now=date('Y-m-d H:i:s');$uid=Yii::$app->has('user')&&!Yii::$app->user->isGuest?Yii::$app->user->id:null;if($insert){$this->ref=$this->ref?:substr(Yii::$app->security->generateRandomString(),10);$this->created_at=$this->created_at?:$now;$this->created_by=$this->created_by?:$uid;}$this->updated_at=$now;$this->updated_by=$uid;return true;}
    public function getLines(){return $this->hasMany(AccountingJournalLine::class,['journal_id'=>'id'])->orderBy('sequence');}
}
