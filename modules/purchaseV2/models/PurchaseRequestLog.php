<?php

namespace app\modules\purchaseV2\models;

use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Employees;

class PurchaseRequestLog extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'purchase_request_log';
    }

    public function rules()
    {
        return [
            [['request_id', 'from_status', 'to_status', 'actor_emp_id', 'actor_user_id', 'created_by'], 'integer'],
            [['action', 'message', 'data_json', 'created_at'], 'safe'],
            [['action'], 'string', 'max' => 50],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        if (is_string($this->data_json)) {
            $decoded = json_decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($this->data_json)) {
            $this->data_json = [];
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (is_array($this->data_json)) {
            $this->data_json = json_encode($this->data_json, JSON_UNESCAPED_UNICODE);
        }

        return true;
    }

    public function getRequest()
    {
        return $this->hasOne(PurchaseRequest::class, ['id' => 'request_id']);
    }

    public function getActorEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'actor_emp_id']);
    }
}

