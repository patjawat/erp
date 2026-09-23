<?php

namespace app\modules\laundry\models;

use yii\db\ActiveRecord;

/**
 * ทะเบียนหน่วยงานภายนอกที่ส่งผ้ามาให้ (เช่น รพ.เลย ส่งผ้ากลับหลัง Refer) — ใช้ในหน้า นับ–รีด–QC
 *
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property int $is_active
 * @property string|null $created_at
 * @property int|null $created_by
 */
class LaundryExternalSource extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%laundry_external_source}}';
    }

    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
            [['name'], 'unique', 'message' => 'มีหน่วยงานภายนอกชื่อนี้แล้ว'],
            [['sort_order', 'is_active', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return true;
    }

    /** id => name เรียงตามลำดับ */
    public static function options(bool $activeOnly = true): array
    {
        $query = static::find()->select(['name', 'id'])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->indexBy('id');
        if ($activeOnly) {
            $query->andWhere(['is_active' => 1]);
        }
        return $query->column();
    }

    /**
     * รับค่าจาก Select2 (tags): ตัวเลข = id เดิม, ข้อความ = ชื่อใหม่ → หา/สร้างให้
     * @return int|null id หน่วยงานภายนอก (null = ค่าไม่ถูกต้อง)
     */
    public static function resolve($value, ?int $userId): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (ctype_digit($value) && ($m = static::findOne((int) $value))) {
            return (int) $m->id;
        }
        $m = static::findOne(['name' => $value]);
        if ($m) {
            if (!$m->is_active) {
                $m->is_active = 1;
                $m->save(false);
            }
            return (int) $m->id;
        }
        $m = new static([
            'name' => $value, 'is_active' => 1, 'created_by' => $userId,
            'sort_order' => (int) static::find()->max('sort_order') + 1,
        ]);
        return $m->save() ? (int) $m->id : null;
    }
}
