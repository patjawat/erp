<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * หมวดใหญ่ของคู่มือการเงิน
 *
 * @property int $id
 * @property string $code
 * @property string $title
 * @property string|null $icon
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property FinanceManualTopic[] $topics
 */
class FinanceManualCategory extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%finance_manual_category}}';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['description'], 'string', 'max' => 500],
            [['title'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 40],
            [['icon'], 'string', 'max' => 60],
            [['sort_order'], 'integer'],
            [['is_active'], 'boolean'],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'ชื่อหมวด',
            'icon' => 'ไอคอน',
            'description' => 'คำอธิบาย',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getTopics()
    {
        return $this->hasMany(FinanceManualTopic::class, ['category_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        if ($insert && $this->created_at === null) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        if (!Yii::$app->user->isGuest) {
            $this->updated_by = Yii::$app->user->id;
        }
        if ($insert && ($this->code === null || $this->code === '')) {
            $this->code = 'cat_' . substr(md5(uniqid('', true)), 0, 10);
        }
        return true;
    }
}
