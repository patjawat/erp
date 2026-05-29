<?php

namespace app\modules\hr\models;

/**
 * Table `employee_position`:
 * ตำแหน่งพนักงาน (ใหม่)
 */
class EmployeePosition extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'employee_position';
    }

    public function rules()
    {
        return [
            [['employee_position_group_id', 'title'], 'required'],
            [['title'], 'filter', 'filter' => [self::class, 'normalizeTitleValue']],
            [['employee_type_id', 'employee_position_group_id', 'sort', 'active'], 'integer'],
            [['title'], 'unique', 'message' => 'ตำแหน่งนี้มีอยู่แล้ว'],
            [['legacy_code'], 'string', 'max' => 50],
            [['data_json'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_type_id' => 'ประเภทพนักงาน (ใหม่)',
            'employee_position_group_id' => 'กลุ่มตำแหน่งพนักงาน (ใหม่)',
            'legacy_code' => 'รหัสเดิมจาก categorise',
            'title' => 'ตำแหน่งพนักงาน (ใหม่)',
            'sort' => 'ลำดับแสดงผล',
            'active' => 'สถานะใช้งาน',
            'data_json' => 'ข้อมูลเพิ่มเติม',
        ];
    }

    public function getEmployeeType()
    {
        return $this->hasOne(EmployeeType::className(), ['id' => 'employee_type_id']);
    }

    public function getEmployeePositionGroup()
    {
        return $this->hasOne(EmployeePositionGroup::className(), ['id' => 'employee_position_group_id']);
    }

    public static function normalizeTitleValue($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $value);

        return $normalized === null ? $value : $normalized;
    }

    private static function normalizeTitleKey($value): string
    {
        $value = self::normalizeTitleValue($value);
        if ($value === '') {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    public static function listItems($employeeTypeId = null, $groupId = null): array
    {
        if (\Yii::$app->db->getTableSchema(self::tableName(), true) === null) {
            return [];
        }

        $query = self::find()
            ->where(['active' => 1]);

        if ($employeeTypeId !== null && $employeeTypeId !== '') {
            $query->andWhere(['employee_type_id' => $employeeTypeId]);
        }
        if ($groupId !== null && $groupId !== '') {
            $query->andWhere(['employee_position_group_id' => $groupId]);
        }

        $models = $query->orderBy([
            'employee_position_group_id' => SORT_ASC,
            'sort' => SORT_ASC,
            'id' => SORT_ASC,
        ])->all();

        $items = [];
        $uniqueModels = [];
        foreach ($models as $model) {
            $key = self::normalizeTitleKey($model->title ?? '');
            if ($key === '' || isset($uniqueModels[$key])) {
                continue;
            }

            $uniqueModels[$key] = $model;
        }

        foreach ($uniqueModels as $model) {
            $items[$model->id] = trim((string) ($model->title ?? ''));
        }

        return $items;
    }
}
