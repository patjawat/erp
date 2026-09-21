<?php

namespace app\modules\ha12\models;

/**
 * เกณฑ์ประเมิน 5 ระดับ ต่อกิจกรรม (มี version)
 *
 * @property int $id
 * @property int $activity_id
 * @property int $level        ระดับการพัฒนา 1-5
 * @property string|null $title
 * @property string|null $description
 * @property int $version
 * @property int $is_active
 * @property string $ref
 */
class Ha12Criteria extends Ha12ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ha12_criteria}}';
    }

    public function rules(): array
    {
        return [
            [['activity_id', 'level'], 'required'],
            [['activity_id', 'level', 'version', 'is_active'], 'integer'],
            [['level'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['version'], 'default', 'value' => 1],
            [['is_active'], 'default', 'value' => 1],
            [['activity_id'], 'exist', 'targetClass' => Ha12Activity::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'level' => 'ระดับ',
            'title' => 'ชื่อระดับ',
            'description' => 'นิยามเกณฑ์',
            'version' => 'เวอร์ชัน',
        ];
    }

    public function getActivity()
    {
        return $this->hasOne(Ha12Activity::class, ['id' => 'activity_id']);
    }
}
