<?php

namespace app\modules\hr\models;

use yii\db\ActiveQuery;

/**
 * สายงานนี้มีกรอบได้ไหม ในโรงพยาบาลขนาดไหน
 *
 * eligible: 1 = มีกรอบได้, 0 = ไม่มีกรอบ, NULL = ยังไม่ได้ยืนยันจากเอกสาร
 * NULL ไม่ใช่ 0 — ต้องแสดงให้ผู้ใช้เห็นว่ายังไม่ยืนยัน ไม่ใช่ตัดกรอบทิ้งเงียบ ๆ
 *
 * @property int $id
 * @property int $line_id
 * @property string $level_code
 * @property string|null $size_band
 * @property int|null $eligible
 * @property string|null $min_qty
 * @property string|null $max_qty
 * @property string|null $note
 */
class WorkforceStandardRule extends \yii\db\ActiveRecord
{
    public const ELIGIBLE_YES = 1;
    public const ELIGIBLE_NO = 0;

    public static function tableName()
    {
        return '{{%workforce_standard_rule}}';
    }

    public function rules()
    {
        return [
            [['line_id', 'level_code'], 'required'],
            [['line_id', 'eligible'], 'integer'],
            [['min_qty', 'max_qty'], 'number', 'min' => 0],
            [['note'], 'string'],
            [['level_code'], 'string', 'max' => 10],
            [['size_band'], 'string', 'max' => 30],
        ];
    }

    public function getLine(): ActiveQuery
    {
        return $this->hasOne(WorkforceStandardLine::class, ['id' => 'line_id']);
    }

    public function isUnverified(): bool
    {
        return $this->eligible === null;
    }

    /**
     * กฎของระดับโรงพยาบาลหนึ่ง จัดคีย์ตาม line_id เพื่อ lookup เร็ว
     *
     * ยังไม่รองรับช่วงจำนวนเตียงย่อย — เกณฑ์ส่วนนั้นยังไม่ได้ seed
     * เมื่อ seed แล้วให้เลือกแถวที่ size_band ตรงก่อน ค่อย fallback มาที่ null
     *
     * @return array<int,static>
     */
    public static function mapForLevel(string $levelCode, ?string $sizeBand = null): array
    {
        $rows = static::find()
            ->where(['level_code' => $levelCode])
            ->andWhere(['or', ['size_band' => null], ['size_band' => $sizeBand]])
            ->orderBy(['size_band' => SORT_ASC])
            ->all();

        $map = [];
        foreach ($rows as $row) {
            // แถวที่ระบุ size_band ตรง ให้ทับแถว null
            if (isset($map[$row->line_id]) && $row->size_band === null) {
                continue;
            }
            $map[$row->line_id] = $row;
        }

        return $map;
    }
}
