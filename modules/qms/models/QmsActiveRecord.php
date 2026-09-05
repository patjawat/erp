<?php

namespace app\modules\qms\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Base ของทุกตาราง QMS
 * - auto ref (สุ่มครั้งแรกที่บันทึก)
 * - created_at/updated_at (dateTime string) + created_by/updated_by อัตโนมัติ
 */
abstract class QmsActiveRecord extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => static fn (): string => date('Y-m-d H:i:s'),
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && empty($this->ref)) {
            $this->ref = Yii::$app->security->generateRandomString(24);
        }
        return true;
    }
}
