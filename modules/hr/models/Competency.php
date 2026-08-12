<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ทะเบียนสมรรถนะแม่ — ตัวตนที่คงที่ข้ามปีงบประมาณ
 * ใช้เชื่อมว่า "บริการด้วยใจ ปี 2569" กับ "ปี 2570" คือสมรรถนะตัวเดียวกัน
 * ส่วนเนื้อหาที่ใช้ประเมินจริงอยู่ใน CompetencyYear
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type core / functional
 * @property int $is_active
 * @property int $sort_order
 * @property CompetencyYear[] $years
 */
class Competency extends ActiveRecord
{
    public const TYPE_CORE = 'core';
    public const TYPE_FUNCTIONAL = 'functional';

    public static function tableName()
    {
        return '{{%hr_competency}}';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['is_active', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 40],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['type'], 'in', 'range' => [self::TYPE_CORE, self::TYPE_FUNCTIONAL]],
            [['type'], 'default', 'value' => self::TYPE_CORE],
            [['is_active'], 'default', 'value' => 1],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสสมรรถนะ',
            'name' => 'ชื่อสมรรถนะ',
            'type' => 'ประเภท',
            'is_active' => 'ใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getYears()
    {
        return $this->hasMany(CompetencyYear::class, ['competency_id' => 'id']);
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_CORE => 'สมรรถนะหลัก (Core)',
            self::TYPE_FUNCTIONAL => 'สมรรถนะตามสายงาน (Functional)',
        ];
    }

    /** สร้างรหัสจากชื่อเมื่อ HR ไม่ได้ระบุเอง เช่น "การสื่อสาร" → core_1723... */
    public static function makeCode(string $type): string
    {
        return $type . '_' . substr((string) microtime(true), -8);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
