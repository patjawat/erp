<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;

class Pk1 extends ActiveRecord
{
    public const STATUS_DRAFT='draft';
    public static function tableName(): string{return '{{%iac_pk1}}';}
    public function rules(): array{return [[['hospital_id','fiscal_year_id','fiscal_year','status','recipient','assessment_text','conclusion_text'],'required'],[['hospital_id','fiscal_year_id','fiscal_year','signer_emp_id','created_by','updated_by'],'integer'],[['assessment_text','conclusion_text','weakness_text','signature_data'],'string'],[['recipient','signer_name','signer_position'],'string','max'=>255],[['signature_type'],'in','range'=>['canvas','system']],[['created_at','updated_at'],'safe']];}
    public function getHospital(){return $this->hasOne(Hospital::class,['id'=>'hospital_id']);}
    public function getSigner(){return $this->hasOne(\app\modules\hr\models\Employees::class,['id'=>'signer_emp_id']);}
}
