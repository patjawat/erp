<?php

namespace app\modules\dms\models;

use Yii;

/**
 * This is the model class for table "document_tags".
 *
 * @property int $id
 * @property string|null $name ชื่อการ tags เอกสาร
 * @property string|null $to_id
 * @property string|null $document_id
 * @property string|null $data_json
 */
class DocumentTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json'], 'safe'],
            [['name', 'to_id', 'document_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อการ tags เอกสาร',
            'to_id' => 'To ID',
            'document_id' => 'Document ID',
            'data_json' => 'Data Json',
        ];
    }
}
