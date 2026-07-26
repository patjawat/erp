<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use app\modules\filemanager\models\Uploads;
use yii\db\ActiveQuery;

final class LocationPhoto extends HousingActiveRecord
{
    public $photo_file;

    public static function tableName(): string
    {
        return '{{%housing_location_photo}}';
    }

    public function rules(): array
    {
        return [
            [['unit_id'], 'required'],
            [['unit_id', 'room_id', 'upload_id', 'is_primary', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['caption'], 'string', 'max' => 255],
            [['is_primary'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
            [['photo_file'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'skipOnEmpty' => false,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'photo_file' => 'ไฟล์รูปภาพ',
            'caption' => 'คำอธิบายภาพ',
            'is_primary' => 'กำหนดเป็นภาพหลัก',
        ];
    }

    public function getUpload(): ActiveQuery
    {
        return $this->hasOne(Uploads::class, ['id' => 'upload_id']);
    }
}
