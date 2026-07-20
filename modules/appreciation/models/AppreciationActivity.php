<?php
namespace app\modules\appreciation\models;

use yii\db\ActiveRecord;

class AppreciationActivity extends ActiveRecord
{
    public $imageFile;
    const TYPE_SURVEY='survey'; const TYPE_TRAINING='training'; const TYPE_EVENT='event'; const TYPE_ONLINE='online';
    const MODE_REGISTER='register'; const MODE_EXTERNAL='external'; const MODE_ATTENDANCE='attendance';
    const STATUS_DRAFT='draft'; const STATUS_PUBLISHED='published'; const STATUS_CLOSED='closed';
    public static function tableName(){ return '{{%appreciation_activity}}'; }
    public static function typeLabels(){ return [self::TYPE_SURVEY=>'แบบสอบถาม',self::TYPE_TRAINING=>'อบรม/หลักสูตร',self::TYPE_EVENT=>'กิจกรรม',self::TYPE_ONLINE=>'กิจกรรมออนไลน์']; }
    public static function modeLabels(){ return [self::MODE_REGISTER=>'ลงทะเบียนเข้าร่วม',self::MODE_EXTERNAL=>'ทำกิจกรรมผ่านลิงก์ภายนอก',self::MODE_ATTENDANCE=>'ตรวจสอบการเข้าร่วม']; }
    public static function statusLabels(){ return [self::STATUS_DRAFT=>'แบบร่าง',self::STATUS_PUBLISHED=>'เปิดรับผู้เข้าร่วม',self::STATUS_CLOSED=>'ปิดกิจกรรม']; }
    public function rules(){ return [
        [['program_year_id','title','activity_type','participation_mode','points','start_at','end_at','status'],'required'],
        [['program_year_id','points','capacity'],'integer','min'=>0], [['description'],'string'], [['requires_review'],'boolean'],
        [['start_at','end_at','created_at','updated_at'],'safe'], [['title'],'string','max'=>255],
        [['external_url'],'url'], [['external_url'],'string','max'=>1000], [['image_url'],'string','max'=>500],
        [['imageFile'],'file','skipOnEmpty'=>true,'extensions'=>['png','jpg','jpeg','webp'],'checkExtensionByMimeType'=>true,'maxSize'=>5*1024*1024],
        [['activity_type'],'in','range'=>array_keys(self::typeLabels())], [['participation_mode'],'in','range'=>array_keys(self::modeLabels())],
        [['status'],'in','range'=>array_keys(self::statusLabels())], [['end_at'],'compare','compareAttribute'=>'start_at','operator'=>'>='],
    ]; }
    public function beforeSave($insert){ if($insert)$this->created_at=date('Y-m-d H:i:s'); $this->updated_at=date('Y-m-d H:i:s'); return parent::beforeSave($insert); }
    public function attributeLabels(){ return ['program_year_id'=>'รอบปี','title'=>'ชื่อกิจกรรม','description'=>'รายละเอียด','activity_type'=>'ประเภทกิจกรรม','participation_mode'=>'รูปแบบการเข้าร่วม','external_url'=>'ลิงก์กิจกรรมภายนอก','image_url'=>'รูปภาพ','imageFile'=>'ภาพปกกิจกรรม','points'=>'คะแนนที่ได้รับ','capacity'=>'จำนวนที่รับ','start_at'=>'เริ่มกิจกรรม','end_at'=>'สิ้นสุดกิจกรรม','status'=>'สถานะ','requires_review'=>'ให้ผู้ดูแลตรวจสอบก่อนให้คะแนน']; }
    public function getProgramYear(){ return $this->hasOne(AppreciationProgramYear::class,['id'=>'program_year_id']); }
    public function getParticipations(){ return $this->hasMany(AppreciationParticipation::class,['activity_id'=>'id']); }
    public function isOpen(){ return $this->status===self::STATUS_PUBLISHED && strtotime($this->start_at)<=time() && strtotime($this->end_at)>=time(); }
}
