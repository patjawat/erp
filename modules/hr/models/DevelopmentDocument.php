<?php

namespace app\modules\hr\models;

use app\modules\purchase\models\Doc;

/** Snapshot เอกสารเดินทางที่แก้ไขได้ก่อนพิมพ์ */
class DevelopmentDocument extends Doc
{
    public static function tableName()
    {
        return 'hr_development_document';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['development_id'], 'required'],
            [['development_id'], 'integer'],
        ]);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && strpos((string) $this->ref, 'purchase_doc_') === 0) {
            $this->ref = 'hr_development_doc_' . uniqid();
        }

        return true;
    }

    public function getDevelopment()
    {
        return $this->hasOne(Development::class, ['id' => 'development_id']);
    }
}
