<?php

namespace app\modules\qms\models;

/**
 * หลักฐานของ checklist item หนึ่ง — เลือกที่มาได้ (ดึงจากระบบ หรือแนบไฟล์เอง)
 *
 * @property int $id
 * @property int $cycle_item_id
 * @property string $source_type
 * @property string|null $source_module
 * @property string|null $source_id
 * @property string|null $file_path
 * @property string|null $file_name
 * @property string|null $url
 * @property string|null $title
 * @property string|null $note
 */
class Evidence extends QmsActiveRecord
{
    public const SOURCE_DMS = 'dms';        // ดึงจากสารบรรณ
    public const SOURCE_MEDSOP = 'medsop';  // ดึงจาก SOP/WI
    public const SOURCE_FILE = 'file';      // แนบไฟล์เอง
    public const SOURCE_LINK = 'link';      // ลิงก์ภายนอก

    public static function tableName(): string
    {
        return '{{%qms_evidence}}';
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_DMS => 'สารบรรณ (DMS)',
            self::SOURCE_MEDSOP => 'SOP/WI (medsop)',
            self::SOURCE_FILE => 'แนบไฟล์',
            self::SOURCE_LINK => 'ลิงก์',
        ];
    }

    public function rules(): array
    {
        return [
            [['cycle_item_id', 'source_type'], 'required'],
            [['cycle_item_id'], 'integer'],
            [['source_type'], 'in', 'range' => array_keys(self::sourceLabels())],
            [['source_module', 'source_id'], 'string', 'max' => 64],
            [['file_path', 'url'], 'string', 'max' => 500],
            [['file_name', 'title', 'note'], 'string', 'max' => 255],
            [['cycle_item_id'], 'exist', 'targetClass' => CycleItem::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'source_type' => 'ที่มา',
            'title' => 'ป้ายกำกับ',
            'file_name' => 'ไฟล์',
            'url' => 'ลิงก์',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getCycleItem()
    {
        return $this->hasOne(CycleItem::class, ['id' => 'cycle_item_id']);
    }

    public function sourceLabel(): string
    {
        return self::sourceLabels()[$this->source_type] ?? $this->source_type;
    }
}
