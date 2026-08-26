<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class Pk4Item extends ActiveRecord
{
    public static function tableName(): string{return '{{%iac_pk4_item}}';}
    public static function components(): array{return ['control_environment'=>'1. สภาพแวดล้อมการควบคุม','risk_assessment'=>'2. การประเมินความเสี่ยง','control_activities'=>'3. กิจกรรมการควบคุม','information_communication'=>'4. สารสนเทศและการสื่อสาร','monitoring'=>'5. การติดตามประเมินผล'];}
    public function rules(): array{return [[['pk4_id','component_code','sequence','component_name'],'required'],[['pk4_id','sequence','created_by','updated_by'],'integer'],[['evaluation_summary'],'string'],[['created_at','updated_at'],'safe']];}
}
