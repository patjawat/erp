<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ใบประเมินสมรรถนะของบุคลากร 1 คน ในรอบหนึ่ง
 *
 * @property int $id
 * @property int $assignment_id
 * @property string $status
 * @property float|null $score_percent
 * @property string|null $comment
 * @property CompetencyAssignment $assignment
 * @property CompetencyScore[] $scores
 */
class CompetencyEvaluation extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';         // กำลังให้คะแนน ยังไม่ครบ
    public const STATUS_COMPLETED = 'completed'; // ให้คะแนนครบแล้ว รอส่งพร้อมกันทั้งชุด
    public const STATUS_SUBMITTED = 'submitted'; // ส่งผลแล้ว ล็อกแก้ไข

    public static function tableName()
    {
        return '{{%hr_competency_evaluation}}';
    }

    public function rules()
    {
        return [
            [['assignment_id'], 'required'],
            [['assignment_id', 'created_by', 'updated_by'], 'integer'],
            [['score_percent'], 'number'],
            [['comment'], 'string'],
            [['completed_at', 'submitted_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_COMPLETED, self::STATUS_SUBMITTED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['assignment_id'], 'unique'],
            [['assignment_id'], 'exist', 'targetClass' => CompetencyAssignment::class, 'targetAttribute' => ['assignment_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'score_percent' => 'คะแนนสมรรถนะ',
            'comment' => 'ข้อเสนอแนะของผู้ประเมิน',
            'status' => 'สถานะ',
        ];
    }

    public function getAssignment()
    {
        return $this->hasOne(CompetencyAssignment::class, ['id' => 'assignment_id']);
    }

    public function getScores()
    {
        return $this->hasMany(CompetencyScore::class, ['evaluation_id' => 'id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'กำลังประเมิน',
            self::STATUS_COMPLETED => 'ประเมินครบแล้ว',
            self::STATUS_SUBMITTED => 'ส่งผลแล้ว',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * คิดคะแนนสมรรถนะรวมตามสูตรของแบบฟอร์มเดิม
     *
     *   คะแนนแต่ละสมรรถนะ = ผลรวมคะแนนที่ได้ ÷ ผลรวมคะแนนเต็มของระดับที่ประเมิน × 100
     *   คะแนนรวม          = เฉลี่ยสมรรถนะที่ประเมินจริง (น้ำหนักเท่ากันทุกตัว = 100 ÷ จำนวนที่ประเมิน)
     *
     * @param array<int, int> $expectedLevels competency_year_id => ระดับที่คาดหวัง
     * @param array<int, array<int, CompetencyIndicator[]>> $indicatorMap competency_year_id => level_no => ข้อ
     * @param array<int, int> $scores indicator_id => คะแนน
     * @return array{per_competency: array<int, float|null>, total: float|null, rated: int, expected: int}
     */
    public static function calculate(array $expectedLevels, array $indicatorMap, array $scores): array
    {
        $perCompetency = [];
        $ratedTotal = 0;
        $expectedTotal = 0;

        foreach ($expectedLevels as $competencyYearId => $expectedLevel) {
            $got = 0;
            $full = 0;
            $rated = 0;
            $expected = 0;

            foreach (($indicatorMap[$competencyYearId] ?? []) as $levelNo => $indicators) {
                if ((int) $levelNo > (int) $expectedLevel) {
                    continue; // ระดับเกินที่คาดหวัง ไม่นำมาคิด ตรงกับสูตรในไฟล์ Excel
                }
                foreach ($indicators as $indicator) {
                    $expected++;
                    $value = $scores[(int) $indicator->id] ?? null;
                    if ($value === null) {
                        continue;
                    }
                    $got += (int) $value;
                    $full += 5;
                    $rated++;
                }
            }

            $ratedTotal += $rated;
            $expectedTotal += $expected;
            $perCompetency[$competencyYearId] = $full > 0 ? round($got / $full * 100, 2) : null;
        }

        $scored = array_filter($perCompetency, static fn ($value): bool => $value !== null);
        return [
            'per_competency' => $perCompetency,
            'total' => $scored === [] ? null : round(array_sum($scored) / count($scored), 2),
            'rated' => $ratedTotal,
            'expected' => $expectedTotal,
        ];
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
