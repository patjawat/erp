<?php

namespace app\models;

use Yii;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Order;
use app\modules\booking\models\Booking;

/**
 * This is the model class for table "approve".
 *
 * @property int $id
 * @property string|null $from_id รหัสการขออนุญาต
 * @property string|null $name ชื่อการอนุญาต
 * @property string|null $title ชื่อ
 * @property string|null $data_json
 * @property int|null $emp_id ผู้คตรวจสอลและอนุมัติ
 * @property string|null $status ความเห็น Y ผ่าน N ไม่ผ่าน
 * @property int|null $level ลำดับการอนุมติ
 * @property string|null $comment ความคิดเห็น
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Approve extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'approve';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'comment'], 'string'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['emp_id', 'level', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['from_id', 'name', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_id' => 'รหัสการขออนุญาต',
            'name' => 'ชื่อการอนุญาต',
            'title' => 'ชื่อ',
            'data_json' => 'Data Json',
            'emp_id' => 'ผู้คตรวจสอลและอนุมัติ',
            'status' => 'ความเห็น Y ผ่าน N ไม่ผ่าน',
            'level' => 'ลำดับการอนุมติ',
            'comment' => 'ความคิดเห็น',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    // relation table
    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLeave()
    {
        return $this->hasOne(Leave::class, ['id' => 'from_id']);
    }
    public function getPurchase()
    {
        return $this->hasOne(Order::class, ['id' => 'from_id'])->andOnCondition(['name' => 'order']);
    }
    
    public function getBookCar()
    {
        return $this->hasOne(Booking::class, ['id' => 'from_id'])->andOnCondition(['name' => 'book_car']);
    }
    


    

    public function viewApproveDate()
    {
    try {
        return \Yii::$app->thaiFormatter->asDateTime($this->data_json['approve_date'], 'medium');
    } catch (\Throwable $th) {
        return null;
    }    
    }

    
    public function getAvatar($msg =null)
    {
        try {

            if($this->level == 3 && $this->status == 'Pending'){
                $employee = UserHelper::GetEmployee();
                
            }else{
                $employee = Employees::find()->where(['id' => $this->emp_id])->one();
            }
            
            return [
                'avatar' => $employee->getAvatar(false,$msg),
                'photo' => $employee->ShowAvatar(),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName()
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'photo' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }
}
