<?php

namespace app\modules\km\models;

/**
 * แม่แบบกิจกรรม — กรอกซ้ำได้เร็ว
 *
 * @property int $id
 * @property string $name
 * @property int|null $category_id
 * @property string|null $default_title
 * @property string|null $default_objective
 * @property string|null $default_detail
 * @property int $sort
 * @property int $is_active
 * @property string $ref
 */
class KmTemplate extends KmActiveRecord
{
    public static function tableName(): string
    {
        return '{{%km_template}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['default_title'], 'string', 'max' => 500],
            [['default_objective', 'default_detail'], 'string'],
            [['category_id', 'sort', 'is_active'], 'integer'],
            [['is_active'], 'default', 'value' => 1],
            [['sort'], 'default', 'value' => 0],
            [['category_id'], 'exist', 'targetClass' => KmCategory::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'ชื่อแม่แบบ',
            'category_id' => 'หมวดหมู่',
            'default_title' => 'ชื่อกิจกรรมตั้งต้น',
            'default_objective' => 'วัตถุประสงค์ตั้งต้น',
            'default_detail' => 'รายละเอียดตั้งต้น',
            'sort' => 'ลำดับ',
            'is_active' => 'เปิดใช้งาน',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(KmCategory::class, ['id' => 'category_id']);
    }
}
