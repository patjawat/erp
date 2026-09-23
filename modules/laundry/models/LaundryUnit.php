<?php

namespace app\modules\laundry\models;

use app\modules\hr\models\Organization;
use yii\db\ActiveRecord;

/**
 * ทะเบียนหน่วยงานซักฟอก — หน่วยงานที่แสดงเป็นการ์ดในหน้ารับผ้า/ตรวจรับ/จ่าย
 *
 * @property int $id
 * @property int $tree_id
 * @property string|null $abbr
 * @property int $sort_order
 * @property int $is_active
 * @property string|null $created_at
 * @property int|null $created_by
 */
class LaundryUnit extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%laundry_unit}}';
    }

    public function rules()
    {
        return [
            [['tree_id'], 'required'],
            [['tree_id', 'sort_order', 'is_active', 'created_by'], 'integer'],
            [['abbr'], 'string', 'max' => 20],
            [['tree_id'], 'unique', 'message' => 'หน่วยงานนี้อยู่ในทะเบียนแล้ว'],
            [['created_at'], 'safe'],
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'tree_id']);
    }

    /**
     * ตัวเลือกหน่วยงานซักฟอก เรียงตามลำดับในหน้าตั้งค่า (sort_order) — tree_id => "ชื่อย่อ · ชื่อเต็ม"
     * ใช้กับ dropdown/Select2 ทุกจุดในโมดูล ให้ลำดับตรงกับการ์ดหน้ารับผ้า/ตรวจนับ
     */
    public static function options(bool $activeOnly = true): array
    {
        $query = static::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        if ($activeOnly) {
            $query->andWhere(['is_active' => 1]);
        }
        $units = $query->all();
        if (!$units) {
            return [];
        }
        $names = Organization::find()->select(['name', 'id'])
            ->where(['id' => array_map(static fn($u) => $u->tree_id, $units)])->indexBy('id')->column();
        $options = [];
        foreach ($units as $u) {
            $name = $names[$u->tree_id] ?? ('#' . $u->tree_id);
            $options[$u->tree_id] = $u->abbr ? ($u->abbr . ' · ' . $name) : $name;
        }
        return $options;
    }

    /** ชื่อหน่วยงานเต็ม */
    public function getName(): string
    {
        return $this->organization->name ?? ('#' . $this->tree_id);
    }
}
