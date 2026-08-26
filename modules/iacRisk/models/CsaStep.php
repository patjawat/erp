<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class CsaStep extends ActiveRecord
{
    public static function tableName(): string { return '{{%iac_csa_step}}'; }
    public function rules(): array { return [[['name'],'required'],[['detail','control_point'],'string'],[['responsible'],'string','max'=>500],[['duration'],'string','max'=>255],[['has_risk'],'boolean']]; }
    public function getRisks() { return $this->hasMany(CsaRisk::class, ['step_id'=>'id'])->orderBy(['sequence'=>SORT_ASC]); }
}
