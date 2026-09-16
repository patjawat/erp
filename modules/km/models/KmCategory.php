<?php

namespace app\modules\km\models;

/**
 * หมวดหมู่กิจกรรม KM (เป็นชั้นได้)
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort
 * @property int $is_active
 * @property string $ref
 */
class KmCategory extends KmActiveRecord
{
    public static function tableName(): string
    {
        return '{{%km_category}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['icon'], 'string', 'max' => 64],
            [['color'], 'string', 'max' => 32],
            [['parent_id', 'sort', 'is_active'], 'integer'],
            [['is_active'], 'default', 'value' => 1],
            [['sort'], 'default', 'value' => 0],
            [['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'ชื่อหมวด',
            'parent_id' => 'หมวดแม่',
            'icon' => 'ไอคอน',
            'color' => 'สี',
            'sort' => 'ลำดับ',
            'is_active' => 'เปิดใช้งาน',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC]);
    }

    public function isTop(): bool
    {
        return empty($this->parent_id);
    }

    /**
     * แผนที่สำหรับ dropDownList แบบมีลำดับชั้น 2 ชั้น
     * หมวดหลักแสดงชื่อปกติ, หมวดย่อยขึ้นต้นด้วย "— " เพื่อให้เห็นว่าอยู่ใต้หมวดหลัก
     *
     * @return array<int,string>  [id => label]
     */
    public static function dropdownMap(bool $activeOnly = true): array
    {
        $query = self::find();
        if ($activeOnly) {
            $query->where(['is_active' => 1]);
        }
        $all = $query->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])->all();

        $byParent = [];
        foreach ($all as $c) {
            $byParent[(int) $c->parent_id][] = $c; // parent_id null -> key 0 (หมวดหลัก)
        }

        $map = [];
        foreach ($byParent[0] ?? [] as $top) {
            $map[(int) $top->id] = $top->name;
            foreach ($byParent[(int) $top->id] ?? [] as $child) {
                $map[(int) $child->id] = '— ' . $child->name;
            }
        }
        return $map;
    }
}
