<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveRecord;

final class RequestStatusLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_request_status_log}}';
    }

    public function rules(): array
    {
        return [
            [['request_id', 'to_status', 'acted_at'], 'required'],
            [['request_id', 'acted_by'], 'integer'],
            [['comment'], 'string'],
            [['acted_at'], 'safe'],
            [['from_status', 'to_status'], 'string', 'max' => 30],
        ];
    }
}
