<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "hrd_person".
 *
 * @property int $ID
 * @property int|null $FINGLE_ID
 * @property string|null $HR_CID รหัสบัตรประชาชน
 * @property string|null $HR_PREFIX_ID
 * @property string|null $HR_FNAME ชื่อ
 * @property string|null $HR_LNAME นามสกุล
 * @property string|null $HR_EN_NAME
 * @property string|null $PAY
 * @property string|null $SEX
 * @property string|null $HR_BLOODGROUP_ID
 * @property string|null $HR_MARRY_STATUS_ID
 * @property string|null $HR_BIRTHDAY วันเกิด
 * @property string|null $HR_PHONE เบอร์โทรศัพท์
 * @property string|null $HR_EMAIL อีเมลล์ 
 * @property string|null $HR_FACEBOOK เฟสบุ็ค 
 * @property string|null $HR_LINE ไลน์
 * @property string|null $HR_HOME_NUMBER บ้านเลขที่
 * @property string|null $HR_VILLAGE_NO หมู่้บ้าน
 * @property string|null $HR_ROAD_NAME ถนน 
 * @property string|null $HR_SOI_NAME ชื่อซอย
 * @property string|null $PROVINCE_ID
 * @property string|null $AMPHUR_ID
 * @property string|null $TUMBON_ID
 * @property string|null $HR_VILLAGE_NAME ชื่อบ้าน
 * @property string|null $HR_ZIPCODE รหัสไปรษณีย์
 * @property string|null $HR_RELIGION_ID
 * @property string|null $HR_NATIONALITY_ID
 * @property string|null $HR_CITIZENSHIP_ID
 * @property string|null $HR_DEPARTMENT_ID
 * @property string|null $HR_DEPARTMENT_SUB_ID
 * @property string|null $HR_POSITION_ID
 * @property string|null $HR_FARTHER_NAME ชื่อพ่อ
 * @property string|null $HR_FARTHER_CID รหัสบัตรประชาชนพ่อ
 * @property string|null $HR_MATHER_NAME ชื่อแม่
 * @property string|null $HR_MATHER_CID รหัสบัตรประชาชนแม่
 * @property string|null $HR_STATUS_ID
 * @property string|null $HR_LEVEL_ID
 * @property string|null $HR_IMAGE รูปภาพบุคลากร
 * @property string|null $HR_USERNAME
 * @property string|null $HR_PASSWORD รหัสผ่านบุคลากร
 * @property string|null $DATE_TIME_UPDATE วันที่แก้ไขข้อมูลล่าสุด 
 * @property string|null $DATE_TIME_CREATE วันที่ลงทะเบียน
 * @property string|null $HR_STARTWORK_DATE วันเริ่มทำงาน
 * @property string|null $HR_WORK_REGISTER_DATE วันที่บรรจุ
 * @property string|null $HR_WORK_END_DATE วันออกจากงาน 
 * @property string|null $HR_PIC
 * @property string|null $HR_POSITION_NUM
 * @property float|null $HR_SALARY
 * @property float|null $MONEY_POSITION
 * @property string|null $IP_INSERT
 * @property string|null $IP_UPDATE
 * @property string|null $PCODE
 * @property string|null $PERSON_TYPE
 * @property string|null $PCODE_MAIN
 * @property string|null $USER_TYPE
 * @property float|null $HR_HIGH
 * @property float|null $HR_WEIGHT
 * @property string|null $PERMIS_ID
 * @property string|null $VCODE เลขที่ใบประกอบวิชาชีพ
 * @property string|null $VCODE_DATE วันที่ได้รับใบประกอบ
 * @property string|null $VGROUP_ID คุณลักษณะการจัดกลุ่ม
 * @property string|null $NICKNAME
 * @property string|null $HR_PERSON_TYPE_ID
 * @property string|null $POSITION_IN_WORK
 * @property string|null $BOOK_BANK_NUMBER
 * @property string|null $BOOK_BANK_NAME
 * @property string|null $BOOK_BANK
 * @property string|null $BOOK_BANK_BRANCH
 * @property string|null $HR_DATE_PUT วันที่เริ่มเป็นข้าราชการ
 * @property string|null $HR_HOME_NUMBER_1
 * @property string|null $HR_HOME_NUMBER_2
 * @property string|null $HR_ROAD_NAME_1
 * @property string|null $HR_ROAD_NAME_2
 * @property string|null $HR_VILLAGE_NO_1
 * @property string|null $HR_VILLAGE_NO_2
 * @property string|null $HR_VILLAGE_NAME_1
 * @property string|null $HR_VILLAGE_NAME_2
 * @property string|null $PROVINCE_ID_1
 * @property string|null $PROVINCE_ID_2
 * @property string|null $AMPHUR_ID_1
 * @property string|null $AMPHUR_ID_2
 * @property string|null $TUMBON_ID_1
 * @property string|null $TUMBON_ID_2
 * @property string|null $HR_ZIPCODE_1
 * @property string|null $HR_ZIPCODE_2
 * @property string|null $HR_HOME_PHONE_1
 * @property string|null $HR_HOME_PHONE_2
 * @property string|null $SAME_ADDR_1 เหมือนกับที่อยู่ตามบัตร
 * @property string|null $SAME_ADDR_2 เหมือนกับที่อยู่ตามบัตร
 * @property string|null $HR_BANK_ID
 * @property resource|null $HR_FINGLE1 ภาพลายนิ้วมือ
 * @property resource|null $HR_FINGLE2
 * @property resource|null $HR_FINGLE3
 * @property resource|null $LICEN
 * @property string|null $BOOK_BANK_OT_NUMBER
 * @property string|null $BOOK_BANK_OT_NAME
 * @property string|null $HR_BANK_OT_ID
 * @property string|null $BOOK_BANK_OT
 * @property string|null $BOOK_BANK_OT_BRANCH
 * @property string|null $MARRY_CID
 * @property string|null $MARRY_NAME
 * @property string|null $HR_DEPARTMENT_SUB_SUB_ID
 * @property string|null $HOS_USE_CODE
 * @property string|null $HR_KIND_ID ชนิดราชการ พลเรือน ทหาร
 * @property string|null $HR_KIND_TYPE_ID ประเภทของชนิด
 * @property string|null $LINE_NAME
 * @property string|null $LINE_TOKEN
 * @property string|null $LINE_TOKEN1
 * @property string|null $LINE_TOKEN2
 * @property string|null $updated_at
 * @property string|null $HR_IMAGE_NAME
 * @property string|null $created_at
 * @property string|null $HR_AGENCY_ID
 * @property string|null $LEAVEDAY_ACTIVE
 * @property string|null $HR_SOI_NAME_1
 * @property string|null $HR_SOI_NAME_2
 */
