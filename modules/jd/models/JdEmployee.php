<?php

namespace app\modules\jd\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\db\ActiveRecord;

/**
 * JD ของแต่ละพนักงาน (โหลดจาก template ได้ แก้ไข/เพิ่มได้)
 *
 * @property int $id
 * @property int $emp_id
 * @property int|null $template_id template ที่โหลดมา
 * @property string $created_at
 * @property string|null $updated_at
 * @property JdEmployeeSection[] $sections
 * @property Employees $employee
 * @property JdTemplate|null $template
 */
class JdEmployee extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETIRED = 'retired';
    public static function tableName()
    {
        return '{{%jd_employee}}';
    }

    public function rules()
    {
        return [
            [['emp_id'], 'required'],
            [['emp_id', 'template_id', 'revision_no', 'supersedes_id', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'effective_from', 'effective_to', 'approved_at'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_RETIRED]],
            [['position_code', 'department_code'], 'string', 'max' => 64],
            [['position_title', 'department_title'], 'string', 'max' => 255],
            [['revision_no'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['template_id'], 'exist', 'targetClass' => JdTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'template_id' => 'Template ที่ใช้',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getSections()
    {
        return $this->hasMany(JdEmployeeSection::class, ['jd_employee_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getTemplate()
    {
        return $this->hasOne(JdTemplate::class, ['id' => 'template_id']);
    }

    public function getSupersedes()
    {
        return $this->hasOne(self::class, ['id' => 'supersedes_id']);
    }

    public function getAcknowledgement()
    {
        return $this->hasOne(JdEmployeeAcknowledgement::class, ['jd_employee_id' => 'id'])
            ->andOnCondition(['emp_id' => $this->emp_id]);
    }

    public function getOpenChangeRequest()
    {
        return $this->hasOne(JdChangeRequest::class, ['jd_employee_id' => 'id'])
            ->andOnCondition(['status' => JdChangeRequest::openStatuses()])
            ->orderBy(['submitted_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getPublisherEmployee()
    {
        return $this->hasOne(Employees::class, ['user_id' => 'approved_by']);
    }

    public function getApprovalRows()
    {
        return $this->hasMany(\app\modules\approveV2\models\Approve::class, ['from_id' => 'id'])
            ->andOnCondition(['name' => \app\modules\jd\services\JdApprovalService::APPROVE_NAME])
            ->orderBy(['level' => SORT_ASC]);
    }

    public static function findCurrent(int $employeeId): ?self
    {
        $today = date('Y-m-d');
        return self::find()
            ->where(['emp_id' => $employeeId, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['or', ['effective_from' => null], ['<=', 'effective_from', $today]])
            ->andWhere(['or', ['effective_to' => null], ['>=', 'effective_to', $today]])
            ->with(['sections', 'template'])
            ->orderBy(['effective_from' => SORT_DESC, 'revision_no' => SORT_DESC, 'id' => SORT_DESC])
            ->one();
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_PENDING => 'รอลงนาม',
            self::STATUS_ACTIVE => 'ฉบับปัจจุบัน',
            self::STATUS_RETIRED => 'สิ้นสุดแล้ว',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
                if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                    $this->created_by = (int) Yii::$app->user->id;
                }
            }
            $this->updated_at = $now;
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                $this->updated_by = (int) Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }
}
