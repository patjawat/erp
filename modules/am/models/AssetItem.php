<?php

namespace app\modules\am\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
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
    public $q;
    public $schedule;
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
            [['data_json','fsn_auto','q'], 'safe'],
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

        // หมายเลขถัดไปของทรัพย์สิน
        // ใช้สำหรับการสร้างหมายเลขทรัพย์สินใหม่
        public function nextCode()
        {
                $sql = "SELECT x.* FROM(SELECT 
                CONCAT(:asset_item,'/', 
                    SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1), 
                    '.', 
                    LPAD(MAX(CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)) + 1, 2, '0')
                ) AS next_code
                    FROM asset 
                    WHERE code LIKE CONCAT(:asset_item, '/%')
                    GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1)) as x
                    order BY next_code DESC limit 1;";
                    $query = Yii::$app->db->createCommand($sql)
                    ->bindValue(':asset_item', $this->code)
                    ->queryOne();

            $newCode  = $this->code.'/'.substr(AppHelper::YearBudget(), -2).'01';
            return $query['next_code'] ?? $newCode;
    }
        //section Relationships
        public function getAssetType()
        {
            return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
        }


//หน่วยนับ
    public function listUnit(){
        return ArrayHelper::map(self::find()->where(['name' => 'unit'])->all(),'title','title');
    }

        public function listAssetType(){
        return ArrayHelper::map(self::find()->where(['name' => 'asset_type'])->all(),'code','title');
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
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'asset_item'])->one();
            if($model){
                return [
                    'image' => FileManagerHelper::getImg($model->id),
                    'isFile' => true,
                ];
            }else{
                 return [
                    'image' => Yii::getAlias('@web') . '/img/placeholder-img.jpg',
                    'isFile' => false,
                ];
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