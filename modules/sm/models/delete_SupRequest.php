<?php

namespace app\modules\sm\models;
use app\components\CategoriseHelper;

use Yii;

/**
 * This is the model class for table "sup_request".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $req_code เลขที่ขอ
 * @property string|null $req_date วันที่ขอ
 * @property string|null $req_detail รายละเอียดการร้องขอ
 * @property string|null $req_vendor บริษัท
 * @property string|null $req_amount มูลค่า
 * @property string|null $req_status สถานะการร้องขอ
 * @property string|null $req_dep หน่วยงานที่ขอ
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class SupRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sup_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['req_date', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'req_code', 'req_detail', 'req_vendor', 'req_status', 'req_dep'], 'string', 'max' => 255],
            [['req_amount'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'req_code' => 'เลขที่ขอ',
            'req_date' => 'วันที่ขอ',
            'req_detail' => 'รายละเอียดการร้องขอ',
            'req_vendor' => 'บริษัท',
            'req_amount' => 'มูลค่า',
            'req_status' => 'สถานะการร้องขอ',
            'req_dep' => 'หน่วยงานที่ขอ',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function ListDepartment()
    {
      return CategoriseHelper::Department();
    }
}
