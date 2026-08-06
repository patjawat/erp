<?php

namespace app\modules\pm\models;

use Yii;
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

    public function beforeSave($insert): bool
    {
        if ($insert && !$this->ref) {
            $this->ref = Yii::$app->security->generateRandomString(32);
        }
        return parent::beforeSave($insert);
    }
}
