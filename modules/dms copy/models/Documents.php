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
use app\modules\dms\models\DocumentTags;
use app\modules\usermanager\models\User;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property string|null $doc_number เลขที่หนังสือ
 * @property string|null $topic ชื่อเรื่อง
 * @property string|null $document_type ประเภทหนังสือ
 * @property string|null $document_org_id จากหน่วยงาน
 * @property string|null $thai_year ปี พ.ศ.
 * @property string|null $doc_regis_number เลขรับ
 * @property string|null $doc_speed ชั้นความเร็ว
 * @property string|null $secret ชั้นความลับ
 * @property string|null $doc_date วันที่หนังสือ
 * @property string|null $doc_expire วันหมดอายุ
 * @property string|null $doc_date ลงวันรับเข้า
 * @property string|null $doc_time เวลารับ
 * @property string|null $data_json
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * {@inheritdoc}
     */
    public $q;
    public $tags;
    public $reading;

    public function rules()
    {
        return [
            // ['doc_time', 'match', 'pattern' => '/^([01][0-9]|2[0-3]):([0-5][0-9])$/', 'message' => 'กรุณากรอกเวลาในรูปแบบ HH:mm'],
            [['thai_year','topic','doc_number','secret','doc_speed','document_type', 'document_org', 'document_group', 'doc_regis_number','doc_time'], 'required'],
            [['topic'], 'string'],
            [['reading','tags','department_tag','data_json','view_json', 'q','document_group','department_tag','employee_tag','req_approve','doc_receive_date','status','ref'], 'safe'],
            [['doc_number', 'document_type', 'document_org', 'thai_year', 'doc_regis_number', 'doc_speed', 'secret', 'doc_date', 'doc_expire', 'doc_receive_date', 'doc_time'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'doc_number' => 'เลขที่หนังสือ',
            'topic' => 'ชื่อเรื่อง',
            'document_type' => 'ประเภทหนังสือ',
            'document_org' => 'จากหน่วยงาน',
            'thai_year' => 'ปี พ.ศ.',
            'doc_regis_number' => 'เลขรับ',
            'doc_speed' => 'ชั้นความเร็ว',
            'secret' => 'ชั้นความลับ',
            'doc_date' => 'วันที่หนังสือ',
            'doc_expire' => 'วันหมดอายุ',
            'doc_date' => 'ลงวันรับเข้า',
            'doc_time' => 'เวลารับ',
            'data_json' => 'Data Json',
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
    public function afterFind()
    {
        try {
            $this->reading = $this->viewCount()['reading'];
        } catch (\Throwable $th) {
        }

        parent::afterFind();
    }
    

        // สถานะ
        public function getDocumentStatus()
        {
            return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'document_status']);
        }
        
    // section Relationships
    public function getDocumentOrg()
    {
        return $this->hasOne(Categorise::class, ['code' => 'document_org'])->andOnCondition(['name' => 'document_org']);
    }

    // ประเภทหนังสือ
    public function getDocumentType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'document_type'])->andOnCondition(['name' => 'document_type']);
    }

        // การ tags หนังสือ
        public function getDocumentTags()
        {
            return $this->hasMany(DocumentTags::class, ['document_id' => 'id']);
        }


        //คำนวนเลขรับเข้า
        public function runNumber()
        {
            $model =  self::find()->select('doc_regis_number')->where(['thai_year' => (date('Y')+543)])->orderBy(['doc_regis_number' => SORT_DESC])->one();
            if($model){
                return $model->doc_regis_number+1;
            }else{
                return 1;
            }
        }
        

        public function sendMessage($lineId)
        {
            $message = $thi->topic;
            
            try {

                $client = new Client();
                // $token = 'u090Q5IjiP3BOCPbGGdn1Vdj16AZ6mVtz2SV9Bd22ce';
                $token = $lineId;
                $response = $client
                    ->createRequest()
                    ->setMethod('POST')
                    ->setUrl('https://notify-api.line.me/api/notify')
                    ->setHeaders(['Authorization' => 'Bearer ' . $token])
                    ->setData(['message' => $message])
                    ->send();
    
                if ($response->isOk) {
                    return $response->data;
                } else {
                    throw new Exception('Failed to send message: ' . $response->content);
                }
                // code...
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
    // แสดงรูปแบบ format วันที่หนังสือ
    public function viewDocDate()
    {
        return Yii::$app->thaiFormatter->asDate($this->doc_date, 'medium');
    }

        // แสดงรูปแบบ format วันที่หนังสือ
        public function viewReceiveDate()
        {
            return Yii::$app->thaiFormatter->asDate($this->doc_receive_date, 'medium');
        }
        
    public function UploadClipFile($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function viewCount()
    {
        try {
            $me = UserHelper::GetEmployee();
            $model =    DocumentTags::find()->where(['name' => 'reading','document_id' => $this->id])->count();
            $reading =  DocumentTags::find()->where(['name' => 'reading','document_id' => $this->id,'emp_id' => $me->id])->count();
            return [
                'count' => $model,
                'reading' => $reading
            ];
        } catch (\Throwable $th) {
            return [
                'count' => 0,
                'reading' => 0
            ];
        }
       
   
}
    // แสดงปีงบประมานทั้งหมด

    public function ListThaiYear()
    {
        $model = self::find()
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

    // แสดงรายการสถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

        // แสดงรายการประเภทเอกสาร
        public function ListDocumentType()
        {
            $model = Categorise::find()
                ->where(['name' => 'document_type'])
                ->asArray()
                ->all();
            return ArrayHelper::map($model, 'code', 'title');
        }
        

    // แสดงหน่วยงานภานนอก
    public function ListDocumentOrg()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_org'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }


    
    //ชั้นความลับ
    public function DocSecret()
    {
        $model = Categorise::find()
        ->where(['name' => 'document_secret'])
        ->asArray()
        ->all();
    return ArrayHelper::map($model, 'code', 'title');
    }

    //ชั้นความเร็ว
    public function DocSpeed()
    {
        $model = Categorise::find()
        ->where(['name' => 'urgent'])
        ->asArray()
        ->all();
    return ArrayHelper::map($model, 'code', 'title');
    }
    // ตรวจเช็คว่ามีการแบไฟล์หรือไม่
    public function isFile()
    {
        $ref = $this->ref;
        $directory = Yii::getAlias('@app/modules/filemanager/fileupload/' . $ref . '/');
        $checkFileUpload = Uploads::findOne(['ref' => $ref]);
        if ($checkFileUpload) {
            $fileName = $checkFileUpload->real_filename;
            $filePath = $directory . $fileName;

            // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
            if (file_exists($filePath) && is_file($filePath)) {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function listEmployee()
    {
        // ดึงข้อมูลจากตาราง Employee
         $employees = Employees::find()->limit(5)->all();
         return ArrayHelper::map($employees, 'id', function($model){
            return $model->fullname;
         });
    }

    //ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {

         $employees = Employees::find()
            ->select(['id', 'concat(fname, " ", lname) as fullname'])
            ->andWhere(['status' => '1'])
            ->andWhere(['<>','id','1'])
            ->asArray()
            ->all();
        
            // return ArrayHelper::map($employees,'id','fname');
        return ArrayHelper::map($employees,'id',function($model){
            return $model['fullname'];
        });
                    
    } catch (\Throwable $th) {
        return [];
    }
    }
// update ค่า tags ไปหาบุคลอื่นๆ
public function UpdateToTags()
{

    foreach ($this->data_json['department_tag'] as $key => $item) {
        $model = new DocumentTags();
        $model->document_id = $this->id;
        $model->name = 'department';
        $model->tag_id = $item;
        $model->save();
    }
    // $modelIn = DocumentTags::find()->where(['document_id' => $this->id,'name' => 'employee_tag'])
    // ->andFilterWhere(['IN','emp_id',$this->department_tag])
    // ->all();

    // $modelNotIn = DocumentTags::find()->where(['document_id' => $this->id,'name' => 'employee_tag'])
    // ->andFilterWhere(['not in','emp_id',$this->tags])
    // ->all();
    // return $modelNotIn;
}
    //รายการแสดงความเห็น
    public function listComment()
{
    return DocumentTags::find()->where(['document_id' => $this->id,'name' => 'comment'])->all();
}
// การติดตาม
public function listTrack()
{
    return DocumentTags::find()->where(['document_id' => $this->id])->andWhere(['in','name',['employee_tag','req_approve']])->all();
}
    // แสดงการส่งต่อรายบุคคล
    public function StackDocumentTags($tag_name)
    {
        try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (DocumentTags::find()->where(['document_id' => $this->id,'name' => $tag_name])->all() as $key => $item) {
            $emp = Employees::findOne(['id' => $item->emp_id]);
            $data .= Html::a(
                Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
        'data' => [
            'expand' => '-20',
            'sizes' => 'auto',
            'src' =>$emp->showAvatar()
            ]
    ]),
                ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                [
                    'class' => 'open-modal',
                    'data' => [
                        'size' => 'modal-md',
                        "bs-trigger" => "hover focus",
                        "bs-toggle" => "popover",
                        "bs-placement" => "top",
                        "bs-title" => '<i class="fa-regular fa-comment"></i> ความคิดเห็น',
                        "bs-html" => "true",
                        "bs-content" => $emp->fullname . "<br>" . $item->comment
                    ]
                ]
            );
        }
        $data .= '</div>';
        return $data;
        } catch (\Throwable $th) {
            return 'เกิดข้อผิดพลาด';
        }
    }
//แสดงข้อมูลผู้รับเข้า
    public function viewCreate()
    {
            try {

                
                $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
                $createDate = Yii::$app->thaiFormatter->asDate($this->created_at, 'medium').' '.$this->doc_time;
                // $msg = $employee->departmentName();
                return [
                    'avatar' => $employee->getAvatar(false, $createDate),
                    'department' => $employee->departmentName(),
                    'fullname' => $employee->fullname,
                    'position_name' => $employee->positionName(),
                    'create_date' => $createDate
                ];
            } catch (\Throwable $th) {
                return [
                    'avatar' => '',
                    'department' => '',
                    'fullname' => '',
                    'position_name' => '',
                    'product_type_name' => ''
                ];
            }
    }

//ข้อมูลการลงความเห็น
    public function documentApprove()
{
    try {
        return DocumentTags::findOne(['document_id' => $this->id,'name'=> 'req_approve']);
    } catch (\Throwable $th) {
        return [];
    }
}   
//นับจำนวนตามประเภท
public function CountType($group)
{
    return self::find()->where(['thai_year' => $this->thai_year,'document_group' => $group])->count();
}
// รายงานแยกตามเดือน
public function getChartSummary($name)
{
    return self::find()->select([
        'thai_year',
        'm1' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 1 THEN 1 END)'),
        'm2' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 2 THEN 1 END)'),
        'm3' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 3 THEN 1 END)'),
        'm4' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 4 THEN 1 END)'),
        'm5' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 5 THEN 1 END)'),
        'm6' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 6 THEN 1 END)'),
        'm7' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 7 THEN 1 END)'),
        'm8' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 8 THEN 1 END)'),
        'm9' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 9 THEN 1 END)'),
        'm10' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 10 THEN 1 END)'),
        'm11' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 11 THEN 1 END)'),
        'm12' => new Expression('COUNT(CASE WHEN MONTH(doc_date) = 12 THEN 1 END)'),
    ])
    ->where(['thai_year' => $this->thai_year,'document_group' => $name])
    ->groupBy('thai_year')
            ->asArray()
            ->one();
}

