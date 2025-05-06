<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Development;

/**
 * This is the model class for table "development_detail".
 *
 * @property int $id
 * @property int $development_id ID ของการพัฒนา
 * @property string $name ชื่อของการเก็บข้อมูล
 * @property string $emp_id รหัสบุคลากร
 * @property int|null $qty จํานวน
 * @property float|null $price ราคา
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
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
            [['data_json', 'created_at', 'updated_at', 'deleted_at','category_id'], 'safe'],
            [['name', 'emp_id'], 'string', 'max' => 255],
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
            'emp_id' => 'รหัสบุคลากร',
            'qty' => 'จํานวน',
            'price' => 'ราคา',
            'category_id' => 'รหัสหมวดหมู่ของ name',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
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
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'expense_type']);
    }
    
    public function listExpenseType()
    {
        $data = Categorise::find()->where(['name' => 'expense_type'])->all();
        return \yii\helpers\ArrayHelper::map($data, 'code', 'title');
    }
}
