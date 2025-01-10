<?php

namespace app\modules\dms\models;

use Yii;
use Exception;
use yii\helpers\Url;
use yii\helpers\Json;
use Imagine\Image\Box;
use yii\db\Expression;
use yii\imagine\Image;
use yii\base\Component;
use yii\bootstrap5\Html;
use yii\web\UploadedFile;
use app\models\Categorise;
use kartik\file\FileInput;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;


/**
 * This is the model class for table "documents_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อการ tags เอกสาร เช่น  employee,department
 * @property string|null $document_id เอกสาร
 * @property string|null $to_id ถึงบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน
 * @property string|null $to_name ถึงบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน
 * @property string|null $to_type ถึงบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน
 * @property string|null $from_id จากบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน
 * @property string|null $from_name จากบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน
 * @property string|null $from_type จากบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class DocumentsDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $thai_year;
    public $show_reading;
    public static function tableName()
    {
        return 'documents_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at','thai_year','q','show_reading','tags_employee','tags_department'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by','doc_read'], 'integer'],
            [['ref', 'name', 'document_id', 'to_id', 'to_name', 'to_type', 'from_id', 'from_name', 'from_type'], 'string', 'max' => 255],
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
            'name' => 'ชื่อการ tags เอกสาร เช่น  employee,department',
            'document_id' => 'เอกสาร',
            'to_id' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน',
            'to_name' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน',
            'to_type' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน',
            'from_id' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน',
            'from_name' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน',
            'from_type' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน',
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
    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

    
    public function ListThaiYear()
    {
        $model = Documents::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    // ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {
            // หาบุคคลที่เคย tag ไปแล้ว
            $tags = self::find()->select('to_id')
            
            ->where(['name' => 'employee', 'document_id' => $this->document_id])
            ->andWhere(['<>', 'to_id',$this->created_by])
            ->All();
            $allTag = ArrayHelper::getColumn($tags, 'to_id');
            // หาบุคคลที่ยังไม่ได้ tag ไป  และ ไม่ใช่ admin
            $employees = Employees::find()
                ->select(['id', 'concat(fname, " ", lname) as fullname'])
                ->andWhere(['status' => '1'])
                ->andWhere(['<>', 'id', '1'])
                ->andWhere(['not in', 'id', $allTag])
                ->asArray()
                ->all();

            return ArrayHelper::map($employees, 'id', function ($model) {
                return $model['fullname'];
            });
        } catch (\Throwable $th) {
            return [];
        }
    }

    
     // บันทึก tag ไปยัง document
     public function UpdateDocumentsDetail()
     {
         $me = UserHelper::GetEmployee();
 
             // $clearDEmployeeTag = self::deleteAll([
             //     'and',
             //     ['not in', 'tag_id', $this->tags_employee],
             //     ['document_id' => $this->document_id, 'name' => 'employee','crated_by' => $me->id]
             // ]);
             // foreach ($this->data_json['employee_tag'] as $key => $value):
             foreach ($this->tags_employee as $key => $value):
                 $check = DocumentsDetail::find()->where(['name' => 'employee','document_id' => $this->document_id, 'to_id' => $value])->one();
                 $new = $check ? $check : new DocumentsDetail();
                 $new->name = 'employee';
                 $new->document_id = $this->document_id;
                 $new->to_id = $value;
                 $new->save(false);
                 // if($new->employee->isDicrector())
                 // {
                 //     $dicrector = 1;
                 // }
                 
             endforeach;
             // return $dicrector;
     }

     public function updateTags()
     {
        try {
            $dicrector = 0;
            $clearDEmployeeTag = DocumentsDetail::deleteAll([
                'and',
                ['not in', 'to_id', $this->tags_employee],
                ['document_id' => $this->id, 'name' => 'employee']
            ]);

            foreach ($this->tags_employee as $key => $value):
                $check = DocumentsDetail::find()->where(['name' => 'employee', 'document_id' => $this->id, 'to_id' => $value])->one();
                $new = $check ? $check : new DocumentsDetail();
                $new->name = 'employee';
                $new->document_id = $this->id;
                $new->to_id = $value;
                $new->save(false);
            endforeach;
        } catch (\Throwable $th) {
        }
     }
     
     public function getAvatar($empid, $msg = '')
    {
        // try {
        $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
        $createdAt = Yii::$app->thaiFormatter->asDate($this->created_at, 'medium');
        // $msg = '<i class="fa-regular fa-comment"></i> ' . $this->data_json['comment'];
        // $msg = $employee->departmentName();
        return [
            'avatar' => $employee->getAvatar(false, $msg),
            'department' => $employee->departmentName(),
            'fullname' => $employee->fullname,
            'position_name' => $employee->positionName(),
            // 'product_type_name' => $this->data_json['product_type_name']
        ];
        // } catch (\Throwable $th) {
        //     return [
        //         'avatar' => '',
        //         'department' => '',
        //         'fullname' => '',
        //         'position_name' => '',
        //         'product_type_name' => ''
        //     ];
        // }
    }
    

}
