<?php

namespace app\modules\booking\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\am\models\Asset;
/**
 * This is the model class for table "booking_cars_items".
 *
 * @property int $id
 * @property string|null $car_type ประเภทของรถตามการใช้งาน
 * @property int|null $asset_item_id รายการทรัพย์สิน
 * @property string|null $license_plate เลขทะเบียน
 * @property int|null $active
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class BookingCarsItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking_cars_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['asset_item_id', 'active', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['car_type', 'license_plate'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'car_type' => 'ประเภทของรถตามการใช้งาน',
            'asset_item_id' => 'รายการทรัพย์สิน',
            'license_plate' => 'เลขทะเบียน',
            'active' => 'Active',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function listCars()
    {
        $list  = Asset::find()->where(['asset_group' => 3])->all();
        return ArrayHelper::map($list,'asset_item',function($model){
            try {
                return $model->assetItem->title;
            } catch (\Throwable $th) {
                return '';
            }
        });
    }
}
