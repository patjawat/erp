<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Room extends HousingActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_room}}';
    }

    public function rules(): array
    {
        return [
            [['unit_id', 'code', 'name'], 'required'],
            [['unit_id', 'capacity', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 150],
            [['code'], 'unique'],
            [['status'], 'in', 'range' => array_keys(Unit::statusOptions())],
            [['status'], 'default', 'value' => Unit::STATUS_VACANT],
            [['capacity'], 'integer', 'min' => 1, 'skipOnEmpty' => true],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'unit_id' => 'ยูนิต',
            'code' => 'รหัสห้อง',
            'name' => 'ชื่อห้อง',
            'capacity' => 'ความจุ (ถ้ามี)',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'sort_order' => 'ลำดับแสดงผล',
        ];
    }

    public function getUnit(): ActiveQuery
    {
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }

    public function getAssets(): ActiveQuery
    {
        return $this->hasMany(AssetAssignment::class, ['room_id' => 'id'])
            ->andWhere(['is_active' => 1])
            ->orderBy(['item_name' => SORT_ASC]);
    }

    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(LocationPhoto::class, ['room_id' => 'id'])
            ->orderBy(['is_primary' => SORT_DESC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
