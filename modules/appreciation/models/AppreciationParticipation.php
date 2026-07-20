<?php
namespace app\modules\appreciation\models;

use yii\db\ActiveRecord;
use app\modules\appreciation\services\AppreciationSnapshotService;

class AppreciationParticipation extends ActiveRecord
{
    const STATUS_REGISTERED='registered'; const STATUS_SUBMITTED='submitted'; const STATUS_COMPLETED='completed'; const STATUS_REJECTED='rejected';
    public static function tableName(){ return '{{%appreciation_participation}}'; }
    public static function statusLabels(){ return [self::STATUS_REGISTERED=>'ลงทะเบียนแล้ว',self::STATUS_SUBMITTED=>'รอตรวจสอบ',self::STATUS_COMPLETED=>'ได้รับคะแนนแล้ว',self::STATUS_REJECTED=>'ไม่ผ่านการตรวจสอบ']; }
    public function rules(){ return [[['activity_id','program_year_id','emp_id','status','registered_at'],'required'],[['activity_id','program_year_id','emp_id','points_awarded','reviewed_by','department_id_snapshot','age_at_registration_snapshot'],'integer'],[['registered_at','completed_at','reviewed_at'],'safe'],[['evidence_url'],'string','max'=>1000],[['note'],'string','max'=>500],[['department_name_snapshot','position_name_snapshot','position_group_name_snapshot'],'string','max'=>255],[['age_band_snapshot'],'string','max'=>32],[['status'],'in','range'=>array_keys(self::statusLabels())]]; }
    public function beforeSave($insert){
        if($insert){
            $snapshot=AppreciationSnapshotService::employee($this->emp_id,$this->registered_at ?: date('Y-m-d'));
            $this->department_id_snapshot=$snapshot['department_id'];
            $this->department_name_snapshot=$snapshot['department_name'];
            $this->position_name_snapshot=$snapshot['position_name'];
            $this->position_group_name_snapshot=$snapshot['position_group_name'];
            $this->age_at_registration_snapshot=$snapshot['age'];
            $this->age_band_snapshot=$snapshot['age_band'];
        }
        return parent::beforeSave($insert);
    }
    public function getActivity(){ return $this->hasOne(AppreciationActivity::class,['id'=>'activity_id']); }
}
