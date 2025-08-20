<?php

namespace app\modules\plan\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AssetHelper;
use app\modules\hr\models\Organization;

/**
 * This is the model class for table "plan_order".
 *
 * @property int $id
 * @property int|null $thai_year ปีงบประมาณ
 * @property int|null $department_id หน่วยงาน
 * @property string $plan_group_id ประเภทแผน: material, personnel, expenses
 * @property string|null $asset_group_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_type_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_category_id หมวดหมู่ของประเภททรัพย์สินย์
 * @property string $title ชื่อแผน
 * @property string|null $description รายละเอียด
 * @property string $start_date วันที่เริ่มแผน
 * @property string $end_date วันที่สิ้นสุดแผน
 * @property float|null $budget_total งบประมาณรวม
 * @property float|null $budget_used งบที่ใช้ไปแล้ว
 * @property string|null $status สถานะ: draft, submitted, approved, completed
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class PlanOrder extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['thai_year', 'department_id', 'asset_group_id', 'asset_type_id', 'asset_category_id', 'description', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['budget_used'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'draft'],
            [['month_1', 'month_2', 'month_3', 'month_4', 'month_5', 'month_6', 'month_7', 'month_8', 'month_9', 'month_10', 'month_11', 'month_12', 'order_price'], 'default', 'value' => 0],
            [['thai_year', 'department_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['plan_group_id', 'thai_year', 'department_id', 'asset_group_id', 'price_ref'], 'required'],
            [['description'], 'string'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'title', 'emp_id', 'month_1', 'month_2', 'month_3', 'month_4', 'month_5', 'month_6', 'month_7', 'month_8', 'month_9', 'month_10', 'month_11', 'month_12', 'order_price','plan_item_id'], 'safe'],
            [['budget_total', 'budget_used'], 'number'],
            [['plan_group_id'], 'string', 'max' => 50],
            [['asset_group_id', 'asset_type_id', 'asset_category_id', 'title', 'emp_id'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'department_id' => 'Department ID',
            'plan_group_id' => 'Plan Group ID',
            'asset_group_id' => 'Asset Group ID',
            'asset_type_id' => 'Asset Type ID',
            'asset_category_id' => 'Asset Category ID',
            'title' => 'Title',
            'description' => 'Description',
            'budget_total' => 'Budget Total',
            'budget_used' => 'Budget Used',
            'status' => 'Status',
            'emp_id' => 'Emp ID',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }


    public function getAssetGroup()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_group_id'])->andOnCondition(['name' => 'asset_group']);
    }

    public function getAssetType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_type_id'])->andOnCondition(['name' => 'asset_type']);
    }


    public function getAssetCategory()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_category_id'])->andOnCondition(['name' => 'asset_category']);
    }

    public function getBudge()
    {
        return $this->hasOne(Categorise::class, ['code' => 'budget_id'])->andOnCondition(['name' => 'budget']);
    }


    public function getPlanItems()
    {
        return $this->hasMany(PlanItem::class, ['plan_order_id' => 'id']);
    }

    public function departmentName()
    {
        $model =  Organization::findOne(['id' => $this->department_id]);
        if ($model) {
            return $model->name;
        } else {
            return '-';
        }
    }
    public function listAssetType()
    {
        return AssetHelper::listAssetType();
    }

    public function listAssetCategory()
    {
        return AssetHelper::listAssetCategory();
    }

    public function listPriceRef()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'price_ref'])->all(), 'code', 'title');
    }
}
