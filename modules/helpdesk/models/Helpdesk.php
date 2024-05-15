<?php

namespace app\modules\helpdesk\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use app\models\Categorise;
use app\components\CategoriseHelper;

/**
 * This is the model class for table "helpdesk".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $name ชื่อการเก็บข้อมูล
 * @property string|null $title รายการ
 * @property string|null $data_json การเก็บข้อมูลชนิด JSON
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Helpdesk extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

     public $asset_name;
     public $asset_type_name;
    public static function tableName()
    {
        return 'helpdesk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end', 'data_json','created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'code', 'name', 'title'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'name' => 'Name',
            'title' => 'Title',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
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

public function Upload($name)
{
    return FileManagerHelper::FileUpload($this->ref, $name);
}

public function afterFind()
{
    try {
        $this->asset_name = isset($this->asset->data_json['asset_name']) ? $this->asset->data_json['asset_name'] : null;
        $this->asset_type_name = isset($this->asset->data_json['asset_type_text']) ? $this->asset->data_json['asset_type_text'] : null;
    } catch (\Throwable $th) {

    }

    parent::afterFind();
}


// relation
    //Relationships
    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['code' => 'code']);
    }


     //ผู้รับผิดชอบ
     public function getUserReq()
     {
         try {
             $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
             return $employee->getAvatar(false);
         } catch (\Throwable $th) {
             return null;
         }
     }

     // ช่างเทคนิค แสดงตามชื่อกลุ่มที่ส่งมา
     public function listTecName(){
        $sql = "SELECT concat(emp.fname,' ',emp.lname) as fullname,emp.user_id FROM employees emp
        INNER JOIN user ON user.id = emp.user_id
        INNER JOIN auth_assignment auth ON auth.user_id = user.id;";
        $querys = Yii::$app->db->createCommand($sql)->queryAll();
        return ArrayHelper::map($querys, 'user_id','fullname');
        
     }

     //ความเร่งด่วน
    public static function listUrgency()
    {
        return ArrayHelper::map(CategoriseHelper::Categorise('urgency'), 'title', 'title');
    }

         //สถานะงานซ่อม
         public static function listRepairStatus()
         {
             return ArrayHelper::map(CategoriseHelper::Categorise('repair_status'), 'title', 'title');
         }

        //  ทีม
         public function avatarStack()
         {
            try {
                $data = '';
                $data .='<div class="avatar-stack">';
                              foreach($this->data_json['join'] as $key => $avatar){
                                  $emp = Employees::findOne(['user_id' => $avatar]);
                                  $data.= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="'.$emp->fullname.'">';
                                  $data.= Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle shadow']);
                                  $data.='</a>';
                                }
                                $data.='</div>';
                                return $data;
            } catch (\Throwable $th) {

            }
         }
}