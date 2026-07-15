<?php

namespace app\modules\am\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $group_id กลุ่ม
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property int|null $qty
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property string|null $ma_items รายการบำรุงรักษา
 * @property int|null $active
 */
class AssetCategory extends \yii\db\ActiveRecord
{


    public $q;

    /**
     * {@inheritdoc}
     */
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
            [['ref', 'group_id', 'category_id', 'code', 'emp_id', 'title', 'qty', 'description', 'data_json', 'ma_items'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['name'], 'required'],
            [['qty', 'active'], 'integer'],
            [['useful_life'], 'integer', 'min' => 1],
            [['depreciation_rate'], 'number', 'min' => 0],
            [['data_json', 'ma_items','q'], 'safe'],
            [['ref', 'group_id', 'category_id', 'code', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
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
            'group_id' => 'กลุ่ม',
            'category_id' => 'Category ID',
            'code' => 'รหัส',
            'emp_id' => 'พนักงาน',
            'name' => 'ชนิดข้อมูล',
            'title' => 'ชื่อ',
            'qty' => 'Qty',
            'description' => 'รายละเอียดเพิ่มเติม',
            'data_json' => 'Data Json',
            'ma_items' => 'รายการบำรุงรักษา',
            'active' => 'Active',
            'useful_life' => 'อายุการใช้งาน (ปี)',
            'depreciation_rate' => 'อัตราค่าเสื่อม (%)',
        ];
    }


            public function getAssetType()  
        {
            return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
        }

            public function listAssetType($group = 'EQUIP'){
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','group_id' => $group])->all(),'code','title');
    }
    

}
