<?php

namespace app\modules\roster\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * เติมคอลัมน์มาตรฐาน (ref + audit) ให้ทุกตารางในโมดูล roster ตามกติกาใน PRODUCT.md
 */
abstract class RosterActiveRecord extends ActiveRecord
{
    /** คืน null เมื่อรันจาก console (ไม่มี user component) หรือยังไม่ล็อกอิน */
    protected static function currentUserId(): ?int
    {
        try {
            $user = Yii::$app->has('user') ? Yii::$app->user : null;
            return ($user && !$user->isGuest) ? (int) $user->id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = static::currentUserId();
        if ($insert) {
            if ($this->hasAttribute('ref') && !$this->ref) {
                $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            }
            if ($this->hasAttribute('created_at')) {
                $this->created_at = $now;
            }
            if ($this->hasAttribute('created_by')) {
                $this->created_by = $userId;
            }
        }
        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = $now;
        }
        if ($this->hasAttribute('updated_by')) {
            $this->updated_by = $userId;
        }
        return true;
    }
}
