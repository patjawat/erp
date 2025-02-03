<?php

namespace app\modules\booking\models;

use Yii;
use yii\helpers\Html;
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
class BookingCarItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking_car_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['asset_item_id','car_type','license_plate'], 'required'],
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

    // section Relationships
    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'asset_item_id']);
    }

    public function listCars()
    {
        $sql = "SELECT a.id, t.code as asset_type, a.code,concat(t.title,' (',a.code,')') as title
        FROM `asset` a
        LEFT JOIN `categorise` t ON t.code = a.asset_item 
        WHERE a.asset_group = '3' AND t.category_id = '4'
        ORDER BY `code` DESC, `receive_date`";

        $querys = Yii::$app->db->createCommand($sql)->queryAll();

        return ArrayHelper::map($querys, 'id', 'title');
    }

    public function AvatarXl()
    {
        // return Html::img($this->asset->showImg(),['class' => 'lazyautosizes ls-is-cached lazyloaded','style' => 'max-width:300px;max-height:300px']);
        return Html::img($this->asset->showImg(),['class' => 'card-img-top p-2 rounded border border-2 border-secondary-subtle','style' => 'max-width:250px;max-height:250px']);
    }
    public function Avatar()
    {
        return '<div class="d-flex">
        '.Html::img($this->asset->showImg(),['class' => 'avatar avatar-sm bg-primary text-white lazyautosizes ls-is-cached lazyloaded']).'
        <div class="avatar-detail">
            <h6 class="fs-13">'.$this->asset->AssetitemName().'</h6>
            <p class="text-muted mb-0 fs-13"><span class="badge rounded-pill badge-soft-danger text-success fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ว่าง</span></p>
        </div>
    </div>';
    }
}
