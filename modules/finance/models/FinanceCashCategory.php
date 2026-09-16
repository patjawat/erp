<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * ผังบัญชีรับ-จ่ายเงินบำรุง 3 ระดับ (self-ref) แยก IN/OUT
 * โครงสร้าง: กลุ่ม (group) → หมวด (category) → หัวข้อบัญชี (account)
 * ใช้ร่วมทั้งฝั่งแผน (finance_cash_plan) และฝั่งบันทึกจริง (finance_cash_txn)
 *
 * @property int $id
 * @property string $txn_type IN|OUT
 * @property int|null $parent_id
 * @property string $level group|category|account
 * @property string|null $code
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 * @property int $is_active
 * @property FinanceCashCategory|null $parent
 * @property FinanceCashCategory[] $children
 */
class FinanceCashCategory extends ActiveRecord
{
    use LoanAuditTrait;

    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';

    public const LEVEL_GROUP = 'group';
    public const LEVEL_CATEGORY = 'category';
    public const LEVEL_ACCOUNT = 'account';

    public static function tableName()
    {
        return '{{%finance_cash_category}}';
    }

    public function rules()
    {
        return [
            [['txn_type', 'level', 'name'], 'required'],
            [['txn_type'], 'in', 'range' => [self::TYPE_IN, self::TYPE_OUT]],
            [['level'], 'in', 'range' => [self::LEVEL_GROUP, self::LEVEL_CATEGORY, self::LEVEL_ACCOUNT]],
            [['parent_id', 'sort_order', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 500],
            [['code'], 'string', 'max' => 32],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'txn_type' => 'ประเภท',
            'parent_id' => 'อยู่ภายใต้',
            'level' => 'ระดับ',
            'code' => 'รหัสบัญชี',
            'name' => 'ชื่อ',
            'description' => 'คำอธิบาย',
            'sort_order' => 'ลำดับ',
            'is_active' => 'ใช้งาน',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** คิดระดับจากความลึกของแม่: ไม่มีแม่=group, แม่เป็น group=category, ที่เหลือ=account */
    public function levelFromParent(): string
    {
        if (!$this->parent_id) {
            return self::LEVEL_GROUP;
        }
        $parent = self::findOne($this->parent_id);
        if ($parent && $parent->level === self::LEVEL_GROUP) {
            return self::LEVEL_CATEGORY;
        }
        return self::LEVEL_ACCOUNT;
    }

    public static function typeLabel(?string $type): string
    {
        return $type === self::TYPE_OUT ? 'รายจ่าย' : 'รายรับ';
    }

    /** ผังทั้งหมดของประเภทหนึ่ง (asArray) เรียงพร้อมทำ dropdown/tree ฝั่ง client */
    public static function treeArray(string $type): array
    {
        return self::find()
            ->where(['txn_type' => $type])
            ->orderBy(['parent_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /** เส้นทางเต็ม เช่น "รายรับจากการดำเนินงาน › ...UC › ..." ใช้แสดงในตารางบันทึก */
    public function pathLabel(): string
    {
        $parts = [$this->name];
        $node = $this->parent;
        $guard = 0;
        while ($node !== null && $guard++ < 5) {
            array_unshift($parts, $node->name);
            $node = $node->parent;
        }
        return implode(' › ', $parts);
    }
}
