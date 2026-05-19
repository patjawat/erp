<?php

namespace app\modules\hr\models;

use yii\helpers\ArrayHelper;

/**
 * Table `employee_position_group`:
 * กลุ่มตำแหน่งพนักงาน (ใหม่) แบบอิสระจากประเภทพนักงาน
 */
class EmployeePositionGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'employee_position_group';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'filter', 'filter' => 'trim'],
            [['sort', 'active'], 'integer'],
            [['title'], 'unique', 'message' => 'กลุ่มตำแหน่งนี้มีอยู่แล้ว'],
            [['legacy_code'], 'string', 'max' => 30],
            [['data_json'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'legacy_code' => 'รหัสเดิมจาก categorise',
            'title' => 'กลุ่มตำแหน่งพนักงาน (ใหม่)',
            'sort' => 'ลำดับแสดงผล',
            'active' => 'สถานะใช้งาน',
            'data_json' => 'ข้อมูลเพิ่มเติม',
        ];
    }

    public function getEmployeePositions()
    {
        return $this->hasMany(EmployeePosition::className(), ['employee_position_group_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public static function listItems($employeeTypeId = null): array
    {
        if (\Yii::$app->db->getTableSchema(self::tableName(), true) === null) {
            return [];
        }

        $query = self::find()->where(['active' => 1]);

        $models = $query->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();
        $uniqueModels = [];
        foreach ($models as $model) {
            $key = self::normalizeTitleKey($model->title ?? '');
            if ($key === '' || isset($uniqueModels[$key])) {
                continue;
            }

            $uniqueModels[$key] = $model;
        }

        return ArrayHelper::map(array_values($uniqueModels), 'id', 'title');
    }

    private static function normalizeTitleKey($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value);
        if ($value === null) {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}
