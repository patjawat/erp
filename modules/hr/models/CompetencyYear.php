<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * สมรรถนะที่ประกาศใช้ในปีงบประมาณหนึ่ง — ตัวจริงที่ใบประเมินอ้างถึง
 * แก้ข้อความของปีหนึ่งจะไม่กระทบปีอื่น เพราะระดับ/ข้อพฤติกรรมผูกกับแถวนี้
 *
 * @property int $id
 * @property int $competency_id
 * @property int $fiscal_year
 * @property string $name
 * @property string|null $definition
 * @property int $sort_order
 * @property string $status
 * @property string|null $note
 * @property Competency $competency
 * @property CompetencyLevel[] $levels
 */
class CompetencyYear extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';     // ยังร่างอยู่ ยังไม่ให้ใช้ประเมิน
    public const STATUS_ACTIVE = 'active';   // ประกาศใช้ในปีนั้น
    public const STATUS_RETIRED = 'retired'; // ยกเลิกกลางปี เก็บไว้อ้างอิง

    /** ชื่อสมรรถนะแม่ ใช้ตอนสร้างใหม่จากฟอร์มเดียว */
    public $competency_name;

    public static function tableName()
    {
        return '{{%hr_competency_year}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'name'], 'required'],
            [['competency_id', 'fiscal_year', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['definition', 'note'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['competency_name'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_RETIRED]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['sort_order'], 'default', 'value' => 0],
            [['competency_id'], 'exist', 'targetClass' => Competency::class, 'targetAttribute' => ['competency_id' => 'id']],
            [['competency_id'], 'unique',
                'targetAttribute' => ['competency_id', 'fiscal_year'],
                'message' => 'สมรรถนะนี้ถูกกำหนดไว้ในปีงบประมาณนี้แล้ว',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'competency_id' => 'สมรรถนะในทะเบียนกลาง',
            'fiscal_year' => 'ปีงบประมาณ',
            'name' => 'ชื่อสมรรถนะ',
            'definition' => 'คำจำกัดความ',
            'sort_order' => 'ลำดับ',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getCompetency()
    {
        return $this->hasOne(Competency::class, ['id' => 'competency_id']);
    }

    public function getLevels()
    {
        return $this->hasMany(CompetencyLevel::class, ['competency_year_id' => 'id'])
            ->orderBy(['level_no' => SORT_ASC]);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_ACTIVE => 'ประกาศใช้',
            self::STATUS_RETIRED => 'ยกเลิกใช้',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    /** จำนวนระดับและจำนวนข้อพฤติกรรมของสมรรถนะนี้ ใช้แสดงในตารางรายการ */
    public function getLevelCount(): int
    {
        return (int) CompetencyLevel::find()->where(['competency_year_id' => $this->id])->count();
    }

    public function getIndicatorCount(): int
    {
        return (int) CompetencyIndicator::find()
            ->alias('i')
            ->innerJoin(['l' => CompetencyLevel::tableName()], 'l.id = i.level_id')
            ->where(['l.competency_year_id' => $this->id])
            ->count();
    }

    /** ปีงบประมาณที่มีการกำหนดสมรรถนะไว้แล้ว (มาก→น้อย) */
    public static function definedYears(string $type = Competency::TYPE_CORE): array
    {
        return array_map('intval', self::find()
            ->alias('cy')
            ->select('cy.fiscal_year')
            ->distinct()
            ->innerJoin(['c' => Competency::tableName()], 'c.id = cy.competency_id')
            ->where(['c.type' => $type])
            ->orderBy(['cy.fiscal_year' => SORT_DESC])
            ->column());
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
        return true;
    }
}
