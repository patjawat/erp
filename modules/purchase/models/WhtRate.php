<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * อัตราภาษีหัก ณ ที่จ่าย — ตารางตั้งค่า
 *
 * แยกออกมาเป็นทะเบียนแทนการเขียนอัตราไว้ในโค้ด เพราะอัตราและเกณฑ์ยอดขั้นต่ำ
 * เปลี่ยนตามกฎหมาย และงานการเงินต้องแก้เองได้โดยไม่ต้องรอแก้โปรแกรม
 *
 * @property int $id
 * @property string $code ตรงกับ Contract::contract_type
 * @property string $party_type juristic|personal
 * @property string $title
 * @property float $rate อัตรา (%)
 * @property float $threshold ยอดจ่ายขั้นต่ำที่เริ่มหัก
 * @property string|null $law_ref
 * @property string|null $note
 * @property int $active
 * @property int $sort_order
 */
class WhtRate extends \yii\db\ActiveRecord
{
    const PARTY_JURISTIC = 'juristic';
    const PARTY_PERSONAL = 'personal';

    public static function tableName()
    {
        return 'purchase_wht_rate';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['code', 'party_type', 'title'], 'required'],
            [['rate'], 'number', 'min' => 0, 'max' => 100],
            [['threshold'], 'number', 'min' => 0],
            [['active', 'sort_order'], 'integer'],
            [['code', 'party_type'], 'string', 'max' => 20],
            [['title', 'law_ref'], 'string', 'max' => 255],
            [['note'], 'string'],
            [['party_type'], 'in', 'range' => array_keys(self::partyTypeList())],
            [['active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'ประเภทสัญญา',
            'party_type' => 'ผู้รับเงิน',
            'title' => 'คำอธิบาย',
            'rate' => 'อัตรา (%)',
            'threshold' => 'ยอดจ่ายขั้นต่ำที่เริ่มหัก (บาท)',
            'law_ref' => 'อ้างอิงกฎหมาย',
            'note' => 'หมายเหตุ',
            'active' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    public static function partyTypeList()
    {
        return [
            self::PARTY_JURISTIC => 'นิติบุคคล',
            self::PARTY_PERSONAL => 'บุคคลธรรมดา',
        ];
    }

    public function partyTypeName()
    {
        return self::partyTypeList()[$this->party_type] ?? $this->party_type;
    }

    public function contractTypeName()
    {
        return Contract::typeList()[$this->code] ?? $this->code;
    }

    /**
     * หาแถวที่ใช้กับสัญญาประเภทนี้ คืน null เมื่อไม่มีหรือถูกปิดไว้
     * ผู้เรียกต้องรับมือกับ null เอง — ระบบต้องไม่เดาอัตราภาษีให้เมื่อไม่มีค่าตั้งไว้
     */
    public static function findRate(?string $code, ?string $partyType): ?self
    {
        if (empty($code)) {
            return null;
        }
        return self::findOne([
            'code' => $code,
            'party_type' => $partyType ?: self::PARTY_JURISTIC,
            'active' => 1,
        ]);
    }

    /** true เมื่อยังไม่มีใครแตะค่าที่ระบบ seed ไว้ — ใช้ขึ้นป้ายเตือนในหน้าตั้งค่า */
    public static function needsReview(): bool
    {
        return self::find()->where(['like', 'note', 'ยังไม่ผ่านการยืนยันจากงานการเงิน'])->exists();
    }
}
