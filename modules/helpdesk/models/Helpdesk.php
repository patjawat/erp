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
use app\modules\filemanager\models\Uploads;
use yii\helpers\Json;

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
            [['date_start', 'date_end', 'data_json','created_at', 'updated_at','status','rating','repair_group'], 'safe'],
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


// public function beforeSave($insert)
// {
//     // if ($insert) {  // only for Save (No Update)
//         if (!empty($this->rating)) {
//             $this->setAttribute('rating', $this->rating-3.75);
//         }
//     // }
//     return parent::beforeSave($insert);
// }
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

// แสดงรูปภาพ
public function ShowImg()
{
    try {
        $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'repair'])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }
    } catch (\Throwable $th) {
        return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
    }

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
        return ArrayHelper::map(CategoriseHelper::Categorise('urgency'), 'code', 'title');
    }

         //การใช้คะแนน
         public static function listRating()
         {
             return ArrayHelper::map(CategoriseHelper::Categorise('rating'), 'code', 'title');
         }

                  //หน่วยงานที่ส่งซ่อม
                  public static function listRepairGroup()
                  {
                      return ArrayHelper::map(CategoriseHelper::Categorise('repair_group'), 'code', 'title');
                  }

                                    //หน่วยงานที่ส่งซ่อม
                                    public  function viewRepairGroup()
                                    {
                                        $model =  Categorise::findOne(['name' => 'repair_group','code' => $this->repair_group]);
                                        if($model){
                                            return $model->title;
                                        }else{
                                            return null;
                                        }
                                    }
         //สถานะงานซ่อม
         public static function listRepairStatus()
         {
            $model = Categorise::find()->where(['name' => 'repair_status'])
            ->andWhere(['<>','code',5])->all();
             return ArrayHelper::map($model, 'code', 'title');
         }

        //  ภาพทีม
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

         public function showAvatarCreate()
         {
            $emp = Employees::findOne(['user_id' => $this->created_by]);
            return  Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle shadow']);
         }

        //แสดงสถานะ
         public function viewStatus()
         {
            if(isset($this->data_json['urgency'])){
                $model = Categorise::findOne(['name' => 'repair_status','code' => $this->status]);
               
                if($model->code == 1)
                {
                    return '<span class="badge rounded-pill bg-danger-subtle"><i class="fa-solid fa-triangle-exclamation"></i> '.$model->title.'</span>';
                }
                if($model->code == 2)
                {
                    return '<span class="badge rounded-pill bg-warning-subtle"><i class="fa-solid fa-user-check"></i> '.$model->title.'</span>';
                }
                if($model->code == 3)
                {
                    return '<span class="badge rounded-pill bg-primary-subtle"><i class="fa-solid fa-person-digging text-primary"></i> '.$model->title.'</span>';
                }
                if($model->code == 4)
                {
                    return '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-circle-check text-success"></i> '.$model->title.'</span>';
                }
                if($model->code == 5)
                {
                    return '<span class="badge rounded-pill bg-success-subtle"><i class="fa-solid fa-circle-minus text-danger"></i> '.$model->title.'</span>';
                }
            }
         }

         //แสดงความเร่งด่วน
         public function viewUrgency()
         {
            if(isset($this->data_json['urgency'])){
                $model = Categorise::findOne(['name' => 'urgency','code' => $this->data_json['urgency']]);
               
                if($model->code == 1)
                {
                    return '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-face-smile"></i> '.$model->title.'</span>';
                }
                if($model->code == 2)
                {
                    return '<span class="badge rounded-pill bg-primary-subtle"><i class="fa-solid fa-exclamation"></i> '.$model->title.'</span>';
                }
                if($model->code == 3)
                {
                    return '<span class="badge rounded-pill bg-warning-subtle"><i class="fa-solid fa-circle-exclamation text-danger"></i> '.$model->title.'</span>';
                }
                if($model->code == 4)
                {
                    return '<span class="badge rounded-pill bg-danger-subtle"><i class="fa-solid fa-bomb text-danger"></i> '.$model->title.'</span>';
                }
            }
         }

         //แสดงวันที่ส่งซ่อม
         public function viewCreateDate()
         {
            return Yii::$app->thaiFormatter->asDate($this->created_at, 'short');
         }

         public function viewCreateDateTime()
         {
            return Yii::$app->thaiFormatter->asDateTime($this->created_at, 'medium');
         }

         //แสดงวันที่รับเรื่อง
         public function viewAccetpTime()
         {
             return Yii::$app->thaiFormatter->asDateTime($this->data_json['accept_time'], 'medium');
             try {
            } catch (\Throwable $th) {
                //throw $th;
            }
         }

           //แสดงวันที่รับเรื่อง
           public function viewStartJob()
           {
               try {
                  return Yii::$app->thaiFormatter->asDateTime($this->data_json['start_job'], 'medium');
              } catch (\Throwable $th) {
                  //throw $th;
              }
           }
        //แสดงวันที่แล้วสร็จ
        public function viewEndJob()
            {
                try {
                        return Yii::$app->thaiFormatter->asDateTime($this->data_json['end_job'], 'medium');
                    } catch (\Throwable $th) {

             }
        }
        
                //วันที่แสดงความคิดเห็น
                public function viewCommentDate()
                {
                    try {
                            return Yii::$app->thaiFormatter->asDateTime($this->data_json['comment_date'], 'medium');
                        } catch (\Throwable $th) {
    
                 }
            }
}