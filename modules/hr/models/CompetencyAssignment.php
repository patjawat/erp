<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ผู้ประเมินสมรรถนะของบุคลากร 1 คน ในรอบประเมินหนึ่ง — HR เป็นผู้กำหนด
 * แยกรายรอบเพราะหัวหน้าอาจเปลี่ยนกลางปี ส่วนรอบที่ไม่มีอะไรเปลี่ยนใช้การคัดลอกจากรอบก่อน
 *
 * @property int $id
 * @property int $emp_id
 * @property int $round_id
 * @property int|null $evaluator_id
 * @property string $source
 * @property string $status
 * @property string|null $note
 * @property string|null $assigned_at
 * @property Employees $employee
 * @property Employees|null $evaluator
 */
class CompetencyAssignment extends ActiveRecord
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_SUGGESTED = 'suggested';

    public const STATUS_DRAFT = 'draft'; // ยังกำหนดไม่ครบ (ขาดผู้ประเมินหรือขาดระดับ)
    public const STATUS_READY = 'ready'; // พร้อมให้ผู้ประเมินเริ่มประเมิน

    public static function tableName()
    {
        return '{{%hr_competency_assignment}}';
    }

    public function rules()
    {
        return [
            [['emp_id', 'round_id'], 'required'],
            [['emp_id', 'round_id', 'evaluator_id', 'created_by', 'updated_by'], 'integer'],
            [['note'], 'string', 'max' => 255],
            [['assigned_at', 'created_at', 'updated_at'], 'safe'],
            [['source'], 'in', 'range' => [self::SOURCE_MANUAL, self::SOURCE_SUGGESTED]],
            [['source'], 'default', 'value' => self::SOURCE_MANUAL],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_READY]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['evaluator_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['evaluator_id' => 'id'], 'skipOnEmpty' => true],
            [['evaluator_id'], 'compare', 'compareAttribute' => 'emp_id', 'operator' => '!=',
                'message' => 'ผู้ประเมินต้องไม่ใช่ตัวผู้ถูกประเมินเอง'],
            [['round_id'], 'exist', 'targetClass' => AppraisalRound::class, 'targetAttribute' => ['round_id' => 'id']],
            [['emp_id'], 'unique', 'targetAttribute' => ['round_id', 'emp_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'emp_id' => 'ผู้ถูกประเมิน',
            'evaluator_id' => 'ผู้ประเมิน',
            'round_id' => 'รอบประเมิน',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getEvaluator()
    {
        return $this->hasOne(Employees::class, ['id' => 'evaluator_id']);
    }

    public function getRound()
    {
        return $this->hasOne(AppraisalRound::class, ['id' => 'round_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ยังไม่พร้อม',
            self::STATUS_READY => 'พร้อมประเมิน',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    /**
     * ผู้ประเมินที่ระบบแนะนำตามผังองค์กร — เป็นเพียงค่าตั้งต้นให้ HR กดรับ ไม่ได้บังคับใช้
     *
     * ไล่จากหัวหน้าหน่วยที่สังกัด ถ้าได้ตัวเอง (เพราะเป็นหัวหน้าหน่วยนั้น) ให้ไต่ขึ้นหน่วยแม่
     * จนกว่าจะได้คนอื่น ถึงรากแล้วยังไม่ได้ก็คืน null ให้ HR ระบุเอง
     *
     * @param Employees[] $employees
     * @return array<int, int|null> emp_id => evaluator_id
     */
    public static function suggestEvaluators(array $employees): array
    {
        $out = [];
        foreach ($employees as $employee) {
            $out[(int) $employee->id] = self::suggestFor($employee);
        }
        return $out;
    }

    public static function suggestFor(Employees $employee): ?int
    {
        $node = $employee->empDepartment ?? null;
        if (!$node) {
            return null;
        }

        $empId = (int) $employee->id;
        $guard = 0;
        while ($node && $guard++ < 10) {
            $leaderId = self::leaderIdOf($node);
            if ($leaderId && $leaderId !== $empId) {
                return $leaderId;
            }
            $node = self::parentOf($node);
        }
        return null;
    }

    private static function leaderIdOf(Organization $node): ?int
    {
        // คอลัมน์ tree.data_json เป็นชนิด json — Yii ถอดรหัสมาเป็น array ให้แล้ว
        // แต่รองรับกรณีที่ได้สตริงมาด้วย เผื่อ driver/เวอร์ชันต่างกัน
        $json = $node->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        // ทะเบียนหน่วยงานเก็บคีย์นี้เป็น leader1 (ไม่มีขีดล่าง) เหมือนที่ Employees::ledOrganizations() ใช้
        $leaderId = is_array($json) ? ($json['leader1'] ?? null) : null;
        return $leaderId ? (int) $leaderId : null;
    }

    private static function parentOf(Organization $node): ?Organization
    {
        if ((int) $node->lvl <= 0) {
            return null;
        }
        return Organization::find()
            ->where(['root' => $node->root, 'lvl' => (int) $node->lvl - 1])
            ->andWhere(['<', 'lft', $node->lft])
            ->andWhere(['>', 'rgt', $node->rgt])
            ->one();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        if ($this->evaluator_id && !$this->assigned_at) {
            $this->assigned_at = $now;
        }
        return true;
    }
}
