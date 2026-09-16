<?php

namespace app\modules\complaint\models;

use yii\db\ActiveQuery;

/**
 * ไฟล์แนบของเรื่องร้องเรียน — เก็บไฟล์จริงนอก webroot (self-managed) เหมือน KmActivityPhoto
 *
 * @property int         $id
 * @property int         $complaint_id
 * @property int|null    $action_id
 * @property string      $category
 * @property string      $file_path
 * @property string|null $thumbnail_path
 * @property string|null $file_name
 * @property string|null $mime
 * @property int|null    $size
 * @property int         $sort
 */
class ComplaintAttachment extends ComplaintActiveRecord
{
    /** หมวดไฟล์แนบ (ยึด typeKey เดิมจากเอกสารสรุป ข้อ 7) */
    public const CATEGORIES = [
        'publicComplaint' => 'หน้าแจ้งเรื่อง',
        'intakeEvidence' => 'ขั้นรับเรื่อง',
        'assessmentEvidence' => 'ขั้นประเมิน',
        'actionReview' => 'ดำเนินงาน (ทบทวน/RCA)',
        'actionReply' => 'ดำเนินงาน (ตอบกลับ)',
        'closeEvidence' => 'ปิดเคส',
        'general' => 'ทั่วไป',
    ];

    public static function tableName(): string
    {
        return '{{%complaint_attachment}}';
    }

    public function rules(): array
    {
        return [
            [['complaint_id', 'file_path'], 'required'],
            [['complaint_id', 'action_id', 'size', 'sort'], 'integer'],
            [['file_path', 'thumbnail_path'], 'string', 'max' => 500],
            [['file_name'], 'string', 'max' => 255],
            [['mime'], 'string', 'max' => 128],
            [['category'], 'string', 'max' => 32],
        ];
    }

    public function isImage(): bool
    {
        return $this->mime !== null && str_starts_with((string) $this->mime, 'image/');
    }

    public function getComplaint(): ActiveQuery
    {
        return $this->hasOne(Complaint::class, ['id' => 'complaint_id']);
    }
}
