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
            [['description', 'caution', 'related_links'], 'string'],
            [['related_links'], 'validateRelatedLinks'],
            [['title'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function getMedia()
    {
        return $this->hasMany(DocumentStepMedia::class, ['step_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getRelatedLinkItems(): array
    {
        $items = json_decode((string) $this->related_links, true);
        return is_array($items) ? $items : [];
    }

    public function validateRelatedLinks($attribute): void
    {
        foreach ($this->getRelatedLinkItems() as $link) {
            $url = trim((string) ($link['url'] ?? ''));
            if ((int) ($link['document_id'] ?? 0) > 0) {
                continue;
            }
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
                $this->addError($attribute, 'ลิงก์เอกสารที่เกี่ยวข้องไม่ถูกต้อง');
                return;
            }
        }
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
