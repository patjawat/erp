<?php

namespace app\modules\dms\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\Documents;

/**
 * This is the model class for table "document_tags".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อการ tags เอกสาร
 * @property string|null $doc_number เลขที่หนังสือ
 * @property string|null $emp_id
 * @property string|null $status สถานะ
 * @property string|null $department_id
 * @property string|null $data_json
 */
class DocumentTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $comment;
    public $tags_employee;

    public static function tableName()
    {
        return 'document_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'tag_id', 'reading','tags_employee'], 'safe'],
            [['ref', 'name', 'doc_number', 'document_id', 'tag_id', 'status', 'department_id', 'document_org_id'], 'string', 'max' => 255],
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
            'name' => 'ชื่อการ tags เอกสาร',
            'doc_number' => 'เลขที่หนังสือ',
            'emp_id' => 'Emp ID',
            'status' => 'สถานะ',
            'department_id' => 'Department ID',
            'document_org_id' => 'จากหน่วยงาน',
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
            $this->comment = isset($this->data_json['comment']) ? $this->data_json['comment'] : '';
        } catch (\Throwable $th) {
        }

        parent::afterFind();
    }

    // สถานะ
    public function getDocumentStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'document_status']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'tag_id']);
    }

    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

    // นับเวลาที่ผ่านมาแล้ว
    public function createdDays()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    public function getAvatar($empid, $msg = '')
    {
        // try {
        $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
        $createdAt = Yii::$app->thaiFormatter->asDate($this->created_at, 'medium');
        $msg = '<i class="fa-regular fa-comment"></i> ' . $this->data_json['comment'];
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

    public function viewCreate()
    {
        try {
            return Yii::$app->thaiFormatter->asDate($this->created_at, 'medium') . ' ' . $this->doc_time;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function listCommentTags()
    {
        $model = Categorise::find()->where(['name' => 'document_comment_tags'])->all();

        return ArrayHelper::map($model, 'title', 'title');
    }

    // ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {
            // หาบุคคลที่เคย tag ไปแล้ว
            $tags = self::find()->select('tag_id')
            
            ->where(['name' => 'employee', 'document_id' => $this->document_id])
            ->andWhere(['<>', 'tag_id',$this->created_by])
            ->All();
            $allTag = ArrayHelper::getColumn($tags, 'tag_id');
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
    public function UpdateDocumentTags()
    {
        $me = UserHelper::GetEmployee();
        try {
            $dicrector = 0;

            // $clearDEmployeeTag = self::deleteAll([
            //     'and',
            //     ['not in', 'tag_id', $this->tags_employee],
            //     ['document_id' => $this->document_id, 'name' => 'employee','crated_by' => $me->id]
            // ]);
            // foreach ($this->data_json['employee_tag'] as $key => $value):
            foreach ($this->tags_employee as $key => $value):
                $check = DocumentTags::find()->where(['name' => 'employee','document_id' => $this->document_id, 'tag_id' => $value])->one();
                $new = $check ? $check : new DocumentTags();
                $new->name = 'employee';
                $new->document_id = $this->document_id;
                $new->tag_id = $value;
                $new->save(false);
                // if($new->employee->isDicrector())
                // {
                //     $dicrector = 1;
                // }
                
            endforeach;
            // return $dicrector;
        } catch (\Throwable $th) {
            // throw $th;
        }
    }
    // public function updateTagToDocument()
    // {
    //     try {
    //         $model = Documents::findOne($this->document_id);
    //         $model->data_json['employee_tag'] = $this->tag_id;
    //         $model->save();
    //     } catch (\Throwable $th) {
    //         return false;
    //     }
    // }
}
