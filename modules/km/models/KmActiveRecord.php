<?php

namespace app\modules\km\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Base ของทุกตาราง KM (ยึดแนวเดียวกับ QmsActiveRecord)
 * - auto ref (สุ่มครั้งแรกที่บันทึก) เผื่ออ้างอิงข้ามระบบ
 * - created_at/updated_at (dateTime string) + created_by/updated_by อัตโนมัติ
 */
abstract class KmActiveRecord extends ActiveRecord
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
