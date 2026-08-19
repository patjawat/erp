<?php

namespace app\modules\hr\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * จับคู่สายงานมาตรฐาน (ตามเกณฑ์ สป.สธ.) กับตำแหน่งจริงของโรงพยาบาลนี้
 *
 * จำเป็นเพราะเอกสารเรียก "นักวิชาการสาธารณสุข" แต่แต่ละโรงพยาบาลตั้งชื่อตำแหน่ง
 * ในทะเบียนของตัวเองไม่เหมือนกัน — ตารางนี้คือสิ่งที่ทำให้ระบบใช้ได้ทุกที่
 *
 * @property int $id
 * @property int $line_id
 * @property int $employee_position_id
 * @property string $matched_by
 */
class WorkforcePositionMap extends \yii\db\ActiveRecord
{
    public const MATCHED_AUTO = 'auto';
    public const MATCHED_MANUAL = 'manual';

    /** คำย่อที่ใช้ในทะเบียนตำแหน่ง แปลงให้เป็นคำเต็มก่อนเทียบชื่อ */
    private const ABBREVIATIONS = [
        'จพ.' => 'เจ้าพนักงาน',
        'นวก.' => 'นักวิชาการ',
        'พนง.' => 'พนักงาน',
        'ลจ.' => 'ลูกจ้าง',
        'ขรก.' => 'ข้าราชการ',
    ];

    public static function tableName()
    {
        return '{{%workforce_position_map}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'updatedAtAttribute' => false,
            ],
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['employee_position_id'], 'required'],
            [['line_id', 'employee_position_id'], 'integer'],
            [['line_id'], 'default', 'value' => null],
            [['matched_by'], 'in', 'range' => [self::MATCHED_AUTO, self::MATCHED_MANUAL]],
            [['employee_position_id'], 'unique', 'message' => 'ตำแหน่งนี้จับคู่กับสายงานอื่นอยู่แล้ว'],
        ];
    }

    /** ยืนยันแล้วว่าตำแหน่งนี้ไม่มีสายงานตรงในเกณฑ์ */
    public function isMarkedNoStandard(): bool
    {
        return $this->line_id === null;
    }

    public function getLine(): ActiveQuery
    {
        return $this->hasOne(WorkforceStandardLine::class, ['id' => 'line_id']);
    }

    public function getPosition(): ActiveQuery
    {
        return $this->hasOne(EmployeePosition::class, ['id' => 'employee_position_id']);
    }

    /**
     * map: employee_position_id => line_id (null = ยืนยันแล้วว่าไม่มีในเกณฑ์)
     *
     * @return array<int,int|null>
     */
    public static function positionToLine(): array
    {
        $map = [];
        foreach (static::find()->select(['employee_position_id', 'line_id'])->asArray()->all() as $row) {
            $map[(int) $row['employee_position_id']] = $row['line_id'] === null ? null : (int) $row['line_id'];
        }

        return $map;
    }

    /**
     * ขยายคำย่อและตัดช่องว่าง เพื่อเทียบชื่อสองฝั่งอย่างยุติธรรม
     *
     * ไม่ตัดวงเล็บทิ้ง เพราะข้อความในวงเล็บคือสิ่งที่แยกสายงานออกจากกัน
     * "นักวิชาการสาธารณสุข (เวชสถิติ)" กับ "(ทันตสาธารณสุข)" เป็นคนละสายงาน
     * และเป็นคนละสายกับ "นักวิชาการสาธารณสุข" เฉย ๆ
     */
    public static function normalize(string $title): string
    {
        $title = trim($title);
        $title = strtr($title, self::ABBREVIATIONS);
        $title = preg_replace('/\s+/u', '', $title) ?? $title;

        return $title;
    }

    /**
     * จับคู่อัตโนมัติตามชื่อ — คู่ที่ชื่อตรงกันเป๊ะหลัง normalize เท่านั้น
     *
     * ตั้งใจไม่ทำ fuzzy match เพราะจับคู่ผิดจะทำให้กรอบผิดทั้งสายงาน
     * ที่เหลือให้คนเลือกเองในหน้าจับคู่
     *
     * @param bool $dryRun true = ดูผลอย่างเดียว ไม่บันทึก
     * @return array{matched:array<int,int>,skipped:int}
     */
    public static function autoMatch(bool $dryRun = false): array
    {
        $taken = self::positionToLine();

        $lineByAlias = [];
        foreach (WorkforceStandardLine::currentEdition() as $line) {
            foreach ($line->titleAliases() as $alias) {
                $key = self::normalize($alias);
                if ($key === '') {
                    continue;
                }
                // ชื่อซ้ำข้ามสายงาน = คลุมเครือ ไม่จับคู่ให้อัตโนมัติ
                $lineByAlias[$key] = isset($lineByAlias[$key]) ? false : (int) $line->id;
            }
        }

        $positions = EmployeePosition::find()->where(['active' => 1])->all();

        $matched = [];
        $skipped = 0;
        foreach ($positions as $position) {
            // array_key_exists ไม่ใช่ isset — แถวที่ยืนยันว่า "ไม่มีในเกณฑ์" เก็บ line_id เป็น null
            if (array_key_exists((int) $position->id, $taken)) {
                continue;
            }

            $key = self::normalize((string) $position->title);
            $lineId = $lineByAlias[$key] ?? null;

            if ($lineId === null || $lineId === false) {
                $skipped++;
                continue;
            }

            $matched[(int) $position->id] = $lineId;

            if (!$dryRun) {
                $model = new static([
                    'line_id' => $lineId,
                    'employee_position_id' => (int) $position->id,
                    'matched_by' => self::MATCHED_AUTO,
                ]);
                $model->save(false);
            }
        }

        return ['matched' => $matched, 'skipped' => $skipped];
    }
}
