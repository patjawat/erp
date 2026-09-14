<?php

namespace app\modules\km\models;

/**
 * รูปภาพของกิจกรรม KM
 *
 * @property int $id
 * @property int $activity_id
 * @property string $file_path
 * @property string|null $file_name
 * @property string|null $mime
 * @property int|null $size
 * @property string|null $thumbnail_path
 * @property string|null $caption
 * @property int $sort
 * @property string $ref
 */
class KmActivityPhoto extends KmActiveRecord
{
    public static function tableName(): string
    {
        return '{{%km_activity_photo}}';
    }

    public function rules(): array
    {
        return [
            [['activity_id', 'file_path'], 'required'],
            [['activity_id', 'size', 'sort'], 'integer'],
            [['file_path', 'thumbnail_path'], 'string', 'max' => 500],
            [['file_name', 'caption'], 'string', 'max' => 255],
            [['caption'], 'string', 'max' => 500],
            [['mime'], 'string', 'max' => 100],
            [['sort'], 'default', 'value' => 0],
            [['activity_id'], 'exist', 'targetClass' => KmActivity::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'file_name' => 'ชื่อไฟล์',
            'caption' => 'คำบรรยายภาพ',
            'sort' => 'ลำดับ',
        ];
    }

    public function getActivity()
    {
        return $this->hasOne(KmActivity::class, ['id' => 'activity_id']);
    }
}
