<?php

namespace app\modules\pm\models;

use Yii;
use app\components\RichText;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

abstract class StrategyRecord extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => static fn () => date('Y-m-d H:i:s')],
            ['class' => BlameableBehavior::class],
        ];
    }

    /** ฟิลด์ที่เปิดให้จัดรูปแบบเป็นข้อ/หัวข้อย่อยได้ — จะถูกกรอง HTML ก่อนบันทึกเสมอ */
    public function richTextAttributes(): array { return []; }

    public function beforeValidate(): bool
    {
        foreach ($this->richTextAttributes() as $attribute) {
            $value = $this->getAttribute($attribute);
            if ($value !== null && $value !== '') {
                $this->setAttribute($attribute, RichText::sanitize((string) $value) ?: null);
            }
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert): bool
    {
        if ($insert && !$this->ref) {
            $this->ref = Yii::$app->security->generateRandomString(32);
        }
        return parent::beforeSave($insert);
    }
}