class Person extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hrd_person';
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
            [['FINGLE_ID'], 'integer'],
            [['PAY', 'SEX', 'HR_IMAGE', 'PERSON_TYPE', 'USER_TYPE', 'SAME_ADDR_1', 'SAME_ADDR_2', 'HR_FINGLE1', 'HR_FINGLE2', 'HR_FINGLE3', 'LICEN', 'LEAVEDAY_ACTIVE'], 'string'],
            [['HR_BIRTHDAY', 'DATE_TIME_UPDATE', 'DATE_TIME_CREATE', 'HR_STARTWORK_DATE', 'HR_WORK_REGISTER_DATE', 'HR_WORK_END_DATE', 'VCODE_DATE', 'HR_DATE_PUT', 'updated_at', 'created_at'], 'safe'],
            [['HR_SALARY', 'MONEY_POSITION', 'HR_HIGH', 'HR_WEIGHT'], 'number'],
            [['HR_CID', 'HR_FARTHER_CID', 'HR_MATHER_CID', 'HR_HOME_NUMBER_1', 'HR_HOME_NUMBER_2', 'BOOK_BANK_OT_NUMBER', 'BOOK_BANK_OT', 'HOS_USE_CODE'], 'string', 'max' => 20],
            [['HR_PREFIX_ID', 'HR_BLOODGROUP_ID', 'PROVINCE_ID', 'HR_RELIGION_ID'], 'string', 'max' => 11],
            [['HR_FNAME', 'HR_LNAME', 'HR_EMAIL', 'HR_HOME_NUMBER', 'HR_ROAD_NAME_1', 'HR_ROAD_NAME_2', 'HR_VILLAGE_NO_1', 'HR_VILLAGE_NO_2', 'HR_VILLAGE_NAME_1', 'HR_VILLAGE_NAME_2', 'BOOK_BANK_OT_NAME', 'BOOK_BANK_OT_BRANCH', 'MARRY_NAME', 'LINE_NAME'], 'string', 'max' => 50],
            [['HR_EN_NAME', 'HR_FACEBOOK', 'HR_LINE', 'HR_VILLAGE_NO', 'HR_VILLAGE_NAME', 'HR_FARTHER_NAME', 'HR_MATHER_NAME', 'HR_USERNAME', 'HR_PASSWORD', 'HR_PIC', 'IP_INSERT', 'IP_UPDATE', 'BOOK_BANK_NUMBER', 'BOOK_BANK_NAME', 'BOOK_BANK', 'BOOK_BANK_BRANCH'], 'string', 'max' => 100],
            [['HR_MARRY_STATUS_ID'], 'string', 'max' => 2],
            [['HR_PHONE', 'VCODE', 'HR_HOME_PHONE_1', 'HR_HOME_PHONE_2'], 'string', 'max' => 30],
            [['HR_ROAD_NAME', 'HR_SOI_NAME', 'HR_SOI_NAME_1', 'HR_SOI_NAME_2'], 'string', 'max' => 45],
            [['AMPHUR_ID', 'TUMBON_ID'], 'string', 'max' => 200],
            [['HR_ZIPCODE', 'HR_PERSON_TYPE_ID', 'HR_ZIPCODE_1', 'HR_ZIPCODE_2'], 'string', 'max' => 5],
            [['HR_NATIONALITY_ID', 'HR_CITIZENSHIP_ID', 'HR_DEPARTMENT_ID', 'HR_DEPARTMENT_SUB_ID', 'HR_POSITION_ID'], 'string', 'max' => 3],
            [['HR_STATUS_ID', 'HR_LEVEL_ID', 'PCODE', 'PCODE_MAIN', 'PERMIS_ID', 'VGROUP_ID', 'NICKNAME', 'PROVINCE_ID_1', 'PROVINCE_ID_2', 'AMPHUR_ID_1', 'AMPHUR_ID_2', 'TUMBON_ID_1', 'TUMBON_ID_2', 'HR_BANK_ID', 'HR_BANK_OT_ID', 'HR_DEPARTMENT_SUB_SUB_ID', 'HR_KIND_ID', 'HR_KIND_TYPE_ID'], 'string', 'max' => 10],
            [['HR_POSITION_NUM', 'HR_IMAGE_NAME', 'HR_AGENCY_ID'], 'string', 'max' => 255],
            [['POSITION_IN_WORK', 'LINE_TOKEN', 'LINE_TOKEN1', 'LINE_TOKEN2'], 'string', 'max' => 150],
            [['MARRY_CID'], 'string', 'max' => 13],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'FINGLE_ID' => 'Fingle ID',
            'HR_CID' => 'รหัสบัตรประชาชน',
            'HR_PREFIX_ID' => 'Hr Prefix ID',
            'HR_FNAME' => 'ชื่อ',
            'HR_LNAME' => 'นามสกุล',
            'HR_EN_NAME' => 'Hr En Name',
            'PAY' => 'Pay',
            'SEX' => 'Sex',
            'HR_BLOODGROUP_ID' => 'Hr Bloodgroup ID',
            'HR_MARRY_STATUS_ID' => 'Hr Marry Status ID',
            'HR_BIRTHDAY' => 'วันเกิด',
            'HR_PHONE' => 'เบอร์โทรศัพท์',
            'HR_EMAIL' => 'อีเมลล์
