<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class Pk4 extends ActiveRecord
{
    public const STATUS_DRAFT='draft';
    public static function tableName(): string{return '{{%iac_pk4}}';}
    public function rules(): array{return [[['hospital_id','fiscal_year_id','fiscal_year','org_unit_id','status'],'required'],[['hospital_id','fiscal_year_id','fiscal_year','org_unit_id','signer_emp_id','created_by','updated_by'],'integer'],[['summary','signature_data'],'string'],[['signer_name','signer_position'],'string','max'=>255],[['signature_type'],'in','range'=>['canvas','system']],[['created_at','updated_at'],'safe']];}
    public function getItems(){return $this->hasMany(Pk4Item::class,['pk4_id'=>'id'])->orderBy(['sequence'=>SORT_ASC]);}
    public function getOrgUnit(){return $this->hasOne(\app\modules\settings\models\OrgUnit::class,['id'=>'org_unit_id']);}
    public function getSigner(){return $this->hasOne(\app\modules\hr\models\Employees::class,['id'=>'signer_emp_id']);}
}
