<?php

namespace app\modules\flowchart\models;

use app\modules\hr\models\Employees;
use app\modules\settings\models\OrgUnit;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * ผังกระบวนการหนึ่งเรื่อง
 *
 * @property int $id
 * @property string|null $code
 * @property string $title
 * @property string|null $description
 * @property string|null $category
 * @property string $status
 * @property string $diagram_dir
 * @property int|null $owner_id
 * @property int|null $org_unit_id
 * @property int|null $budget_year
 * @property string|null $png_path
 * @property int $revision_no
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property FlowchartStep[] $steps
 * @property Employees|null $owner
 */
class Flowchart extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const DIR_TD = 'TD';
    public const DIR_LR = 'LR';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'ฉบับร่าง',
        self::STATUS_PUBLISHED => 'เผยแพร่แล้ว',
    ];

    public const CATEGORY_LABELS = [
        'sop' => 'ขั้นตอนการปฏิบัติงาน (SOP)',
        'process' => 'กระบวนการทำงาน',
        'service' => 'ขั้นตอนบริการ',
        'other' => 'อื่น ๆ',
    ];

    public static function tableName(): string
    {
        return '{{%flowchart}}';
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
            [['description'], 'string'],
            [['owner_id', 'org_unit_id', 'budget_year', 'revision_no'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            ['status', 'default', 'value' => self::STATUS_DRAFT],
            ['diagram_dir', 'in', 'range' => [self::DIR_TD, self::DIR_LR]],
            ['diagram_dir', 'default', 'value' => self::DIR_TD],
            ['category', 'in', 'range' => array_keys(self::CATEGORY_LABELS)],
            [['title'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 30],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'รหัสผัง',
            'title' => 'ชื่อกระบวนการ',
            'description' => 'คำอธิบาย/วัตถุประสงค์',
            'category' => 'ประเภท',
            'status' => 'สถานะ',
            'diagram_dir' => 'ทิศทางผัง',
            'org_unit_id' => 'หน่วยงาน',
            'budget_year' => 'ปีงบประมาณ',
            'updated_at' => 'แก้ไขล่าสุด',
        ];
    }

    public function getSteps()
    {
        return $this->hasMany(FlowchartStep::class, ['flowchart_id' => 'id'])
            ->orderBy(['seq' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getOwner()
    {
        return $this->hasOne(Employees::class, ['user_id' => 'owner_id']);
    }

    /** หน่วยงานเจ้าของ (ทะเบียน org_unit — กลุ่มงาน/หน่วยงาน/ทีมประสาน) */
    public function getOrgUnit()
    {
        return $this->hasOne(OrgUnit::class, ['id' => 'org_unit_id']);
    }

    /** ชื่อหน่วยงานเจ้าของ (กันว่าง) */
    public function unitName(): string
    {
        return $this->orgUnit ? trim((string) $this->orgUnit->name) : '';
    }

    /** อักษรย่อหน่วยงานเจ้าของ ใช้เป็นรหัสนำ (เช่น EMR) มิฉะนั้น FC */
    public function codePrefix(): string
    {
        $c = $this->orgUnit ? strtoupper(trim((string) $this->orgUnit->code)) : '';
        return $c !== '' ? $c : 'FC';
    }

    public function getOwnerName(): string
    {
        if ($this->owner) {
            return trim((string) $this->owner->fullname) ?: 'ผู้ใช้งาน';
        }
        return 'ผู้ใช้งาน';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function categoryLabel(): string
    {
        return $this->category ? (self::CATEGORY_LABELS[$this->category] ?? $this->category) : '';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** จำนวนขั้นตอน (นับเร็วโดยไม่โหลด relation) */
    public function stepCount(): int
    {
        return (int) FlowchartStep::find()->where(['flowchart_id' => $this->id])->count();
    }

    /**
     * ออกรหัสผังถัดไปในรูป {อักษรย่อ}-{ปีงบ}-{running 4 หลัก}
     * เช่น EMR-2569-0001 (running เดินแยกตามอักษรย่อ+ปี) — ไม่มีอักษรย่อใช้ FC
     */
    public static function nextCode(string $prefix, int $budgetYear): string
    {
        $prefix = strtoupper(trim($prefix)) ?: 'FC';
        $base = $prefix . '-' . $budgetYear . '-';
        $last = static::find()
            ->where(['like', 'code', $base . '%', false])
            ->orderBy(['code' => SORT_DESC])
            ->select('code')
            ->scalar();

        $running = 1;
        if ($last && preg_match('/-(\d+)$/', (string) $last, $m)) {
            $running = (int) $m[1] + 1;
        }
        return $base . str_pad((string) $running, 4, '0', STR_PAD_LEFT);
    }
}
