<?php

namespace app\modules\finance\models;

use Yii;

/**
 * ช่อง ref / created_at / updated_at / created_by / updated_by ที่ทุกตารางของเงินยืมมีเหมือนกัน
 *
 * แยกเป็น trait แทนที่จะใช้ TimestampBehavior เพราะตารางชุดนี้เก็บเวลาเป็น datetime
 * ไม่ใช่ timestamp แบบ integer และยังต้องออกค่า ref ให้เองด้วย
 */
trait LoanAuditTrait
{
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: Yii::$app->security->generateRandomString(22);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    /** ตัวเลขที่ผู้ใช้พิมพ์อาจติดลูกน้ำมาจากการคัดลอก ต้องล้างก่อนคำนวณเสมอ */
    protected function money($value): float
    {
        return (float) str_replace([',', ' '], '', (string) $value);
    }
}
