<?php

namespace app\modules\ha12\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Base ของทุกตาราง HA12-PCT (ยึดแนวเดียวกับ KmActiveRecord/QmsActiveRecord)
 * - auto ref (สุ่มครั้งแรกที่บันทึก) เผื่ออ้างอิงข้ามระบบ
 * - created_at/updated_at (dateTime string) + created_by/updated_by อัตโนมัติ
 */
abstract class Ha12ActiveRecord extends ActiveRecord
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
