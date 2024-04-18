<?php

namespace app\modules\employees\models;

use app\components\AppHelper;
use app\components\UserHelper;
use app\models\Categorise;
use Yii;
use yii\bootstrap5\Html;

/**
 * This is the model class for table "employees".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $ref
 * @property string|null $avatar
 * @property resource|null $photo
 * @property int $phone
 * @property int|null $cid เลขบัตรประชาชน
 * @property string|null $email
 * @property string|null $gender เพศ
 * @property string|null $prefix คำนำหน้า
 * @property string $fname ชื่อ
 * @property string $lname นามสกุล
 * @property string|null $fname_en ชื่อ(TH)
 * @property string|null $lname_en นามสกุล(EN)
 * @property string|null $birthday วันเกิด
 * @property string|null $address ที่อยู่
 * @property int|null $province จังหวัด
 * @property int|null $amphure อำเภอ
 * @property int|null $district ตำบล
 * @property int|null $zipcode รหัสไปรษณีย์
 * @property int|null $position ตำแหน่ง
 * @property int|null $department แผนก/ฝ่าย
 * @property string|null $status แผนก/ฝ่าย
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Employees extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    public $fulladdress;
    public $fullname;
    public $fullname_en;
    public $age;
    public $blood_group;
    public $hometown;
    public $ethnicity;
    public $nationality;
    public $religion;
    public $marital_status;
     public static function tableName()
    {
        return 'employees';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'fname', 'lname','phone','cid'], 'required'],
            [['user_id',  'province', 'amphure', 'district', 'zipcode', 'position', 'department', 'created_by', 'updated_by'], 'integer'],
            [['photo'], 'string'],
            [['birthday', 'data_json', 'updated_at', 'created_at','cid','code','emp_id'], 'safe'],
            [['ref', 'avatar', 'email', 'address', 'status'], 'string', 'max' => 255],
            [['gender', 'prefix'], 'string', 'max' => 20],
            [['fname', 'lname', 'fname_en', 'lname_en'], 'string', 'max' => 200],
            ['phone', 'unique', 'targetClass' => 'app\modules\employees\models\Employees', 'message' => 'เบอร์โทรศัพท์ถูกใช้แล้ว'],

            // [['cid'], 'validateIdCard'],
        ];
    }


    

    public function checkOwner(){
        $model =   self::find()->where(['fname' => $this->fname, 'lname' => $this->lname])->one();
        if(!$model){
            $this->addError('fname', 'ไม่พบชื่อในระบบ');
            $this->addError('lname', 'ไม่พบนามสกุลในระบบ');
        }
    }

// ตรวจสอลหมายเลขบัตรประชาชน
    public function validateIdCard()
    {
        try {

        $id = str_split(str_replace('-','', $this->cid)); //ตัดรูปแบบและเอา ตัวอักษร ไปแยกเป็น array $id
        $sum = 0;
        $total = 0;
        $digi = 13;
        
        for($i=0; $i<12; $i++){
            $sum = $sum + (intval($id[$i]) * $digi);
            $digi--;
        }
        $total = (11 - ($sum % 11)) % 10;
        
        if($total != $id[12]){ //ตัวที่ 13 มีค่าไม่เท่ากับผลรวมจากการคำนวณ ให้ add error
            $this->addError('cid', 'หมายเลขบัตรประชาชนไม่ถูกต้อง');
        }  
                    //code...
                } catch (\Throwable $th) {
                    //throw $th;
                } 
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'ref' => 'Ref',
            'avatar' => 'Avatar',
            'photo' => 'Photo',
            'phone' => 'Phone',
            'cid' => 'เลขบัตรประชาชน',
            'email' => 'Email',
            'gender' => 'เพศ',
            'prefix' => 'คำนำหน้า',
            'fname' => 'ชื่อ',
            'lname' => 'นามสกุล',
            'fname_en' => 'ชื่อ(TH)',
            'lname_en' => 'นามสกุล(EN)',
            'birthday' => 'วันเกิด',
            'address' => 'ที่อยู่',
            'province' => 'จังหวัด',
            'amphure' => 'อำเภอ',
            'district' => 'ตำบล',
            'zipcode' => 'รหัสไปรษณีย์',
            'position' => 'ตำแหน่ง',
            'department' => 'แผนก/ฝ่าย',
            'status' => 'สถานะ',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            
            
        ];
    }




    public function beforeSave($insert)
    {
        
        $this->birthday = AppHelper::DateToDb($this->birthday);
        $this->cid = AppHelper::SaveCid($this->cid);
        if($this->prefix == 'นาย'){
            $this->gender = 'ชาย';
        }else{
            $this->gender = 'หญิง';
        }

        return parent::beforeSave($insert);
    }
    

    public function afterFind()
    {
        try {
            $this->cid = AppHelper::cidFormat($this->cid);
            $this->fullname = $this->prefix.$this->fname.' '.$this->lname;
            $this->fullname_en = ($this->prefix == 'นาย' ? 'Mr.' : 'Miss.').$this->fname_en.' '.$this->lname_en;
            $this->birthday = AppHelper::DateFormDb($this->birthday);
            $this->age = AppHelper::Age($this->birthday);
            $this->blood_group = $this->data_json['blood_group'] ?  $this->data_json['blood_group'] : null;
            $this->hometown = $this->data_json['hometown'] ?  $this->data_json['hometown'] : null;
            $this->ethnicity = $this->data_json['ethnicity'] ?  $this->data_json['ethnicity'] : null;
            $this->nationality = $this->data_json['nationality'] ?  $this->data_json['nationality'] : null;
            $this->religion = $this->data_json['religion'] ?  $this->data_json['religion'] : null;
            $this->marital_status = $this->data_json['marital_status'] ?  $this->data_json['marital_status'] : null;
            $this->fulladdress = $this->data_json['address2'] ? $this->data_json['address2'] : null;
        } catch (\Throwable $th) {
            //throw $th;
        }

        parent::afterFind(); 
    }


    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAvatar()
    {
        if (isset($this->avatar)) {
            return Html::img('@web/avatar/' . $this->avatar, ['class' => 'view-avatar']);
         } else {
            return Html::img('@web/img/profiles/avatar-01.jpg', ['class' => 'view-avatar']);
         }
    }

    public function getProvincName()
    {
        if($this->province){
            return $this->hasOne(Province::class, ['id' => 'province']);
        }else{
            return null;
        }
    }
    public function getAmphureName()
    {
        return $this->hasOne(Amphure::class, ['id' => 'amphure']);
    }
    public function getDistrictcName()
    {
        return $this->hasOne(District::class, ['id' => 'district']);
    }

    public function getHosName()
    {
        return $this->hasOne(Hospcode::class, ['hospcode' => 'hospcode']);
    }

    public function getEmpPosition()
    {
        return $this->hasMany(Categorise::class, ['emp_id' => 'id']);
    }

    public function fullname(){
        return $this->prefix.$this->fname.' '.$this->lname;
    }
}
