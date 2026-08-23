<?php
namespace app\modules\serviceProfile\models;
use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;
class ServiceProfileSectionComment extends ActiveRecord
{
    public const STATUS_OPEN='open';public const STATUS_RESOLVED='resolved';
    public static function tableName(){return '{{%service_profile_section_comment}}';}
    public function rules(){return [[['service_profile_id','section_id','reviewer_employee_id','comment'],'required'],[['service_profile_id','section_id','reviewer_employee_id','resolved_by_user_id'],'integer'],[['comment'],'string'],[['created_at','resolved_at'],'safe'],[['status'],'in','range'=>[self::STATUS_OPEN,self::STATUS_RESOLVED]]];}
    public function getSection(){return $this->hasOne(ServiceProfileSection::class,['id'=>'section_id']);}
    public function getReviewer(){return $this->hasOne(Employees::class,['id'=>'reviewer_employee_id']);}
}
