<?php

namespace app\modules\sm\models;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "asset".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $asset_group แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_item
 * @property string|null $code ครุภัณฑ์
 * @property string|null $fsn_number หมายเลขครุภัณฑ์
 * @property int|null $qty จำนวน
 * @property string|null $receive_date วันที่รับเข้า
 * @property float|null $price ราคา
 * @property int|null $purchase
 * @property int|null $department
 * @property string|null $repair ประวัติการซ่อม
 * @property string|null $owner
 * @property int|null $life อายุการใช้งาน
 * @property string|null $device_items อุปกรณ์ภายใน
 * @property int|null $on_year
 * @property int|null $dep_id ประจำอยู่หน่วยงาน
 * @property int|null $depre_type ประเภทค่าเสื่อมราคา
 * @property int|null $budget_year งบประมาณ
 * @property string|null $asset_status สถานะทรัพย์สิน
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qty', 'purchase', 'department', 'life', 'on_year', 'dep_id', 'depre_type', 'budget_year', 'created_by', 'updated_by'], 'integer'],
            [['receive_date', 'device_items', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['price'], 'number'],
            [['ref', 'asset_group', 'asset_item', 'code', 'fsn_number', 'owner', 'asset_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'asset_group' => 'Asset Group',
            'asset_item' => 'Asset Item',
            'code' => 'Code',
            'fsn_number' => 'Fsn Number',
            'qty' => 'Qty',
            'receive_date' => 'Receive Date',
            'price' => 'Price',
            'purchase' => 'Purchase',
            'department' => 'Department',
            'repair' => 'Repair',
            'owner' => 'Owner',
            'life' => 'Life',
            'device_items' => 'Device Items',
            'on_year' => 'On Year',
            'dep_id' => 'Dep ID',
            'depre_type' => 'Depre Type',
            'budget_year' => 'Budget Year',
            'asset_status' => 'Asset Status',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'product_type'])->all(), 'code', 'title');
    }
}
