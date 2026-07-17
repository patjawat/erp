<?php

namespace app\modules\medsop\models;

use yii\db\ActiveRecord;

class DocumentStep extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%medsop_document_step}}';
    }

    public function rules()
    {
        return [
            [['document_id', 'step_order', 'title'], 'required'],
            [['document_id', 'step_order'], 'integer'],
            [['description', 'caution'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function beforeSave($insert)
    {
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
