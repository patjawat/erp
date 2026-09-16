<?php

namespace app\modules\swot\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * กระดานวิเคราะห์ SWOT/SOAR หนึ่งเรื่อง
 *
 * @property int $id
 * @property string $title
 * @property string $framework  swot | soar
 * @property string|null $objective
 * @property int|null $owner_id
 * @property int|null $org_unit_id
 * @property int|null $budget_year  ปีงบ พ.ศ.
 * @property string $status  active | archived
 * @property array|null $custom_categories
 * @property array|null $tows_matrix
 * @property array|null $soar_matrix
 * @property array|null $ai_analysis
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property SwotNote[] $notes
 * @property Employees|null $owner
 */
class SwotBoard extends ActiveRecord
{
    public const FRAMEWORK_SWOT = 'swot';
    public const FRAMEWORK_SOAR = 'soar';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    /** ช่องวิเคราะห์ตามแต่ละกรอบคิด */
    public const QUADRANTS_SWOT = ['strengths', 'weaknesses', 'opportunities', 'threats'];
    public const QUADRANTS_SOAR = ['strengths', 'opportunities', 'aspirations', 'results'];

    /** หมวดหมู่ตั้งต้นสำหรับจัดกลุ่มประเด็น (ขั้นตอนที่ 2) */
    public const DEFAULT_CATEGORIES = [
        'ทักษะ & ความสามารถ',
        'ภาวะผู้นำ & การตัดสินใจ',
        'การสื่อสาร & ความสัมพันธ์',
        'การเงิน & ทรัพยากร',
        'กระบวนการทำงาน & ประสิทธิภาพ',
        'เทคโนโลยี & นวัตกรรม',
        'โอกาสภายนอก & เทรนด์',
        'ความเสี่ยง & ปัจจัยคุกคาม',
        'ทั่วไป',
    ];

    public static function tableName(): string
    {
        return '{{%swot_board}}';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => static fn () => date('Y-m-d H:i:s')],
            ['class' => BlameableBehavior::class],
        ];
    }

    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['objective'], 'string'],
            [['owner_id', 'org_unit_id', 'budget_year'], 'integer'],
            [['custom_categories', 'tows_matrix', 'soar_matrix', 'ai_analysis'], 'safe'],
            ['framework', 'in', 'range' => [self::FRAMEWORK_SWOT, self::FRAMEWORK_SOAR]],
            ['framework', 'default', 'value' => self::FRAMEWORK_SWOT],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_ARCHIVED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'ชื่อเรื่องที่วิเคราะห์',
            'framework' => 'กรอบการวิเคราะห์',
            'objective' => 'วัตถุประสงค์',
            'owner_id' => 'ผู้จัดทำ',
            'org_unit_id' => 'หน่วยงาน',
            'budget_year' => 'ปีงบประมาณ',
            'status' => 'สถานะ',
            'updated_at' => 'แก้ไขล่าสุด',
        ];
    }

    public function getNotes()
    {
        return $this->hasMany(SwotNote::class, ['board_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getOwner()
    {
        return $this->hasOne(Employees::class, ['user_id' => 'owner_id']);
    }

    /** ชื่อผู้จัดทำสำหรับแสดงผล */
    public function getOwnerName(): string
    {
        if ($this->owner) {
            return trim((string) $this->owner->fullname) ?: 'ผู้ใช้งาน';
        }
        return 'ผู้ใช้งาน';
    }

    public function isSoar(): bool
    {
        return $this->framework === self::FRAMEWORK_SOAR;
    }

    /** รายชื่อช่องวิเคราะห์ของกระดานนี้ตามกรอบคิด */
    public function quadrants(): array
    {
        return $this->isSoar() ? self::QUADRANTS_SOAR : self::QUADRANTS_SWOT;
    }

    /** จัดกลุ่มโน้ตตามช่อง คืน map[quadrant] = SwotNote[] */
    public function notesByQuadrant(): array
    {
        $map = [];
        foreach ($this->quadrants() as $q) {
            $map[$q] = [];
        }
        foreach ($this->notes as $note) {
            $map[$note->quadrant][] = $note;
        }
        return $map;
    }

    public static function frameworkLabel(string $framework): string
    {
        return $framework === self::FRAMEWORK_SOAR ? 'SOAR' : 'SWOT';
    }

    /** หมวดหมู่ที่ใช้ในกระดานนี้ = custom (ถ้ามี) รวมกับหมวดที่โน้ตใช้จริง มิฉะนั้นใช้ค่าตั้งต้น */
    public function categories(): array
    {
        $base = (is_array($this->custom_categories) && $this->custom_categories)
            ? $this->custom_categories
            : self::DEFAULT_CATEGORIES;

        $used = [];
        foreach ($this->notes as $n) {
            if ($n->category !== null && $n->category !== '') {
                $used[] = $n->category;
            }
        }
        $merged = array_values(array_unique(array_merge($base, $used)));
        return $merged;
    }

    /**
     * รวมค่าน้ำหนักแยกตามช่อง (quadrant => ผลรวม weight)
     * ใช้ทำเรดาร์ "ภาพรวม 4 ด้าน"
     */
    public function weightByQuadrant(): array
    {
        $sum = [];
        foreach ($this->quadrants() as $q) {
            $sum[$q] = 0;
        }
        foreach ($this->notes as $n) {
            if (isset($sum[$n->quadrant])) {
                $sum[$n->quadrant] += (int) $n->weight;
            }
        }
        return $sum;
    }

    /**
     * รวมค่าน้ำหนักแยกตามหมวดหมู่ (category => ผลรวม weight ของทุกช่อง)
     * ใช้ทำเรดาร์ "ตามหมวดหมู่"
     */
    public function weightByCategory(): array
    {
        $sum = [];
        foreach ($this->notes as $n) {
            $cat = ($n->category !== null && $n->category !== '') ? $n->category : 'ทั่วไป';
            $sum[$cat] = ($sum[$cat] ?? 0) + (int) $n->weight;
        }
        arsort($sum);
        return $sum;
    }
}
