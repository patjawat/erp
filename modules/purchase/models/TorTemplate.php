<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * คลังแม่แบบคุณลักษณะ — ใช้เติมฟอร์มตอนสร้าง TOR ใหม่
 *
 * ref_price เป็นราคาที่เคยพบในตลาด มีไว้ "ดูประกอบ" ตอนเลือกแม่แบบเท่านั้น
 * ห้ามนำไปเติมลงใบสืบราคาอัตโนมัติไม่ว่ากรณีใด — ใบสืบราคาเป็นเอกสารอ้างอิงราคากลาง
 * ทุกแถวต้องมาจากการสืบราคาจริงของเจ้าหน้าที่
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $category
 * @property string $title
 * @property string|null $unit_name
 * @property int|null $delivery_days
 * @property string|null $warranty
 * @property string|null $standard
 * @property string|null $spec คุณลักษณะเฉพาะตั้งต้น (HTML)
 * @property float|null $ref_price
 * @property int $active
 * @property int $sort_order
 */
class TorTemplate extends \yii\db\ActiveRecord
{
    public $q;

    /** ชื่อหมวดภาษาไทย — เก็บรหัสอังกฤษใน DB เพื่อให้เปลี่ยนคำเรียกได้โดยไม่ต้องแก้ข้อมูล */
    const CATEGORIES = [
        'computer' => 'คอมพิวเตอร์',
        'printer' => 'เครื่องพิมพ์',
        'network' => 'ระบบเครือข่าย',
        'av' => 'โสตทัศนูปกรณ์',
        'camera' => 'กล้อง/บันทึกภาพ',
        'ac' => 'เครื่องปรับอากาศ',
        'electrical' => 'เครื่องใช้ไฟฟ้า',
        'furniture' => 'ครุภัณฑ์สำนักงาน',
        'kitchen' => 'ครุภัณฑ์งานครัว',
        'medical' => 'ครุภัณฑ์การแพทย์',
        'education' => 'ครุภัณฑ์การศึกษา',
        'vehicle' => 'ยานพาหนะ',
        'tools' => 'เครื่องมือ/งานช่าง',
        'software' => 'ซอฟต์แวร์',
    ];

    public static function tableName()
    {
        return 'purchase_tor_template';
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
            [['title'], 'required'],
            [['delivery_days', 'active', 'sort_order'], 'integer'],
            [['ref_price'], 'number', 'min' => 0],
            [['spec'], 'string'],
            [['title', 'warranty', 'standard'], 'string', 'max' => 255],
            [['code', 'category', 'unit_name'], 'string', 'max' => 50],
            [['data_json', 'q'], 'safe'],
            [['active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'รหัสแม่แบบ',
            'category' => 'หมวด',
            'title' => 'ชื่อรายการ',
            'unit_name' => 'หน่วยนับ',
            'delivery_days' => 'ระยะเวลาส่งมอบ (วัน)',
            'warranty' => 'การรับประกัน',
            'standard' => 'มาตรฐาน/การรับรอง',
            'spec' => 'คุณลักษณะเฉพาะ',
            'ref_price' => 'ราคาอ้างอิง',
            'active' => 'เปิดใช้งาน',
        ];
    }

    public function categoryName()
    {
        return self::CATEGORIES[$this->category] ?? ($this->category ?: '-');
    }

    /** หมวดที่มีแม่แบบเปิดใช้งานอยู่จริง พร้อมจำนวน — ใช้ทำปุ่มกรองหมวด */
    public static function activeCategories()
    {
        $rows = self::find()
            ->select(['category', 'c' => 'COUNT(*)'])
            ->where(['active' => 1])
            ->groupBy('category')
            ->asArray()
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['category']] = [
                'label' => self::CATEGORIES[$r['category']] ?? $r['category'],
                'count' => (int) $r['c'],
            ];
        }
        return $out;
    }
}
