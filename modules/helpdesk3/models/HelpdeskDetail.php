<?php

namespace app\modules\helpdesk3\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "helpdesk_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property int|null $helpdesk_id เชื่อมกับ ID หลัก
 * @property string|null $name ชื่อการเก็บข้อมูล
 * @property string|null $code
 * @property string|null $title รายการ
 * @property string|null $data_json การเก็บข้อมูลชนิด JSON
 * @property string|null $status สถานะ
 * @property string|null $rating คะแนน
 * @property int|null $move_out จำหน่าย
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class HelpdeskDetail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'helpdesk_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ref', 'name', 'code', 'title', 'data_json', 'status', 'rating', 'move_out', 'thai_year', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['helpdesk_id'], 'default', 'value' => 0],
            [['helpdesk_id', 'move_out', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at','emp_id'], 'safe'],
            [['ref', 'name', 'code', 'title', 'status', 'rating'], 'string', 'max' => 255],
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
            'helpdesk_id' => 'เชื่อมกับ ID หลัก',
            'emp_id' => 'ช่าง',
             'name' => 'ชื่อการเก็บข้อมูล',
            'code' => 'Code',
            'title' => 'รายการ',
            'data_json' => 'การเก็บข้อมูลชนิด JSON',
            'status' => 'สถานะ',
            'rating' => 'คะแนน',
            'move_out' => 'จำหน่าย',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

        public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function ServiceRecordStatus()
    {
         $list = Categorise::find()
            ->andWhere(['name' => 'service_record_status'])->all();
        return ArrayHelper::map($list, 'title', 'title');
    }
    
        public function viewCreateDateTime()
    {
        return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    }

    

}
