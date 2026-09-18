<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * บรรทัดเอกสาร/ข้อกำหนดในแต่ละเรื่อง
 *
 * @property int $id
 * @property int $topic_id
 * @property string $content
 * @property string $kind  doc = เอกสารที่ต้องเตรียม, warning = ข้อห้าม/ข้อควรระวัง, note = หมายเหตุ
 * @property int $sort_order
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property FinanceManualTopic $topic
 */
class FinanceManualItem extends ActiveRecord
{
    public const KIND_DOC = 'doc';
    public const KIND_WARNING = 'warning';
    public const KIND_NOTE = 'note';

    public static function kinds(): array
    {
        return [
            self::KIND_DOC => 'เอกสารที่ต้องเตรียม',
            self::KIND_WARNING => 'ข้อห้าม/ข้อควรระวัง',
            self::KIND_NOTE => 'หมายเหตุ',
        ];
    }

    public static function tableName()
    {
        return '{{%finance_manual_item}}';
    }

    public function rules()
    {
        return [
            [['topic_id', 'content'], 'required'],
            [['topic_id', 'sort_order'], 'integer'],
            [['content'], 'string'],
            [['kind'], 'in', 'range' => array_keys(self::kinds())],
            [['kind'], 'default', 'value' => self::KIND_DOC],
            [['topic_id'], 'exist', 'targetClass' => FinanceManualTopic::class, 'targetAttribute' => 'id'],
        ];
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
        return true;
    }
}
