<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

abstract class HousingActiveRecord extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->ref)) {
            $this->ref = substr(Yii::$app->security->generateRandomString(), 10);
        }

        return parent::beforeSave($insert);
    }
}
