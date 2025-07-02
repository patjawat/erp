<?php

namespace app\modules\am\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "asset_items".
 *
 * @property string $id รหัสทรัพย์สิน
 * @property string|null $ref
 * @property string|null $asset_group_id กลุ่มทรัพย์สิน
 * @property string|null $asset_type_id ประเภททรัพย์สิน
 * @property string|null $asset_category_id หมวดรัพย์สิน
 * @property string|null $title ชื่อทรัพย์สิน
 * @property string|null $fsn ทรัพย์สิน (FSN)
 * @property string|null $description รายละเอียดทรัพย์สิน
 * @property float|null $price ราคาทรัพย์สิน
 * @property float|null $depreciation อัตราค่าเสื่อม
 * @property int|null $service_life อายุการใช้งาน
 * @property string|null $status สถานะทรัพย์สิน
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class AssetItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */

     public $q;
    public static function tableName()
    {
        return 'asset_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ref', 'asset_group_id', 'asset_type_id', 'asset_category_id', 'title', 'fsn', 'description', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['depreciation'], 'default', 'value' => 0.00],
            [['service_life'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 'active'],
            [['id'], 'required'],
            [['title', 'description'], 'string'],
            [['price', 'depreciation'], 'number'],
            [['service_life', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['q','data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id', 'ref', 'asset_group_id', 'asset_type_id', 'asset_category_id', 'fsn'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'รหัสทรัพย์สิน',
            'ref' => 'Ref',
            'asset_group_id' => 'กลุ่มทรัพย์สิน',
            'asset_type_id' => 'ประเภททรัพย์สิน',
            'asset_category_id' => 'หมวดรัพย์สิน',
            'title' => 'ชื่อทรัพย์สิน',
            'fsn' => 'ทรัพย์สิน (FSN)',
            'description' => 'รายละเอียดทรัพย์สิน',
            'price' => 'ราคาทรัพย์สิน',
            'depreciation' => 'อัตราค่าเสื่อม',
            'service_life' => 'อายุการใช้งาน',
            'status' => 'สถานะทรัพย์สิน',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function listAssetType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'EQUIP'])->all(), 'code', 'title');
    }

    public function listAssetCategory()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_category'])->all(), 'code', 'title');
    }

    public function getCategory()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_category_id'])->andOnCondition(['name' => 'asset_category']);
    }
    public function getAssetType()
{
    return $this->hasOne(Categorise::class, ['code' => 'asset_type_id'])->andOnCondition(['name' => 'asset_type']);
        // ->via('category')
}

public function getGroup()
{
    return $this->hasOne(Categorise::class, ['code' => 'asset_group_id'])->andOnCondition(['name' => 'asset_group']);
}

}
