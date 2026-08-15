<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Immutable decision history for Finance Inbox.
 *
 * @property int $id
 * @property string $ref
 * @property int $finance_inbox_id
 * @property string $decision
 * @property string $from_status
 * @property string $to_status
 * @property string|null $note
 * @property array|null $metadata_json
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class FinanceInboxReview extends ActiveRecord
{
    public const DECISION_ACCEPT = 'accept';
    public const DECISION_REQUEST_INFORMATION = 'request_information';
    public const DECISION_REJECT = 'reject';

    public static function tableName()
    {
        return '{{%finance_inbox_review}}';
    }

    public function rules()
    {
        return [
            [['finance_inbox_id', 'decision', 'from_status', 'to_status'], 'required'],
            [['finance_inbox_id', 'created_by', 'updated_by'], 'integer'],
            [['note'], 'string'],
            [['metadata_json', 'created_at', 'updated_at'], 'safe'],
            [['decision', 'from_status', 'to_status'], 'string', 'max' => 30],
            [['decision'], 'in', 'range' => array_keys(self::decisionOptions())],
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
            self::DECISION_ACCEPT => 'รับรองรายการ',
            self::DECISION_REQUEST_INFORMATION => 'ขอข้อมูลเพิ่มเติม',
            self::DECISION_REJECT => 'ไม่รับรายการ',
        ];
    }
}
