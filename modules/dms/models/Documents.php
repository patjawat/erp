<?php

namespace app\modules\dms\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use Imagine\Image\Box;
use yii\imagine\Image;
use yii\base\Component;
use yii\bootstrap5\Html;
use yii\web\UploadedFile;
use app\models\Categorise;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Employees;
use app\modules\dms\models\DocumentTags;
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

    public function rules()
    {
        return [
            // ['doc_time', 'match', 'pattern' => '/^([01][0-9]|2[0-3]):([0-5][0-9])$/', 'message' => 'กรุณากรอกเวลาในรูปแบบ HH:mm'],
            [['thai_year','topic','doc_number','secret','doc_speed','document_type', 'document_org', 'document_group', 'doc_regis_number','doc_time'], 'required'],
            [['topic'], 'string'],
            [['data_json','view_json', 'q','document_group','department_tag','employee_tag','req_approve','doc_receive_date','status'], 'safe'],
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
        
    // แสดงรูปแบบ format วันที่หนังสือ
    public function viewDocDate()
    {
        return Yii::$app->thaiFormatter->asDate($this->doc_date, 'medium');
    }

    public function Uploadx($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function viewCount()
    {
        if ($this->view_json === null) {
            return 0;
        }
        
        $views = $this->view_json;
        
        // If JSON string, decode it
        if (is_string($this->view_json)) {
            $views = json_decode($this->view_json, true);
        }
        
        return is_array($views) ? count($views) : 0;
   
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

        // ดึงข้อมูลจากฐานข้อมูล
        $items = Employees::find()
            ->select(['id', 'fname', 'lname']) // เลือกเฉพาะฟิลด์ที่ต้องการ
            ->where(['id' => $this->data_json['employee_tag']]) // เฉพาะรายการที่เคยเลือก
            ->andWhere(['status' => 1])
            ->andWhere(['>','id',1])
            ->asArray()
            ->all();
    
        // คืนค่าในรูปแบบ [id => 'fname lname']
        $result = [];
        foreach ($items as $item) {
            $result[$item['id']] = $item['fname'] . ' ' . $item['lname'];
        }
    
        return $result;
                    
    } catch (\Throwable $th) {
        return [];
    }
    }

// การติดตาม
public function listTrack()
{
    return DocumentTags::find()->where(['document_id' => $this->id])->andWhere(['in','name',['employee_tag','req_approve']])->all();
}
    // แสดงการส่งต่อรายบุคคล
    public function StackDocumentTags($tag_name)
    {
        // try {
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
                        "bs-title" => 'title',
                        "bs-html" => "true",
                        "bs-content" => $emp->fullname . "<br>" . $emp->positionName()
                    ]
                ]
            );
        }
        $data .= '</div>';
        return $data;
        // } catch (\Throwable $th) {
        // }
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



public function Upload()
{

    $ref = $this->ref;
    $name = 'document';
    list($initialPreview, $initialPreviewConfig) = FileManagerHelper::getInitialPreview($ref, $name);
    return FileInput::widget([
        'name' => 'upload_ajax[]',
        'options' => ['multiple' => true, 'accept' => '*'],
        'pluginOptions' => [
            'overwriteInitial' => false,
            'initialPreviewShowDelete' => true,
            'initialPreviewAsData' => true,
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => $initialPreviewConfig,
            'initialPreviewDownloadUrl' => Url::to(['@web/visit/{filename}']),
            'uploadUrl' => Url::to(['/filemanager/uploads/upload-ajax']),
            'uploadExtraData' => [
                'ref' => $ref,
                'name' => $name,
            ],
            'maxFileCount' => 1,
            'previewFileIconSettings' => [
                // configure your icon file extensions
                'doc' => '<i class="fas fa-file-word text-primary"></i>',
                'docx' => '<i class="fa-regular fa-file-word"></i>',
                'xls' => '<i class="fas fa-file-excel text-success"></i>',
                'ppt' => '<i class="fas fa-file-powerpoint text-danger"></i>',
                'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
                'zip' => '<i class="fas fa-file-archive text-muted"></i>',
                'htm' => '<i class="fas fa-file-code text-info"></i>',
                'txt' => '<i class="fas fa-file-alt text-info"></i>',
                'mov' => '<i class="fas fa-file-video text-warning"></i>',
                'mp3' => '<i class="fas fa-file-audio text-warning"></i>',
                'jpg' => '<i class="fas fa-file-image text-danger"></i>',
                'gif' => '<i class="fas fa-file-image text-muted"></i>',
                'png' => '<i class="fas fa-file-image text-primary"></i>',
            ],
        ],
        'pluginEvents' => [
        'filebatchuploadsuccess' => 'function(event, data) {
            console.log("Upload Success:", data);
            // alert("Upload completed successfully!");
          reloadPdf()
           $("#main-modal").modal("toggle");   
        }',
        'filedeleted' => 'function(event, key) {
            console.log("File deleted with key:", key);
            // alert("File deleted successfully!");
            reloadPdf()
        }',
    ],
    ]);
}
}
