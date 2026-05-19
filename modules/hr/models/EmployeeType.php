<?php

namespace app\modules\hr\models;

use yii\helpers\ArrayHelper;

/**
 * Table `employee_type`:
 * ประเภทพนักงาน (ใหม่)
 */
class EmployeeType extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'employee_type';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['sort', 'active'], 'integer'],
            [['data_json'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'ประเภทพนักงาน (ใหม่)',
            'sort' => 'ลำดับแสดงผล',
            'active' => 'สถานะใช้งาน',
            'data_json' => 'ข้อมูลเพิ่มเติม/รหัสเดิม',
        ];
    }

    public function getEmployeePositions()
    {
        return $this->hasMany(EmployeePosition::className(), ['employee_type_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function legacyCodes(): array
    {
        $data = $this->data_json;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        $codes = $data['legacy_codes'] ?? [];
        return is_array($codes) ? $codes : [];
    }

    public static function listItems(): array
    {
        if (\Yii::$app->db->getTableSchema(self::tableName(), true) === null) {
            return [];
        }

        $models = self::find()
            ->where(['active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($models, 'id', 'title');
    }
}
