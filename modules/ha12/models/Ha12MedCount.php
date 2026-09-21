<?php

namespace app\modules\ha12\models;

/**
 * แถวย่อยความเสี่ยง + จำนวนตามระดับความรุนแรง
 *
 * กฎ (บท 8):
 * - ว่าง = ยังไม่รายงาน ; 0 = ตรวจแล้วไม่พบ
 * - จำนวนครั้ง (total_count) = ผลรวม No Harm + E..I ; คำนวณตอนบันทึก
 * - จำนวนเต็มไม่ติดลบ
 *
 * @property int $id
 * @property int $group_id
 * @property string|null $risk_code
 * @property string $risk_name
 * @property int $is_other
 * @property string|null $team
 * @property int|null $c_no_harm
 * @property int|null $c_e
 * @property int|null $c_f
 * @property int|null $c_g
 * @property int|null $c_h
 * @property int|null $c_i
 * @property int|null $total_count
 * @property string|null $review_result
 * @property string|null $fix
 * @property int $sort
 * @property string $ref
 */
class Ha12MedCount extends Ha12ActiveRecord
{
    /** คอลัมน์จำนวนตามระดับความรุนแรง */
    public const SEVERITY_COLS = ['c_no_harm', 'c_e', 'c_f', 'c_g', 'c_h', 'c_i'];

    public static function tableName(): string
    {
        return '{{%ha12_med_count}}';
    }

    public function rules(): array
    {
        return [
            [['group_id', 'risk_name'], 'required'],
            [['group_id', 'is_other', 'sort'], 'integer'],
            [['risk_code'], 'string', 'max' => 32],
            [['risk_name'], 'string', 'max' => 255],
            [['team'], 'in', 'range' => array_keys(Ha12MedTemplate::TEAMS)],
            [self::SEVERITY_COLS, 'integer', 'min' => 0, 'message' => 'จำนวนต้องเป็นจำนวนเต็มไม่ติดลบ'],
            [['review_result', 'fix'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'risk_name' => 'ความเสี่ยงย่อย',
            'team' => 'ทีมผู้บันทึก',
            'total_count' => 'จำนวนครั้ง',
            'review_result' => 'ผลการทบทวน',
            'fix' => 'การแก้ไข/ป้องกัน',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // จำนวนครั้ง = ผลรวมทุกระดับ ; ถ้าทุกระดับว่าง → total ว่าง (ยังไม่รายงาน)
        $sum = 0;
        $hasValue = false;
        foreach (self::SEVERITY_COLS as $col) {
            $v = $this->$col;
            if ($v === '' ) {
                $this->$col = null;
                $v = null;
            }
            if ($v !== null) {
                $hasValue = true;
                $sum += (int) $v;
            }
        }
        $this->total_count = $hasValue ? $sum : null;
        return true;
    }

    public function getGroup()
    {
        return $this->hasOne(Ha12MedGroup::class, ['id' => 'group_id']);
    }

    public function teamLabel(): string
    {
        return Ha12MedTemplate::teamLabel($this->team);
    }
}
