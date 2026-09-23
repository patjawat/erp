<?php

namespace app\modules\laundry\models;

use yii\db\ActiveRecord;

/**
 * หมวดประเภทผ้า (ผ้าของโรงพยาบาล / ผ้าจากหน่วยงานภายนอก ฯลฯ) — ตั้งค่าที่หน้าประเภทผ้า
 *
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property int $is_active
 * @property string|null $created_at
 * @property int|null $created_by
 */
class LaundryItemCategory extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%laundry_item_category}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
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
        $query = static::find()->select(['name', 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id');
        if ($activeOnly) {
            $query->andWhere(['is_active' => 1]);
        }
        return $query->column();
    }

    /**
     * จัดกลุ่มรายการผ้าตามหมวด (คงลำดับหมวด) — [['name' => หมวด, 'items' => [...]], ...]
     * รายการที่ไม่มีหมวด (category_id ว่าง/หมวดถูกปิด) ไปอยู่กลุ่ม "ไม่ระบุหมวด" ท้ายสุด
     * @param array $items แถวที่มีคีย์ category_id
     */
    public static function group(array $items): array
    {
        $groups = [];
        foreach (static::options(false) as $id => $name) {
            $groups[$id] = ['name' => $name, 'items' => []];
        }
        $groups[0] = ['name' => 'ไม่ระบุหมวด', 'items' => []];
        foreach ($items as $it) {
            $cid = (int) ($it['category_id'] ?? 0);
            $groups[isset($groups[$cid]) ? $cid : 0]['items'][] = $it;
        }
        return array_values(array_filter($groups, static fn($g) => $g['items']));
    }
}
