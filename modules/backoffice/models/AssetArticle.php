<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "asset_article".
 *
 * @property int $ARTICLE_ID
 * @property string|null $ARTICLE_NUM รหัสครุภัณฑ์
 * @property string|null $ARTICLE_NUM_OLD เลขที่ครุภัณฑ์เดิมที่มีอยู่
 * @property string|null $NUM1
 * @property string|null $NUM2
 * @property string|null $NUM3
 * @property string|null $NUM4
 * @property string|null $ARTICLE_NAME
 * @property string|null $ARTICLE_NAME_EN
 * @property string|null $MODEL_ID
 * @property string|null $SIZE_ID
 * @property string|null $DEVICE_NUM
 * @property string|null $SERIAL_NO
 * @property string|null $SUPPLIER_ID ผู้ผลิด/ผู้ขาย
 * @property string|null $SALE_ID
 * @property string|null $COUNTRY_ID
 * @property string|null $TYPE_ID
 * @property string|null $TYPE_SUB_ID
 * @property string|null $BRAND_ID
 * @property int|null $SECTION_ID
 * @property string|null $COLOR_ID
 * @property string|null $RECEIVE_DATE
 * @property float|null $PRICE_PER_UNIT
 * @property string|null $TYPE_MONEY_ID
 * @property string|null $TYPE_MONEY_COMMENT
 * @property string|null $METHOD_ID
 * @property string|null $DOC_NO_NUM
 * @property string|null $DOC_NO_FILE
 * @property string|null $REMARK
 * @property string|null $DEPT
 * @property int|null $UNIT_ID
 * @property float|null $EXPIRED
 * @property float|null $DEPREC
 * @property string|null $NOTES
 * @property string|null $LOCATEDIVISION
 * @property string|null $LOCATEDEPT
 * @property string|null $LOCATESECTION
 * @property string|null $WAY_NAME
 * @property string|null $CHANGE
 * @property string|null $SALER
 * @property string|null $STATUS_ID
 * @property string|null $DEPARTMENT_SUB_ID หน่วยงานที่รับผิดชอบ
 * @property string|null $PERSON_ID ผู้รับผิดชอบ
 * @property resource|null $IMAGES ภาพครุภัณฑ์
 * @property string|null $EXPIRE_DOC ทะเบียนรถ
 * @property string|null $EXPIRE_DATE
 * @property string|null $EXPIRE_DATE_SUBMIT
 * @property string|null $DATE_DOC
 * @property string|null $vehicle_type_id ประเภทรถ
 * @property string|null $CAR_REG
 * @property string|null $UPDATE_PERSON_ID
 * @property string|null $UPDATE_DATE_TIME
 * @property string|null $ARTICLE_MODELS แบบ
 * @property string|null $MODEL_REGIS แบบทะเบียน
 * @property string|null $GROUP_ID
 * @property string|null $CLASS_ID
 * @property int|null $PROPOTIES_ID
 * @property string|null $ROOM_ID
 * @property string|null $LEVEL_ID
 * @property float|null $OLD_USE อายุการใช้งาน
 * @property string|null $VENDOR_ID ผู้ผลิด/ผู้ขาย
 * @property string|null $DEP_ID ประจำอยู่หน่วยงาน
 * @property string|null $LOCATION_ID สถานที่จัดเก็บปัจจุบัน
 * @property string|null $IMG ภาพครุภัณฑ์
 * @property string|null $BUY_ID วิธีการจัดซื้อ
 * @property string|null $LOCATION_LEVEL_ID อยู่ชั้นไหน
 * @property string|null $LEVEL_ROOM_ID อยู่ห้องไหน
 * @property string|null $AR_REGIS_ID รหัส ID การลงทะเบียน sup_regis_ar
 * @property string|null $YEAR_ID ปีงบประมาณ
 * @property string|null $OPENS
 * @property string|null $DECLINE_ID ประเภทค่าเสื่อมราคา
 * @property string|null $CODE_REF รหัสอ้างอิง
 * @property string|null $BUDGET_ID งบประมาณ
 * @property string|null $ARTICLE_PROP คุณลักษณะ
 * @property string|null $SUP_FSN เลข FSN
 * @property string|null $SUP_ID รหัสทะเบียนพัสดุ อ้างถึง
 * @property int|null $AR_REGIS_COUNT นับจำนวนต่อ
 * @property string|null $TYPE_VALUE_ID
 * @property string|null $QRCODE
 * @property string|null $PM_TYPE_ID
 * @property string|null $CAL_TYPE_ID
 * @property string|null $RISK_TYPE_ID
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $DEP_SUB_SUB_ID
 * @property string|null $DEP_SUB_SUB_NAME
 */