//ตารางประเภทหนังสือแยกตามหน่วยงานที่ส่งมา 10 อันดับ
public function summaryOrg()
{
        return self::find()
        ->select([
            'c.title as org_name',
            'd.thai_year',
            new Expression('COUNT(CASE WHEN d.document_type = "DT1" THEN 1 END) AS DT1'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT2" THEN 1 END) AS DT2'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT3" THEN 1 END) AS DT3'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT4" THEN 1 END) AS DT4'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT5" THEN 1 END) AS DT5'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT6" THEN 1 END) AS DT6'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT7" THEN 1 END) AS DT7'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT8" THEN 1 END) AS DT8'),
            new Expression('COUNT(CASE WHEN d.document_type = "DT9" THEN 1 END) AS DT9'),
            new Expression('COUNT(CASE WHEN d.document_type NOT IN ("DT1", "DT2", "DT3", "DT4", "DT5", "DT8", "DT9") THEN 1 END) AS other_count'),
            new Expression('count(d.id) as total_count'),
            
        ])
        ->alias('d')
        ->leftJoin(['c' => Categorise::tableName()], [
            'and',
            'c.code = d.document_org',
            ['c.name' => 'document_org'],
        ])
        ->where(['thai_year' => $this->thai_year])
        ->groupBy(['c.code', 'd.thai_year'])
        ->orderBy([
            'd.thai_year' => SORT_DESC,
            'total_count' => SORT_DESC,
        ])
        ->limit(10)
        ->asArray()->all();
}
// สรุปประเภทหนังสือรับ
public function summaryDocType()
{
    return self::find()
    ->select([
        new Expression('IFNULL(c.title, "ไม่ระบุ") AS title'),  // ใช้ IFNULL สำหรับค่า null
        new Expression('COUNT(d.id) AS total'),  // นับจำนวนเอกสาร
    ])
    ->alias('d')
    ->leftJoin(['c' => Categorise::tableName()], [
        'and',
        'c.code = d.document_type',
        ['c.name' => 'document_type'],
    ])
    ->where(['thai_year' => $this->thai_year])
    ->groupBy(['c.code'])  // กลุ่มตาม code ของ categorise
        ->asArray()->all();
}

//ชั้นเร็ว
public function summaryDocSpeed()
{
    return self::find()
    ->select([
        new Expression('doc_speed AS title'),  // ใช้ IFNULL สำหรับค่า null
        new Expression('COUNT(id) AS total'),  // นับจำนวนเอกสาร
    ])
    ->where(['thai_year' => $this->thai_year])
    ->groupBy(['doc_speed'])  // กลุ่มตาม code ของ categorise
    ->asArray()->all();
}

// public function Upload($refData = null)
// {

   
// }
}
