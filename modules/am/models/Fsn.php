<?php

namespace app\modules\am\models;

use Yii;
use yii\helpers\ArrayHelper;
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
class Fsn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

     public $q;
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
            [['name','title','code'], 'required'],
            [['data_json','q'], 'safe'],
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
        ];
    }

    public function beforeSave($insert)
    {

        // $this->receive_date = AppHelper::DateToDb($this->receive_date);
        if($this->name == 'asset_name'){
            $group = self::find()->where(['code' => $this->category_id,'name' => 'asset_group'])->one();
            $array2 = [
                'asset_group_text' => isset($group) ? $group->title : '',
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $array2);
        }

        return parent::beforeSave($insert);
    }



    public function FsnTypeName()
    {
        $model =  self::find()->where(['name' => 'asset_type','code' => $this->data_json['asset_type']])->one();
        if($model)
        {
            return $model->title;
        }else{
            return null;
        }
    }
    public function FsnGroupName()
    {
        $model =  self::find()->where(['name' => 'asset_group','code' => $this->category_id])->one();
        if($model)
        {
            return $model->title;
        }else{
            return null;
        }
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
}
