<?php

namespace app\modules\finance\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * เรื่องย่อยในคู่มือการเงิน
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string|null $intro
 * @property string|null $note
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property FinanceManualCategory $category
 * @property FinanceManualItem[] $items
 */
class FinanceManualTopic extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%finance_manual_topic}}';
    }

    public function rules()
    {
        return [
            [['category_id', 'title'], 'required'],
            [['category_id', 'sort_order'], 'integer'],
            [['intro', 'note'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['is_active'], 'boolean'],
            [['category_id'], 'exist', 'targetClass' => FinanceManualCategory::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'ชื่อเรื่อง',
            'intro' => 'คำนำ',
            'note' => 'หมายเหตุ',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(FinanceManualCategory::class, ['id' => 'category_id']);
    }

    public function getItems()
    {
        return $this->hasMany(FinanceManualItem::class, ['topic_id' => 'id'])
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
        return true;
    }
}
