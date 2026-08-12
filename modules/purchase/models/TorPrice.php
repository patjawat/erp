<?php

namespace app\modules\purchase\models;

use yii\db\Expression;
use app\models\Categorise;
use yii\behaviors\TimestampBehavior;

/**
 * ใบสืบราคาของ TOR — 1 แถว = ผู้เสนอราคา 1 ราย
 *
 * ระเบียบกำหนดให้สืบราคา "ไม่น้อยกว่า 3 แหล่ง" จึงไม่ล็อกจำนวนแถวไว้ที่ 3
 *
 * เก็บทั้ง vendor_id และ vendor_name เพราะ 2 กรณีใช้ต่างกัน:
 *   - เลือกจากทะเบียนผู้แทนจำหน่าย -> ได้ vendor_id และ snapshot ชื่อลง vendor_name
 *   - แหล่งอ้างอิงที่ไม่ได้อยู่ในทะเบียน (เว็บไซต์/ราคาหน่วยงานอื่น) -> มีแต่ vendor_name
 * เวลาแสดงผลยึด vendor_name เสมอ เพื่อให้เอกสารเก่าคงชื่อ ณ วันที่สืบราคา
 * แม้ทะเบียนผู้ขายจะถูกแก้ไขภายหลัง
 *
 * @property int $id
 * @property int $tor_id
 * @property int $seq
 * @property string|null $vendor_id
 * @property string|null $vendor_name
 * @property string|null $detail
 * @property float $price
 */
class TorPrice extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'purchase_tor_price';
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
        ];
    }

    public function rules()
    {
        return [
            [['tor_id'], 'required'],
            [['tor_id', 'seq'], 'integer'],
            [['price'], 'number', 'min' => 0],
            [['vendor_id', 'vendor_name'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 500],
            [['price'], 'default', 'value' => 0],
            [['seq'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'seq' => 'ที่',
            'vendor_id' => 'ผู้เสนอราคา',
            'vendor_name' => 'ชื่อผู้เสนอราคา/แหล่งอ้างอิง',
            'detail' => 'รายละเอียดที่เสนอ',
            'price' => 'ราคา (บาท)',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // เลือกจากทะเบียนแล้วยังไม่มีชื่อ -> snapshot ชื่อ ณ ตอนบันทึก
        if (!empty($this->vendor_id) && empty($this->vendor_name)) {
            $this->vendor_name = (string) Categorise::find()
                ->select('title')
                ->where(['name' => 'vendor', 'code' => $this->vendor_id])
                ->scalar();
        }

        // พิมพ์ชื่อเองแล้วบังเอิญตรงกับผู้ขายในทะเบียนพอดี -> ผูก vendor_id ให้
        // เพื่อให้รายงานฝั่งผู้ขายนับรายการนี้ได้ด้วย โดยผู้ใช้ไม่ต้องเลือกซ้ำ
        if (empty($this->vendor_id) && !empty($this->vendor_name)) {
            $this->vendor_id = Categorise::find()
                ->select('code')
                ->where(['name' => 'vendor', 'title' => trim($this->vendor_name)])
                ->andWhere(['!=', 'code', '-'])
                ->scalar() ?: null;
        }

        return true;
    }

    public function getVendor()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vendor_id'])->andOnCondition(['name' => 'vendor']);
    }

    /** ชื่อที่ใช้แสดง — ยึด snapshot ก่อน แล้วค่อย fallback ไปทะเบียน */
    public function displayName()
    {
        if (!empty($this->vendor_name)) {
            return $this->vendor_name;
        }
        return $this->vendor->title ?? '-';
    }
}
