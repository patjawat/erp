<?php

namespace app\modules\jd\models;

use Yii;
use yii\db\ActiveRecord;

class JdStructureDefault extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_structure_default}}';
    }

    public function rules()
    {
        return [
            [['section_code', 'title', 'block_type'], 'required'],
            [['sort_order', 'is_enabled', 'is_locked', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['section_code'], 'string', 'max' => 40],
            [['title'], 'string', 'max' => 255],
            [['block_type'], 'string', 'max' => 30],
            [['help_text'], 'string', 'max' => 500],
            [['section_code'], 'unique'],
            [['block_type'], 'in', 'range' => array_keys(self::typeOptions())],
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'fields' => 'หัวข้อและรายละเอียด',
            'prose' => 'ข้อความหลายบรรทัด',
            'matrix' => 'ด้านและขอบเขตงาน',
            'groups' => 'หมวดย่อยและรายละเอียด',
            'kpi' => 'ตัวชี้วัดและเป้าหมาย',
            'competency' => 'สมรรถนะและระดับ',
            'boundary' => 'ขอบเขตความรับผิดชอบ',
            'named_items' => 'รายการหัวข้อและคำอธิบาย',
            'approval' => 'ขั้นตอนการอนุมัติ',
        ];
    }

    public static function ordered(): array
    {
        if (Yii::$app->db->getTableSchema(self::tableName(), true) === null) {
            return [];
        }

        return self::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }
}
