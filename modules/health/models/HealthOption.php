<?php

namespace app\modules\health\models;

use yii\helpers\ArrayHelper;

/**
 * ใช้ตาราง categorise ที่มีอยู่แล้วในระบบ
 * แยกประเภทด้วย name, code คือรหัส, title คือชื่อแสดงผล
 *
 * @property int    $id
 * @property string $name   ชนิดข้อมูล / ประเภท (เช่น family_disease, chronic_disease)
 * @property string $code   รหัส (key ที่เก็บใน data_json)
 * @property string $title  ชื่อที่แสดงผล
 * @property int    $active 1=ใช้งาน 0=ปิด
 */
class HealthOption extends \yii\db\ActiveRecord
{
    const CATEGORY_FAMILY_DISEASE  = 'family_disease';
    const CATEGORY_CHRONIC_DISEASE = 'chronic_disease';

    public static function tableName()
    {
        return 'categorise';
    }

    public function rules()
    {
        return [
            [['name', 'code', 'title'], 'required'],
            [['name', 'code', 'title'], 'string', 'max' => 255],
            [['active'], 'integer'],
            [['active'], 'default', 'value' => 1],
            [['code'], 'unique', 'targetAttribute' => ['name', 'code'], 'message' => 'รหัสนี้มีอยู่แล้วในประเภทนี้'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'     => 'ID',
            'name'   => 'ประเภท',
            'code'   => 'รหัส',
            'title'  => 'ชื่อ',
            'active' => 'สถานะ',
        ];
    }

    /**
     * คืน [code => title] สำหรับใช้ใน checkboxList / dropDownList
     * กรอง active=1 เรียงตาม id
     */
    public static function getList(string $category): array
    {
        try {
            $rows = self::find()
                ->where(['name' => $category, 'active' => 1])
                ->orderBy(['id' => SORT_ASC])
                ->all();
            return ArrayHelper::map($rows, 'code', 'title');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Label ชื่อหมวดหมู่สำหรับแสดงผล
     */
    public static function categoryLabel(string $category): string
    {
        $labels = [
            self::CATEGORY_FAMILY_DISEASE  => 'โรคในครอบครัว',
            self::CATEGORY_CHRONIC_DISEASE => 'โรคประจำตัว',
        ];
        return $labels[$category] ?? $category;
    }
}
