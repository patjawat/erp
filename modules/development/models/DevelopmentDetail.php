<?php

namespace app\modules\development\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Employees;

/**
 * Model สำหรับตาราง "development_detail" (ใช้ table เดิม).
 *
 * @property int $id
 * @property int $development_id
 * @property string|null $category_id
 * @property string $name
 * @property string|null $emp_id
 * @property int|null $qty
 * @property float|null $price
 * @property string|null $data_json
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 */
class DevelopmentDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'development_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qty', 'price', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['development_id', 'name'], 'required'],
            [['development_id', 'qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'category_id'], 'safe'],
            [['name', 'emp_id', 'category_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'development_id' => 'ID ของการพัฒนา',
            'name' => 'ชื่อของการเก็บข้อมูล',
            'category_id' => 'รหัสหมวดหมู่',
            'emp_id' => 'รหัสบุคลากร',
            'qty' => 'จำนวน',
            'price' => 'ราคา',
            'data_json' => 'ข้อมูลเพิ่มเติม',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getDevelopment()
    {
        return $this->hasOne(Development::class, ['id' => 'development_id']);
    }

    public function getExpenseType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])
            ->andOnCondition(['name' => 'expense_type']);
    }

    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
}