class AssetArticle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asset_article';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['SECTION_ID', 'UNIT_ID', 'PROPOTIES_ID', 'AR_REGIS_COUNT', 'DEP_SUB_SUB_ID'], 'integer'],
            [['RECEIVE_DATE', 'EXPIRE_DATE', 'EXPIRE_DATE_SUBMIT', 'DATE_DOC', 'UPDATE_DATE_TIME', 'updated_at', 'created_at'], 'safe'],
            [['PRICE_PER_UNIT', 'EXPIRED', 'DEPREC', 'OLD_USE'], 'number'],
            [['REMARK', 'IMAGES', 'IMG', 'OPENS', 'QRCODE'], 'string'],
            [['ARTICLE_NUM', 'ARTICLE_NAME', 'ARTICLE_NAME_EN', 'DEVICE_NUM', 'DEPT', 'EXPIRE_DOC', 'CAR_REG', 'ARTICLE_PROP'], 'string', 'max' => 100],
            [['ARTICLE_NUM_OLD', 'TYPE_MONEY_COMMENT', 'ROOM_ID', 'LEVEL_ID'], 'string', 'max' => 50],
            [['NUM1', 'NUM3', 'YEAR_ID'], 'string', 'max' => 4],
            [['NUM2'], 'string', 'max' => 3],
            [['NUM4', 'MODEL_ID', 'SIZE_ID', 'BRAND_ID', 'COLOR_ID', 'STATUS_ID', 'PERSON_ID', 'vehicle_type_id', 'UPDATE_PERSON_ID', 'GROUP_ID', 'CLASS_ID', 'LOCATION_ID', 'BUY_ID', 'LOCATION_LEVEL_ID', 'LEVEL_ROOM_ID', 'DECLINE_ID', 'BUDGET_ID', 'PM_TYPE_ID', 'CAL_TYPE_ID', 'RISK_TYPE_ID'], 'string', 'max' => 10],
            [['SERIAL_NO', 'DOC_NO_NUM', 'DOC_NO_FILE', 'CODE_REF'], 'string', 'max' => 30],
            [['SUPPLIER_ID', 'SALE_ID', 'COUNTRY_ID', 'TYPE_ID', 'TYPE_SUB_ID', 'TYPE_MONEY_ID', 'METHOD_ID', 'DEPARTMENT_SUB_ID', 'VENDOR_ID', 'DEP_ID', 'SUP_FSN'], 'string', 'max' => 20],
            [['NOTES', 'LOCATEDIVISION', 'LOCATEDEPT', 'LOCATESECTION', 'WAY_NAME', 'CHANGE', 'SALER'], 'string', 'max' => 254],
            [['ARTICLE_MODELS', 'MODEL_REGIS', 'DEP_SUB_SUB_NAME'], 'string', 'max' => 255],
            [['AR_REGIS_ID', 'SUP_ID', 'TYPE_VALUE_ID'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ARTICLE_ID' => 'Article ID',
            'ARTICLE_NUM' => 'รหัสครุภัณฑ์',
            'ARTICLE_NUM_OLD' => 'เลขที่ครุภัณฑ์เดิมที่มีอยู่',
            'NUM1' => 'Num1',
            'NUM2' => 'Num2',
            'NUM3' => 'Num3',
            'NUM4' => 'Num4',
            'ARTICLE_NAME' => 'Article Name',
            'ARTICLE_NAME_EN' => 'Article Name En',
            'MODEL_ID' => 'Model ID',
            'SIZE_ID' => 'Size ID',
            'DEVICE_NUM' => 'Device Num',
            'SERIAL_NO' => 'Serial No',
            'SUPPLIER_ID' => 'ผู้ผลิด/ผู้ขาย',
            'SALE_ID' => 'Sale ID',
            'COUNTRY_ID' => 'Country ID',
            'TYPE_ID' => 'Type ID',
            'TYPE_SUB_ID' => 'Type Sub ID',
            'BRAND_ID' => 'Brand ID',
            'SECTION_ID' => 'Section ID',
            'COLOR_ID' => 'Color ID',
            'RECEIVE_DATE' => 'Receive Date',
            'PRICE_PER_UNIT' => 'Price Per Unit',
            'TYPE_MONEY_ID' => 'Type Money ID',
            'TYPE_MONEY_COMMENT' => 'Type Money Comment',
            'METHOD_ID' => 'Method ID',
            'DOC_NO_NUM' => 'Doc No Num',
            'DOC_NO_FILE' => 'Doc No File',
            'REMARK' => 'Remark',
            'DEPT' => 'Dept',
            'UNIT_ID' => 'Unit ID',
            'EXPIRED' => 'Expired',
            'DEPREC' => 'Deprec',
            'NOTES' => 'Notes',
            'LOCATEDIVISION' => 'Locatedivision',
            'LOCATEDEPT' => 'Locatedept',
            'LOCATESECTION' => 'Locatesection',
            'WAY_NAME' => 'Way Name',
            'CHANGE' => 'Change',
            'SALER' => 'Saler',
            'STATUS_ID' => 'Status ID',
            'DEPARTMENT_SUB_ID' => 'หน่วยงานที่รับผิดชอบ',
            'PERSON_ID' => 'ผู้รับผิดชอบ',
            'IMAGES' => 'ภาพครุภัณฑ์',
            'EXPIRE_DOC' => 'ทะเบียนรถ',
            'EXPIRE_DATE' => 'Expire Date',
            'EXPIRE_DATE_SUBMIT' => 'Expire Date Submit',
            'DATE_DOC' => 'Date Doc',
            'vehicle_type_id' => 'ประเภทรถ',
            'CAR_REG' => 'Car Reg',
            'UPDATE_PERSON_ID' => 'Update Person ID',
            'UPDATE_DATE_TIME' => 'Update Date Time',
            'ARTICLE_MODELS' => 'แบบ',
            'MODEL_REGIS' => 'แบบทะเบียน',
            'GROUP_ID' => 'Group ID',
            'CLASS_ID' => 'Class ID',
            'PROPOTIES_ID' => 'Propoties ID',
            'ROOM_ID' => 'Room ID',
            'LEVEL_ID' => 'Level ID',
            'OLD_USE' => 'อายุการใช้งาน',
            'VENDOR_ID' => 'ผู้ผลิด/ผู้ขาย',
            'DEP_ID' => 'ประจำอยู่หน่วยงาน',
            'LOCATION_ID' => 'สถานที่จัดเก็บปัจจุบัน',
            'IMG' => 'ภาพครุภัณฑ์',
            'BUY_ID' => 'วิธีการจัดซื้อ',
            'LOCATION_LEVEL_ID' => 'อยู่ชั้นไหน',
            'LEVEL_ROOM_ID' => 'อยู่ห้องไหน',
            'AR_REGIS_ID' => 'รหัส ID การลงทะเบียน sup_regis_ar',
            'YEAR_ID' => 'ปีงบประมาณ',
            'OPENS' => 'Opens',
            'DECLINE_ID' => 'ประเภทค่าเสื่อมราคา',
            'CODE_REF' => 'รหัสอ้างอิง',
            'BUDGET_ID' => 'งบประมาณ',
            'ARTICLE_PROP' => 'คุณลักษณะ',
            'SUP_FSN' => 'เลข FSN',
            'SUP_ID' => 'รหัสทะเบียนพัสดุ อ้างถึง',
            'AR_REGIS_COUNT' => 'นับจำนวนต่อ',
            'TYPE_VALUE_ID' => 'Type Value ID',
            'QRCODE' => 'Qrcode',
            'PM_TYPE_ID' => 'Pm Type ID',
            'CAL_TYPE_ID' => 'Cal Type ID',
            'RISK_TYPE_ID' => 'Risk Type ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'DEP_SUB_SUB_ID' => 'Dep Sub Sub ID',
            'DEP_SUB_SUB_NAME' => 'Dep Sub Sub Name',
        ];
    }
}
