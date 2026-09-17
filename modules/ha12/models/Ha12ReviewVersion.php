<?php

namespace app\modules\ha12\models;

/**
 * ประวัติรุ่นของรายการทบทวน (append ทุกครั้งที่บันทึก/ลบ/กู้คืน)
 *
 * @property int $id
 * @property int $review_id
 * @property int $revision
 * @property string $action  create|update|delete|restore
 * @property string|null $review_date
 * @property string|null $title
 * @property string|null $reviewer_name
 * @property string|null $payload_json
 * @property int $schema_version
 * @property int $deleted
 * @property string $ref
 * @property int|null $created_by
 * @property string|null $created_at
 */
class Ha12ReviewVersion extends Ha12ActiveRecord
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_RESTORE = 'restore';

    public static function actionLabels(): array
    {
        return [
            self::ACTION_CREATE => 'สร้าง',
            self::ACTION_UPDATE => 'แก้ไข',
            self::ACTION_DELETE => 'ลบ',
            self::ACTION_RESTORE => 'กู้คืน',
        ];
    }

    public static function tableName(): string
    {
        return '{{%ha12_review_version}}';
    }

    public function rules(): array
    {
        return [
            [['review_id', 'revision'], 'required'],
            [['review_id', 'revision', 'schema_version', 'deleted'], 'integer'],
            [['action'], 'in', 'range' => array_keys(self::actionLabels())],
            [['review_date'], 'safe'],
            [['reviewer_name', 'title'], 'string', 'max' => 500],
            [['payload_json'], 'string'],
        ];
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }

    /** payload เป็น array */
    public function fields(): array
    {
        $d = $this->payload_json ? json_decode($this->payload_json, true) : [];
        return is_array($d) ? $d : [];
    }
}
