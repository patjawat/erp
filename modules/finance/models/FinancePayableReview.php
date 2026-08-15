<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

class FinancePayableReview extends ActiveRecord
{
    public const DECISION_SUBMIT = 'submit';
    public const DECISION_APPROVE = 'approve';
    public const DECISION_REQUEST_REVISION = 'request_revision';

    public static function tableName()
    {
        return '{{%finance_payable_review}}';
    }

    public function rules()
    {
        return [
            [['finance_payable_id', 'decision', 'from_status', 'to_status'], 'required'],
            [['finance_payable_id', 'created_by', 'updated_by'], 'integer'],
            [['note'], 'string'],
            [['metadata_json'], 'safe'],
            [['ref'], 'string', 'max' => 64],
            [['decision', 'from_status', 'to_status'], 'string', 'max' => 30],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public static function decisionOptions(): array
    {
        return [
            self::DECISION_SUBMIT => 'ส่งตรวจอนุมัติ',
            self::DECISION_APPROVE => 'อนุมัติเข้าทะเบียน',
            self::DECISION_REQUEST_REVISION => 'ส่งกลับแก้ไข',
        ];
    }

    public function getPayable()
    {
        return $this->hasOne(FinancePayable::class, ['id' => 'finance_payable_id']);
    }
}
