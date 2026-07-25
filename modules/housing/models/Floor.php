<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class Floor extends HousingActiveRecord
{
    public static function tableName(): string
    {
        return '{{%housing_floor}}';
    }

    public function rules(): array
    {
        return [
            [['building_id', 'floor_no', 'name'], 'required'],
            [['building_id', 'floor_no', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string'],
            [['building_id', 'floor_no'], 'unique', 'targetAttribute' => ['building_id', 'floor_no']],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'building_id' => 'อาคาร',
            'floor_no' => 'ชั้นที่',
            'name' => 'ชื่อชั้น',
            'description' => 'รายละเอียด',
            'sort_order' => 'ลำดับแสดงผล',
        ];
    }

    public function getBuilding(): ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    public function getUnits(): ActiveQuery
    {
        return $this->hasMany(Unit::class, ['floor_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'code' => SORT_ASC]);
    }
}
