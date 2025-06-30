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
    public $asset_type_id;
    public static function tableName()
    {
        return 'asset_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['group_id','data_json','fsn_auto','q','asset_type_id'], 'safe'],
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
            'title' => 'ชื่อรายการ',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'active' => 'Active',
            'fsn_auto' => 'fsn_auto'
        ];
    }

    public function beforeSave($insert)
    {

        return parent::beforeSave($insert);
    }


            public function NextId()
                {
                $prefix = $this->category_id;
                
                 $maxNumber = Yii::$app->db
                 ->createCommand("SELECT CONCAT(:cat,'-', MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) + 1) AS next_ht_code
                    FROM categorise WHERE name = 'asset_item' AND category_id = :cat;")
                    ->bindValue(':cat', $prefix)
                    ->queryScalar();

                    return $maxNumber;
            }



        // หมายเลขถัดไปของทรัพย์สิน
        // ใช้สำหรับการสร้างหมายเลขทรัพย์สินใหม่
        public function nextCode()
        {
                $sql = "SELECT x.* FROM(SELECT 
                CONCAT(:fsn,'/', 
                    SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1), 
                    '.', 
                    LPAD(MAX(CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)) + 1, 2, '0')
                ) AS next_code
                    FROM asset 
                    WHERE code LIKE CONCAT(:fsn, '/%')
                    GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1)) as x
                    order BY next_code DESC limit 1;";
                    $query = Yii::$app->db->createCommand($sql)
                    ->bindValue(':fsn', $this->code)
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


        

    public function listFsnName(){
        return ArrayHelper::map(self::find()->all(),'code','title');
    }

    public function FsnGroup(){
        return ArrayHelper::map(self::find()->where(['name' => 'asset_group'])->all(),'code','title');
    }

    public function FsnType(){
        return ArrayHelper::map(self::find()->where(['name' => 'asset_type'])->all(),'code','title');
    }

        public function listAssetType(){
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type','group_id' => 'EQUIP'])->all(),'code','title');
    }
    
        public function listAssetCategory(){
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_category'])->all(),'code','title');
    }



    public function getCategory()
{
    return $this->hasOne(Categorise::class, ['code' => 'category_id'])
        ->andOnCondition(['categorise.name' => 'asset_category']);
}

public function getType()
{
    return $this->hasOne(Categorise::class, ['code' => 'category_id'])
        ->via('category')
        ->andOnCondition(['categorise.name' => 'asset_type']);
}

public function getGroup()
{
    return $this->hasOne(Categorise::class, ['code' => 'category_id'])
        ->via('type')
        ->andOnCondition(['categorise.name' => 'asset_group']);
}



    public function ShowImg(){
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'fsn'])->one();
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
             WHERE c.name = 'fsn'
             AND t.category_id = :id";
             $query = Yii::$app->db->createCommand($sql)
             ->bindParam(':id', $id)
             ->queryScalar();
             return $query;
        }

}