<?php

namespace app\modules\dms\models;

use Yii;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\DocumentsDetail;
use app\modules\filemanager\models\Uploads;
use app\modules\dms\components\WebhookSender;
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
    public $q_status;
    public $date_filter;


    // public $tags_employee;
    public $reading;
    public $show_reading;
    public $file;

    public $date_start;
    public $date_end;
    public $q_department;

    // สำหรับการแสดงรายละเอียดในหน้า index DOCUMENT_DETAIL
    public $detail_id;
    public $detail_name;
    public $to_id;
    public $doc_read;




    public function rules()
    {
        return [
            // ['doc_time', 'match', 'pattern' => '/^([01][0-9]|2[0-3]):([0-5][0-9])$/', 'message' => 'กรุณากรอกเวลาในรูปแบบ HH:mm'],
            [['thai_year', 'topic', 'doc_number', 'secret', 'doc_speed', 'document_type', 'document_group', 'doc_regis_number', 'doc_time'], 'required'],
            ['document_org', 'required', 'when' => function ($model) {
                return $model->document_type !== 'DT2';
            }, 'whenClient' => "function (attribute, value) {
                return $('#documents-document_type').val() !== 'DT2';
            }"],
            [['topic'], 'string'],
            [['date_start', 'date_end', 'date_filter', 'file', 'reading', 'show_reading', 'tags_employee', 'tags_department', 'data_json', 'view_json', 'q', 'document_group', 'department_tag', 'employee_tag', 'req_approve', 'doc_transactions_date', 'status', 'ref', 'q_status', 'q_department'], 'safe'],
            [['doc_number', 'document_type', 'thai_year', 'doc_regis_number', 'doc_speed', 'secret', 'doc_date', 'doc_expire', 'doc_transactions_date', 'doc_time'], 'string', 'max' => 255],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->document_group === 'send' && $this->document_type === 'DT2') {
                if (empty($this->document_org)) {
                    $this->document_org = '0';
                }
            }
            // ล้าง tags_department เฉพาะฟอร์มส่ง (send) ที่ไม่ใช่ DT2 เท่านั้น
            // เพราะฟอร์มส่งจะโชว์ tree "ส่งถึงหน่วยงาน" เฉพาะ DT2 ส่วนประเภทอื่นใช้ Select2 document_org แทน
            // ฟอร์มรับ (receive) โชว์ tree "ส่งหน่วยงาน" เสมอ จึงต้องเก็บค่าไว้ ไม่ล้างทิ้ง
            if ($this->document_group === 'send' && $this->document_type !== 'DT2') {
                $this->tags_department = '';
            }
            return true;
        }
        return false;
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

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false; // ยกเลิกการลบ
        }

        // ตัวอย่าง: ลบไฟล์ที่แนบ
        // if (file_exists($this->file_path)) {
        //     unlink($this->file_path);
        // }

        // ตัวอย่าง: ลบข้อมูลลูก
        DocumentsDetail::deleteAll(['document_id' => $this->id]);

        return true; // ดำเนินการลบต่อ
    }

    //  ผู้สร้าง
    public function getCreateBy()
    {
        return $this->hasOne(Employees::class, ['user_id' => 'created_by'])
            ->select(['id', 'user_id', 'ref', 'prefix', 'fname', 'lname', 'department']);
    }

    // สถานะ
    public function getDocumentStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'document_status']);
    }

    public function getDocumentDetail()
    {
        return $this->hasOne(DocumentsDetail::class, ['document_id' => 'id']);
    }

    public function getDocumentDepartment()
    {
        return $this->hasOne(DocumentsDetail::class, ['document_id' => 'id'])
            ->andOnCondition(['d_department.name' => 'department']);
    }

    public function getDocumentTags()
    {
        return $this->hasOne(DocumentsDetail::class, ['document_id' => 'id'])
            ->andOnCondition(['d_tags.name' => 'tags']);
    }

    public function getDocRead()
    {
        return $this->hasMany(DocumentsDetail::class, ['document_id' => 'id'])
            ->andOnCondition(['d_read.name' => 'read']);
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
    // public function getDocumentTags()
    // {
    //     return $this->hasMany(DocumentTags::class, ['document_id' => 'id'])
    // }

    public function getCommittee()
    {
        return $this->hasMany(DocumentsDetail::class, ['document_id' => 'id']);
    }

    // แสดงหน่วยงาน หรือพนักงานที่ถูก tags
    public function IsEmpRead($detail_id)
    {
        $model = DocumentsDetail::find()
            ->Where(['id' => $detail_id])
            ->one();
        if ($model) {
            return [
                'status' => $model->doc_read ? true : false,
                'read_status' => $model->id,
                // 'read_status' => $model->doc_read ? 'อ่านแล้ว' : 'ยังไม่ได้อ่าน',
            ];
        }
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
        foreach ($models as $model) {

            // try {
            $line_id = $model->employee->user->line_id;
            $topic = $this->topic;
            // ส่ง msg ให้ Approve
            LineMsg::sendDocument($model, $line_id);
            // } catch (\Throwable $th) {

            // }
        }
    }

    // แสดงรูปแบบ format วันที่หนังสือ
    public function viewDocDate()
    {
        return ThaiDateHelper::formatThaiDate($this->doc_date);
    }

    // แสดงรูปแบบ format วันที่หนังสือ
    public function viewReceiveDate()
    {
        return ThaiDateHelper::formatThaiDate($this->doc_transactions_date);
    }

    public function UploadClipFile($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function viewCount()
    {
        try {
            return count($this->viewHistory());
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function viewFile($options = [])
    {
        // เช็กว่ามีการส่ง 'view' => true มาหรือไม่ ถ้าไม่มีให้ default เป็น false
        $view =  true;
        return FileManagerHelper::FileUpload($this->ref, 'document_clip', $view);
    }

    public function viewHistory()
    {
        return  DocumentsDetail::find()
            ->where(['document_id' => $this->id, 'name' => 'read'])
            ->andWhere(['IS NOT', 'doc_read', null])
            ->all();
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
            ->where(['name' => 'document_org', 'active' => 1])
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
        $query = Uploads::find(['ref' => $ref, 'name' => 'document_clip'])->count();
        $query = Uploads::find()->where(['ref' => $ref, 'name' => 'document_clip']);
        $count = $query->count();
        if ($count > 0) {
            return '<i class="fas fa-paperclip ms-1 text-muted fs-12"></i>';
        } else {
            return '';
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
            if ($this->tags_department) {

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
    }

    // แสดงรายชื่อหน่วยงานที่ Tags ไป
    public function viewTagsDepartment($limit = 3)
    {
        $departments = DocumentsDetail::find()
            ->where(['name' => 'department', 'document_id' => $this->id])
            ->all();

        $count = count($departments);
        if ($count === 0) {
            return '';
        }

        $names = [];
        $tooltipNames = [];
        foreach ($departments as $index => $detail) {
            $deptName = $detail->department ? $detail->department->name : ('หน่วยงาน #' . $detail->to_id);
            $tooltipNames[] = $deptName;
            if ($limit === null || $index < $limit) {
                $names[] = '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 ms-1">' . Html::encode($deptName) . '</span>';
            }
        }

        if ($limit !== null && $count > $limit) {
            $remaining = $count - $limit;
            $tooltipHtml = '';
            foreach ($tooltipNames as $n) {
                $tooltipHtml .= Html::encode($n) . '<br>';
            }
            $names[] = '<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 ms-1" data-bs-toggle="tooltip" data-bs-html="true" title="' . $tooltipHtml . '" style="cursor: pointer;"><i class="fa-solid fa-ellipsis"></i> +' . $remaining . '</span>';
        }

        return implode('', $names);
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
        $rows = DocumentsDetail::find()
            ->with(['document.documentStatus', 'employee'])
            ->where(['document_id' => $this->id])
            ->andWhere(['in', 'name', ['employee_tag', 'tags', 'employee', 'req_approve']])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        foreach ($rows as $row) {
            $row->status = $row->document ? $row->document->status : null;
        }

        return $rows;
    }

    // นับจำนวนที่ส่งต่อ
    public function countStackDocumentTags()
    {
        return DocumentsDetail::find()->where(['document_id' => $this->id, 'name' => 'comment'])->count();
    }


    public function StackDocumentTags($tag_name)
    {
        try {
            $querys = DocumentsDetail::find()
                ->where(['document_id' => $this->id, 'name' => $tag_name])
                ->orderBy(['id' => SORT_DESC])
                ->all();

            $toIds = array_column($querys, 'to_id');
            $emps = Employees::find()->where(['id' => $toIds])->indexBy('id')->all();

            $data = '<div class="avatar-stack d-flex align-items-center">';

            foreach ($querys as $key => $item) {
                $emp = $emps[$item->to_id] ?? null;
                if (!$emp) continue;

                $comment = nl2br(Html::encode((string) ($item->comment ?? '')));
                $popoverContent = '<p class="mb-0 small">' . $comment . '</p>';

                $data .= Html::a(
                    Html::img($emp->showAvatar(), [
                        'class' => 'avatar-sm rounded-circle border border-2 border-white shadow-sm',
                        'style' => 'margin-left:-10px; cursor:pointer; object-fit:cover;',
                    ]),
                    'javascript:void(0);',
                    [
                        'class' => 'avatar-item-link d-inline-block',
                        'role' => 'button',
                        'tabindex' => '0',
                        'aria-label' => $emp->fullname,
                        'data' => [
                            'bs-toggle' => 'popover',
                            'bs-trigger' => 'hover focus',
                            'bs-placement' => 'top',
                            'bs-html' => 'true',
                            'bs-title'=>$emp->fullname,
                            'bs-content' => $popoverContent,
                            'bs-container' => 'body',
                            'bs-custom-class'=>'custom-popover'
                        ]
                    ]
                );
            }

            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
            return '';
        }
    }

    //นับจำนวนหนังสือที่ส่งมา
    public function isReceive()
    {
        $count = WebhookSender::countReceivedDocuments();
        return $count;
    }

    //     public function StackDocumentTags($tag_name)
    //         {
    //         try {
    //         $querys = DocumentsDetail::find()
    //             ->where(['document_id' => $this->id, 'name' => $tag_name])
    //              ->orderBy(['id' => SORT_DESC])
    //             ->all();

    //         $count = count($querys) - 2;

    //         $data = '<div class="avatar-stack">';
    //         // preload employees
    //         $toIds = array_column($querys, 'to_id');
    //         $emps = Employees::find()
    //         ->where(['id' => $toIds])
    //         ->indexBy('id')

    //         ->all();

    //         foreach ($querys as $key => $item) {

    //             $emp = $emps[$item->to_id] ?? null;
    //             if (!$emp) continue;

    //             $data .= Html::a(
    //                 Html::img('@web/img/loading.gif', [
    //                     'class' => 'avatar-sm rounded-circle shadow lazyload',
    //                     'data' => [
    //                         'expand' => '-20',
    //                         'sizes' => 'auto',
    //                         'src' => $emp->showAvatar()
    //                     ]
    //                 ]),
    //                 ['/dms/documents/list-comment', 'id' => $item->document_id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'],
    //                 [
    //                     'class' => 'open-modal',
    //                     'data' => [
    //                         'size' => 'modal-md',
    //                         'bs-content' => $emp->fullname . '<br>' . $item->comment
    //                     ]
    //                 ]
    //             );
    //         }

    //         $data .= '</div>';
    //         return $data;
    //     } catch (\Throwable $th) {
    //         return '';
    //     }
    // }


    public function StackDocumentTagsLimit($tag_name)
    {
        try {
            $querys = DocumentsDetail::find()
                ->where(['document_id' => $this->id, 'name' => $tag_name])
                ->all();

            $count = count($querys) - 2;

            $data = '<div class="avatar-stack">';
            if ($count > 0) {
                $data .= Html::a(
                    '+' . $count,
                    ['/dms/documents/list-comment', 'id' => $this->id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'],
                    [
                        'class' => 'open-modal avatar-sm rounded-circle shadow bg-secondary text-white text-center p-2 fs-13',
                        'data' => ['size' => 'modal-md']
                    ]
                );
            }

            // preload employees
            $toIds = array_column($querys, 'to_id');
            $emps = Employees::find()->where(['id' => $toIds])->indexBy('id')->all();

            foreach ($querys as $key => $item) {
                if ($key > 1) continue;

                $emp = $emps[$item->to_id] ?? null;
                if (!$emp) continue;

                $comment = nl2br(Html::encode((string) ($item->comment ?? '')));

                $data .= Html::a(
                    Html::img('@web/img/loading.gif', [
                        'class' => 'avatar-sm rounded-circle shadow lazyload',
                        'data' => [
                            'expand' => '-20',
                            'sizes' => 'auto',
                            'src' => $emp->showAvatar()
                        ]
                    ]),
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
                            'bs-content' => '<strong>' . Html::encode($emp->fullname) . '</strong><br><p class="mb-0 small">' . $comment . '</p>',
                            'bs-container' => 'body',
                        ]
                    ]
                );
            }

            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
            return '';
        }
    }


    // แสดงข้อมูลผู้รับเข้า
    public function viewCreate()
    {
        try {
            $employee = $this->createBy; // ใช้ relation แทน query ใหม่

            if (!$employee) {
                throw new \Exception('Employee not found');
            }

            $createDate = ThaiDateHelper::formatThaiDate($this->doc_transactions_date) . ' ' . $this->doc_time;
            $encodedFullname = Html::encode($employee->fullname ?? '');
            $encodedCreateDate = Html::encode($createDate);
            $avatarUrl = $employee->ShowAvatar();
            $avatarHtml = Html::img($avatarUrl, [
                'class' => 'avatar avatar-sm bg-primary text-white rounded-circle flex-shrink-0',
                'alt' => $encodedFullname,
            ]);

            return [
                'photo' => $avatarUrl,
                'avatar' => <<<HTML
<div class="d-flex align-items-center">
    {$avatarHtml}
    <div class="avatar-detail">
        <p class="mb-0 small fw-bold text-muted">{$encodedFullname}</p>
        <p class="text-muted mb-0 fs-12">{$encodedCreateDate}</p>
    </div>
</div>
HTML,
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                'create_date' => $createDate
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


    // ข้อมูลการลงความเห็น
    public function documentApprove()
    {
        try {
            return DocumentsDetail::findOne(['document_id' => $this->id, 'name' => 'req_approve']);
        } catch (\Throwable $th) {
            return [];
        }
    }

    // นับจำนวนตามประเภท
    public function CountType($group)
    {
        return self::find()->where(['thai_year' => $this->thai_year, 'document_group' => $group])->count();
    }

    /**
     * นับจำนวนทุก document_group ในปีเดียวกัน 1 query (ใช้แทน CountType หลายครั้ง)
     * @return array ['receive' => int, 'send' => int, 'appointment' => int, 'announce' => int]
     */
    public function getCountsByGroup()
    {
        $rows = self::find()
            ->select(['document_group', 'cnt' => new Expression('COUNT(*)')])
            ->where(['thai_year' => $this->thai_year])
            ->groupBy('document_group')
            ->asArray()
            ->all();
        $counts = [
            'receive' => 0,
            'send' => 0,
            'appointment' => 0,
            'announce' => 0,
        ];
        foreach ($rows as $row) {
            if (isset($counts[$row['document_group']])) {
                $counts[$row['document_group']] = (int) $row['cnt'];
            }
        }
        return $counts;
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
    public function checkListDoceument() {}
}