',
            'HR_FACEBOOK' => 'เฟสบุ็ค
',
            'HR_LINE' => 'ไลน์',
            'HR_HOME_NUMBER' => 'บ้านเลขที่',
            'HR_VILLAGE_NO' => 'หมู่้บ้าน',
            'HR_ROAD_NAME' => 'ถนน
',
            'HR_SOI_NAME' => 'ชื่อซอย',
            'PROVINCE_ID' => 'Province ID',
            'AMPHUR_ID' => 'Amphur ID',
            'TUMBON_ID' => 'Tumbon ID',
            'HR_VILLAGE_NAME' => 'ชื่อบ้าน',
            'HR_ZIPCODE' => 'รหัสไปรษณีย์',
            'HR_RELIGION_ID' => 'Hr Religion ID',
            'HR_NATIONALITY_ID' => 'Hr Nationality ID',
            'HR_CITIZENSHIP_ID' => 'Hr Citizenship ID',
            'HR_DEPARTMENT_ID' => 'Hr Department ID',
            'HR_DEPARTMENT_SUB_ID' => 'Hr Department Sub ID',
            'HR_POSITION_ID' => 'Hr Position ID',
            'HR_FARTHER_NAME' => 'ชื่อพ่อ',
            'HR_FARTHER_CID' => 'รหัสบัตรประชาชนพ่อ',
            'HR_MATHER_NAME' => 'ชื่อแม่',
            'HR_MATHER_CID' => 'รหัสบัตรประชาชนแม่',
            'HR_STATUS_ID' => 'Hr Status ID',
            'HR_LEVEL_ID' => 'Hr Level ID',
            'HR_IMAGE' => 'รูปภาพบุคลากร',
            'HR_USERNAME' => 'Hr Username',
            'HR_PASSWORD' => 'รหัสผ่านบุคลากร',
            'DATE_TIME_UPDATE' => 'วันที่แก้ไขข้อมูลล่าสุด
',
            'DATE_TIME_CREATE' => 'วันที่ลงทะเบียน',
            'HR_STARTWORK_DATE' => 'วันเริ่มทำงาน',
            'HR_WORK_REGISTER_DATE' => 'วันที่บรรจุ',
            'HR_WORK_END_DATE' => 'วันออกจากงาน
