<?php

namespace app\modules\laundry\models;

use yii\db\ActiveRecord;

/**
 * ประเภทผ้า (เสื้อ/กางเกง/ผ้าห่ม/ผ้าปูเตียง ฯลฯ) — ตั้งค่าที่เมนูตั้งค่า
 *
 * @property int $id
 * @property string $item_code
 * @property string $item_name
 * @property int|null $category_id  หมวดผ้า (laundry_item_category)
 * @property string|null $stock_item_code
 * @property int $is_active
 * @property string $created_at
 * @property int|null $created_by
 */
class LaundryItem extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%laundry_item}}';
    }

    public function rules()
    {
        return [
            [['item_name'], 'required'],
            [['item_name'], 'string', 'max' => 255],
            [['item_code'], 'string', 'max' => 50],
            [['item_code'], 'unique'],
            [['is_active', 'created_by', 'category_id'], 'integer'],
            [['category_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => LaundryItemCategory::class, 'targetAttribute' => 'id'],
            [['created_at'], 'safe'],
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(LaundryItemCategory::class, ['id' => 'category_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            if (empty($this->created_at)) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            if (empty($this->item_code)) {
                do {
                    $code = 'LI' . strtoupper(bin2hex(random_bytes(3)));
                } while (static::find()->where(['item_code' => $code])->exists());
                $this->item_code = $code;
            }
        }
        return true;
    }
}
