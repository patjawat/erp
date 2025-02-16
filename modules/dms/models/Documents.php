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
use app\components\LineNotify;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\usermanager\models\User;
use app\modules\dms\models\DocumentsDetail;
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

    // public $tags_employee;
    public $reading;
    public $show_reading;

    public function rules()
    {
        return [
            // ['doc_time', 'match', 'pattern' => '/^([01][0-9]|2[0-3]):([0-5][0-9])$/', 'message' => 'กรุณากรอกเวลาในรูปแบบ HH:mm'],
            [['thai_year', 'topic', 'doc_number', 'secret', 'doc_speed', 'document_type', 'document_org', 'document_group', 'doc_regis_number', 'doc_time'], 'required'],
            [['topic'], 'string'],
            [['reading', 'show_reading', 'tags_employee', 'tags_department', 'data_json', 'view_json', 'q', 'document_group', 'department_tag', 'employee_tag', 'req_approve', 'doc_transactions_date', 'status', 'ref'], 'safe'],
            [['doc_number', 'document_type', 'document_org', 'thai_year', 'doc_regis_number', 'doc_speed', 'secret', 'doc_date', 'doc_expire', 'doc_transactions_date', 'doc_time'], 'string', 'max' => 255],
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
            // $this->reading = $this->viewCount()['reading'];
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

    // คำนวนเลขรับเข้า
    public function runNumber()
    {
        $model = self::find()
                ->select(['CAST(`doc_regis_number` AS UNSIGNED) AS doc_regis_number'])
                ->where([
                    'document_group' =>  $this->document_group,
                    'thai_year' => date('Y') + 543,
                ])
                ->orderBy(['CAST(`doc_regis_number` AS UNSIGNED)' => SORT_DESC])
                ->limit(1)
                ->one();
        if ($model) {
            return $model->doc_regis_number + 1;
        } else {
            return 1;
        }
    }

    public function sendMessage()
    {
        $models = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $this->id])->all();
        foreach($models as $model){
   
            // try {
                $line_id = $model->employee->user->line_id;
                $topic = $this->topic;
                // ส่ง msg ให้ Approve
                LineNotify::sendDocument($model,$line_id);
            // } catch (\Throwable $th) {
                
            // }
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
        return Yii::$app->thaiFormatter->asDate($this->doc_transactions_date, 'medium');
    }

    public function UploadClipFile($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function viewCount()
    {
        try {
            return count($this->view_json);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function viewHistory()
    {
        return  DocumentsDetail::find()->where(['document_id' => $this->id,'name' => 'tags'])->all();
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

    // ชั้นความลับ
    public function DocSecret()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_secret'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    // ชั้นความเร็ว
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
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function listEmployee()
    {
        // ดึงข้อมูลจากตาราง Employee
        $employees = Employees::find()->limit(5)->all();
        return ArrayHelper::map($employees, 'id', function ($model) {
            return $model->fullname;
        });
    }

    // ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {
            $employees = Employees::find()
                ->select(['id', 'concat(fname, " ", lname) as fullname'])
                // ->andWhere(['status' => '1'])
                // ->andWhere(['<>', 'id', '1'])
                ->asArray()
                ->all();

            // return ArrayHelper::map($employees,'id','fname');
            return ArrayHelper::map($employees, 'id', function ($model) {
                return $model['fullname'];
            });
        } catch (\Throwable $th) {
            return [];
        }
    }

    // update ค่า tags ไปหาบุคลอื่นๆ
    public function UpdateDocumentTags()
    {
        try {
            if($this->tags_department){

            $arrayDepartment = explode(',', $this->tags_department);
            $clearDepartmentTag = DocumentsDetail::deleteAll([
                'and',
                ['not in', 'to_id', $arrayDepartment],
                ['document_id' => $this->id, 'name' => 'department']
            ]);
            foreach ($arrayDepartment as $key => $value):
                $check = DocumentsDetail::find()->where(['name' => 'department', 'document_id' => $this->id, 'to_id' => $value])->one();
                $new = $check ? $check : new DocumentsDetail();
                $new->name = 'department';
              
                $new->document_id = $this->id;
                $new->to_id = $value;
                $new->save(false);
            endforeach;
                            
        }
            // code...
        } catch (\Throwable $th) {
        }

        // try {
        //     $dicrector = 0;
        //     $clearDEmployeeTag = DocumentsDetail::deleteAll([
        //         'and',
        //         ['not in', 'to_id', $this->tags_employee],
        //         ['document_id' => $this->id, 'name' => 'comment']
        //     ]);

        //     foreach ($this->tags_employee as $key => $value):
        //         $check = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $this->id, 'to_id' => $value])->one();
        //         $new = $check ? $check : new DocumentsDetail();
        //         $new->name = 'comment';
        //         $new->data_json = ['comment' => $this->data_json['comment']];
        //         $new->document_id = $this->id;
        //         $new->to_id = $value;
        //         $new->save(false);
        //     endforeach;
        // } catch (\Throwable $th) {
        // }
    }

    // รายการแสดงความเห็น
    public function listComment()
    {
        return DocumentsDetail::find()->where(['document_id' => $this->id, 'name' => 'comment'])->orderBy([
            'id' => SORT_ASC,
        ])->all();
    }

    // การติดตาม
    public function listTrack()
    {
        return DocumentTags::find()->where(['document_id' => $this->id])->andWhere(['in', 'name', ['employee_tag', 'req_approve']])->all();
    }

    // นับจำนวนที่ส่งต่อ
    public function countStackDocumentTags()
    {
        return DocumentsDetail::find()->where(['document_id' => $this->id, 'name' => 'comment'])->count();
    }

    // แสดงการส่งต่อรายบุคคล
    public function StackDocumentTags($tag_name)
    {
        try {
            $querys = DocumentsDetail::find()->where(['document_id' => $this->id, 'name' => $tag_name])->all();
            $count = count($querys) - 2;
            $data = '';
            $data .= '<div class="avatar-stack">';
            $count > 0 ? $data .= Html::a('+' . $count, ['/dms/documents/list-comment', 'id' => $this->id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'], ['class' => 'open-modal avatar-sm rounded-circle shadow bg-secondary text-white text-center p-2 fs-13', 'data' => [
                'size' => 'modal-md',
            ]]) : '';
            foreach ($querys as $key => $item) {
                $emp = Employees::findOne(['id' => $item->to_id]);
                if ($key <= 1) {
                    $data .= Html::a(
                        Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                            'data' => [
                                'expand' => '-20',
                                'sizes' => 'auto',
                                'src' => $emp->showAvatar()
                            ]]),
                        ['/dms/documents/list-comment', 'id' => $item->document_id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'],
                        [
                            'class' => 'open-modal',
                            'data' => [
                                'size' => 'modal-md',
                                'bs-trigger' => 'hover focus',
                                'bs-toggle' => 'popover',
                                'bs-placement' => 'top',
                                'bs-title' => '<i class="fa-regular fa-comment"></i> ความคิดเห็น',
                                'bs-html' => 'true',
                                'bs-content' => $emp->fullname . '<br>' . $item->comment
                            ]
                        ]
                    );
                } else {
                }
            }

            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
            // return 'เกิดข้อผิดพลาด';
        }
    }


    // แสดงข้อมูลผู้รับเข้า
    public function viewCreate()
    {
        try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
            $createDate = Yii::$app->thaiFormatter->asDate($this->created_at, 'medium') . ' ' . $this->doc_time;
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

    // ข้อมูลการลงความเห็น
    public function documentApprove()
    {
        try {
            return DocumentTags::findOne(['document_id' => $this->id, 'name' => 'req_approve']);
        } catch (\Throwable $th) {
            return [];
        }
    }

    // นับจำนวนตามประเภท
    public function CountType($group)
    {
        return self::find()->where(['thai_year' => $this->thai_year, 'document_group' => $group])->count();
    }

        

    // รายงานแยกตามเดือน
    public function getChartSummary($name)
    {
        return self::find()
            ->select([
                'thai_year',
                'm1' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 1 THEN 1 END)'),
                'm2' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 2 THEN 1 END)'),
                'm3' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 3 THEN 1 END)'),
                'm4' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 4 THEN 1 END)'),
                'm5' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 5 THEN 1 END)'),
                'm6' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 6 THEN 1 END)'),
                'm7' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 7 THEN 1 END)'),
                'm8' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 8 THEN 1 END)'),
                'm9' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 9 THEN 1 END)'),
                'm10' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 10 THEN 1 END)'),
                'm11' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 11 THEN 1 END)'),
                'm12' => new Expression('COUNT(CASE WHEN MONTH(doc_transactions_date) = 12 THEN 1 END)'),
            ])
            ->where(['thai_year' => $this->thai_year, 'document_group' => $name])
            ->groupBy('thai_year')
            ->asArray()
            ->one();
    }

    // ตารางประเภทหนังสือแยกตามหน่วยงานที่ส่งมา 10 อันดับ
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
            ->asArray()
            ->all();
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
            ->asArray()
            ->all();
    }

    // ชั้นเร็ว
    public function summaryDocSpeed()
    {
        return self::find()
            ->select([
                new Expression('doc_speed AS title'),  // ใช้ IFNULL สำหรับค่า null
                new Expression('COUNT(id) AS total'),  // นับจำนวนเอกสาร
            ])
            ->where(['thai_year' => $this->thai_year])
            ->groupBy(['doc_speed'])  // กลุ่มตาม code ของ categorise
            ->asArray()
            ->all();
    }

    // public function Upload($refData = null)
    // {

    // }
}