',
            'HR_PIC' => 'Hr Pic',
            'HR_POSITION_NUM' => 'Hr Position Num',
            'HR_SALARY' => 'Hr Salary',
            'MONEY_POSITION' => 'Money Position',
            'IP_INSERT' => 'Ip Insert',
            'IP_UPDATE' => 'Ip Update',
            'PCODE' => 'Pcode',
            'PERSON_TYPE' => 'Person Type',
            'PCODE_MAIN' => 'Pcode Main',
            'USER_TYPE' => 'User Type',
            'HR_HIGH' => 'Hr High',
            'HR_WEIGHT' => 'Hr Weight',
            'PERMIS_ID' => 'Permis ID',
            'VCODE' => 'เลขที่ใบประกอบวิชาชีพ',
            'VCODE_DATE' => 'วันที่ได้รับใบประกอบ',
            'VGROUP_ID' => 'คุณลักษณะการจัดกลุ่ม',
            'NICKNAME' => 'Nickname',
            'HR_PERSON_TYPE_ID' => 'Hr Person Type ID',
            'POSITION_IN_WORK' => 'Position In Work',
            'BOOK_BANK_NUMBER' => 'Book Bank Number',
            'BOOK_BANK_NAME' => 'Book Bank Name',
            'BOOK_BANK' => 'Book Bank',
            'BOOK_BANK_BRANCH' => 'Book Bank Branch',
            'HR_DATE_PUT' => 'วันที่เริ่มเป็นข้าราชการ',
            'HR_HOME_NUMBER_1' => 'Hr Home Number 1',
            'HR_HOME_NUMBER_2' => 'Hr Home Number 2',
            'HR_ROAD_NAME_1' => 'Hr Road Name 1',
            'HR_ROAD_NAME_2' => 'Hr Road Name 2',
            'HR_VILLAGE_NO_1' => 'Hr Village No 1',
            'HR_VILLAGE_NO_2' => 'Hr Village No 2',
            'HR_VILLAGE_NAME_1' => 'Hr Village Name 1',
            'HR_VILLAGE_NAME_2' => 'Hr Village Name 2',
            'PROVINCE_ID_1' => 'Province Id 1',
            'PROVINCE_ID_2' => 'Province Id 2',
            'AMPHUR_ID_1' => 'Amphur Id 1',
            'AMPHUR_ID_2' => 'Amphur Id 2',
            'TUMBON_ID_1' => 'Tumbon Id 1',
            'TUMBON_ID_2' => 'Tumbon Id 2',
            'HR_ZIPCODE_1' => 'Hr Zipcode 1',
            'HR_ZIPCODE_2' => 'Hr Zipcode 2',
            'HR_HOME_PHONE_1' => 'Hr Home Phone 1',
            'HR_HOME_PHONE_2' => 'Hr Home Phone 2',
            'SAME_ADDR_1' => 'เหมือนกับที่อยู่ตามบัตร',
            'SAME_ADDR_2' => 'เหมือนกับที่อยู่ตามบัตร',
            'HR_BANK_ID' => 'Hr Bank ID',
            'HR_FINGLE1' => 'ภาพลายนิ้วมือ',
            'HR_FINGLE2' => 'Hr Fingle2',
            'HR_FINGLE3' => 'Hr Fingle3',
            'LICEN' => 'Licen',
            'BOOK_BANK_OT_NUMBER' => 'Book Bank Ot Number',
            'BOOK_BANK_OT_NAME' => 'Book Bank Ot Name',
            'HR_BANK_OT_ID' => 'Hr Bank Ot ID',
            'BOOK_BANK_OT' => 'Book Bank Ot',
            'BOOK_BANK_OT_BRANCH' => 'Book Bank Ot Branch',
            'MARRY_CID' => 'Marry Cid',
            'MARRY_NAME' => 'Marry Name',
            'HR_DEPARTMENT_SUB_SUB_ID' => 'Hr Department Sub Sub ID',
            'HOS_USE_CODE' => 'Hos Use Code',
            'HR_KIND_ID' => 'ชนิดราชการ พลเรือน ทหาร',
            'HR_KIND_TYPE_ID' => 'ประเภทของชนิด',
            'LINE_NAME' => 'Line Name',
            'LINE_TOKEN' => 'Line Token',
            'LINE_TOKEN1' => 'Line Token1',
            'LINE_TOKEN2' => 'Line Token2',
            'updated_at' => 'Updated At',
            'HR_IMAGE_NAME' => 'Hr Image Name',
            'created_at' => 'Created At',
            'HR_AGENCY_ID' => 'Hr Agency ID',
            'LEAVEDAY_ACTIVE' => 'Leaveday Active',
            'HR_SOI_NAME_1' => 'Hr Soi Name 1',
            'HR_SOI_NAME_2' => 'Hr Soi Name 2',
        ];
    }
}
