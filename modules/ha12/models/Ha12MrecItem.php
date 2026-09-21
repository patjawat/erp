<?php

namespace app\modules\ha12\models;

/**
 * หัวข้อความครบถ้วนของเวชระเบียน + จำนวนที่ครบ → ร้อยละ
 *
 * @property int $id
 * @property int $audit_id
 * @property int $item_no
 * @property string $item_name
 * @property int $is_other
 * @property int|null $complete_count
 * @property string|null $note
 * @property int $sort
 * @property string $ref
 */
class Ha12MrecItem extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_mrec_item}}';
    }

    public function rules(): array
    {
        return [
            [['audit_id', 'item_no', 'item_name'], 'required'],
            [['audit_id', 'item_no', 'is_other', 'sort'], 'integer'],
            [['complete_count'], 'integer', 'min' => 0],
            [['item_name'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'item_name' => 'หัวข้อ',
            'complete_count' => 'จำนวนที่ครบ',
        ];
    }

    public function getAudit()
    {
        return $this->hasOne(Ha12MrecAudit::class, ['id' => 'audit_id']);
    }

    /** ร้อยละของหัวข้อนี้ = ครบ ÷ จำนวนตรวจ × 100 */
    public function percent(): ?float
    {
        $total = $this->audit ? (int) $this->audit->total_charts : 0;
        if (!$total || $this->complete_count === null) {
            return null;
        }
        return (int) $this->complete_count / $total * 100;
    }
}
