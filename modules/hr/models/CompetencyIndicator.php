<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

/**
 * ข้อพฤติกรรมบ่งชี้ (เช่น 1.1, 1.2) — หน่วยย่อยที่สุดที่ผู้ประเมินให้คะแนน
 * scale_id ว่าง = ใช้ชุดมาตรวัดมาตรฐาน 5 ระดับ
 * ข้อที่วัดผลสุขภาพ (BMI/รอบเอว/เดินวิ่ง) จะระบุชุดมาตรวัดเฉพาะของตัวเอง
 *
 * @property int $id
 * @property int $level_id
 * @property string|null $indicator_no
 * @property string $text
 * @property int|null $scale_id
 * @property int $sort_order
 * @property CompetencyLevel $level
 * @property CompetencyScale|null $scale
 */
class CompetencyIndicator extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hr_competency_indicator}}';
    }

    public function rules()
    {
        return [
            [['level_id', 'text'], 'required'],
            [['level_id', 'scale_id', 'sort_order'], 'integer'],
            [['text'], 'string'],
            [['indicator_no'], 'string', 'max' => 20],
            [['sort_order'], 'default', 'value' => 0],
            [['level_id'], 'exist', 'targetClass' => CompetencyLevel::class, 'targetAttribute' => ['level_id' => 'id']],
            [['scale_id'], 'exist', 'targetClass' => CompetencyScale::class, 'targetAttribute' => ['scale_id' => 'id'], 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'indicator_no' => 'ข้อที่',
            'text' => 'พฤติกรรมที่แสดงออก',
            'scale_id' => 'ชุดมาตรวัด',
        ];
    }

    public function getLevel()
    {
        return $this->hasOne(CompetencyLevel::class, ['id' => 'level_id']);
    }

    public function getScale()
    {
        return $this->hasOne(CompetencyScale::class, ['id' => 'scale_id']);
    }

    /** ชุดมาตรวัดที่ใช้จริง — ถ้าไม่ระบุจะตกไปที่ชุดมาตรฐาน */
    public function resolveScale(): ?CompetencyScale
    {
        return $this->scale ?: CompetencyScale::defaultScale();
    }
}
