<?php

namespace app\modules\medsop\models;

use yii\db\ActiveRecord;

class DocumentStepMedia extends ActiveRecord
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public static function tableName()
    {
        return '{{%medsop_document_step_media}}';
    }

    public function rules()
    {
        return [
            [['step_id', 'media_type', 'file_name', 'file_path', 'mime_type', 'file_size'], 'required'],
            [['step_id', 'file_size', 'sort_order', 'created_by'], 'integer'],
            [['media_type'], 'in', 'range' => [self::TYPE_IMAGE, self::TYPE_VIDEO]],
            [['file_name'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
            [['mime_type'], 'string', 'max' => 100],
            [['created_at'], 'safe'],
        ];
    }

    public function getStep()
    {
        return $this->hasOne(DocumentStep::class, ['id' => 'step_id']);
    }
}
