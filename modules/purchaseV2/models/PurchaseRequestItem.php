<?php

namespace app\modules\purchaseV2\models;

use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

class PurchaseRequestItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'purchase_request_item';
    }

    public function rules()
    {
        return [
            [['request_id', 'line_no', 'legacy_order_item_id', 'created_by', 'updated_by'], 'integer'],
            [['qty', 'unit_price', 'amount'], 'number'],
            [['item_type', 'item_code', 'item_name', 'unit_name', 'budget_type_code', 'legacy_ref', 'data_json', 'detail', 'created_at', 'updated_at'], 'safe'],
            [['item_name'], 'string', 'max' => 255],
            [['unit_name'], 'string', 'max' => 100],
            [['item_type'], 'string', 'max' => 30],
            [['item_code'], 'string', 'max' => 100],
            [['budget_type_code'], 'string', 'max' => 50],
        ];
    }

    public function behaviors()
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

        $this->qty = (float) $this->qty;
        $this->unit_price = (float) $this->unit_price;
        $this->amount = (float) ($this->qty * $this->unit_price);

        return true;
    }

    public function getRequest()
    {
        return $this->hasOne(PurchaseRequest::class, ['id' => 'request_id']);
    }

    public function itemTypeLabel(): string
    {
        return self::itemTypeOptions()[$this->item_type] ?? '-';
    }

    public static function itemTypeOptions(): array
    {
        return [
            'asset' => 'ครุภัณฑ์',
            'consumable' => 'วัสดุ/พัสดุ',
            'service' => 'บริการ',
            'other' => 'อื่น ๆ',
        ];
    }
}

