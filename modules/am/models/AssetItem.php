<?php

namespace app\modules\am\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\models\Categorise;
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
class AssetItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $fsn_auto; //กำหนดการให้หมายเลขอัตโนมัติถ้า true;
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
            [['name'], 'required'],
            [['data_json','fsn_auto'], 'safe'],
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
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'title' => 'Title',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'active' => 'Active',
            'fsn_auto' => 'fsn_auto'
        ];
    }

    public function beforeSave($insert)
    {

        // $this->receive_date = AppHelper::DateToDb($this->receive_date);
        if($this->name == 'asset_type'){
            $group = self::find()->select(['name','title','code'])->where(['code' => $this->category_id,'name' => 'asset_group'])->one();
            $array2 = [
                'asset_group' => $group 
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $array2);

            if($this->fsn_auto == "1")
            {
                $this->code = \mdm\autonumber\AutoNumber::generate('G'.$this->category_id.'AT'.'?');
            }

            

            
        }

        if($this->name == 'asset_item'){
            $type = self::find()->select(['name','title','code','category_id'])->where(['code' => $this->category_id,'name' => 'asset_type'])->one();
            $groupType = self::find()->select(['name','title','code'])->where(['code' => $type->category_id,'name' => 'asset_group'])->one();
            $arrayType = [
                'asset_group' => $groupType,
                'asset_type' => $type,
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $arrayType);

            //ถ้าเป็นวัสดุ
            if($this->fsn_auto == "1" && $type->category_id == 1)
            {
                $this->code = \mdm\autonumber\AutoNumber::generate('AI-'.'?');
            }

            // ถ้าเป็นครุภัณฑ์  
            if($this->fsn_auto == "1" && $type->category_id == 2)
            {
                $this->code = \mdm\autonumber\AutoNumber::generate($this->category_id.'-'.'????');
            }
        }


        return parent::beforeSave($insert);
    }


        //section Relationships
        public function getAssetType()
        {
            return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
        }


    public function FsnTypeName()
    {
        // $model =  self::find()->where(['name' => 'asset_type','code' => $this->data_json['asset_type']])->one();
        // if($model)
        // {
        //     return $model->title;
        // }else{
        //     return null;
        // }
    }
    public function FsnGroupName()
    {
        // $model =  self::find()->where(['name' => 'asset_group','code' => $this->category_id])->one();
        // if($model)
        // {
        //     return $model->title;
        // }else{
        //     return null;
        // }
    }
    public function listFsnName(){
        return ArrayHelper::map(self::find()->all(),'code','title');
    }

    public function FsnGroup(){
        return ArrayHelper::map(self::find()->where(['name' => 'asset_group'])->all(),'code','title');
    }

    public function FsnType(){
        return ArrayHelper::map(self::find()->where(['name' => 'asset_type'])->all(),'code','title');
    }


    public function ShowImg(){
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'asset'])->one();
            if($model){
                return FileManagerHelper::getImg($model->id);
            }else{
                return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
            }
    }

    //นับจำนวนประเภทที่อยู่ในกลุ่ม
    public function CountTypeOnGroup()
    {
        return  Categorise::find()->where(['category_id' => $this->code,'name' => 'asset_type'])->count();
    }

        //นับจำนวนรายการที่อยู่ในประเภท
        public function CountItemOnType()
        {
            $id = $this->code;
            $sql = "SELECT count(c.id) FROM categorise c
            LEFT JOIN categorise t ON t.code = c.category_id
             WHERE c.name = 'asset_item'
             AND t.category_id = :id";
             $query = Yii::$app->db->createCommand($sql)
             ->bindParam(':id', $id)
             ->queryScalar();
             return $query;
        }

}
