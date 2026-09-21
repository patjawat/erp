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

    /** ชื่อหน่วยงานเต็ม */
    public function getName(): string
    {
        return $this->organization->name ?? ('#' . $this->tree_id);
    }
}
