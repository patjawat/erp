<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property int|null $active
 */
class Vendor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $tel,$email,$add,$contact_name,$address;
    public static function tableName()
    {
        return 'categorise';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','code','title'], 'required'],
            [['data_json'], 'safe'],
            [['active'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
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
            'category_id' => 'Category ID',
            'code' => 'รหัส',
            'emp_id' => 'พนักงาน',
            'name' => 'ชนิดข้อมูล',
            'title' => 'ชื่อ',
            'description' => 'รายละเอียดเพิ่มเติม',
            'data_json' => 'Data Json',
            'active' => 'Active',
            'address' => 'ที่อยู่',
            'address' => 'หมายเลขโทรศัพท์',
            'contact_name' => 'ผู้ติดต่อ',
            'account_name' => 'ชื่อบัญชี',
            'account_number' => 'หมายเลขบัญชี',
            'bank_name' => 'ชื่อธนาคาร',
        ];
    }

    public function afterFind()
    {
        $this->tel = isset($this->data_json['tel']) ? $this->data_json['tel'] : '-';
        $this->address = isset($this->data_json['address']) ? $this->data_json['address'] : '-';
        $this->email = isset($this->data_json['email']) ? $this->data_json['email'] : '-';
        $this->contact_name = isset($this->data_json['contact_name']) ? $this->data_json['contact_name'] : '-';
        parent::afterFind();
    }
}
