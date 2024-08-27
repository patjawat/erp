<?php

namespace app\modules\sm\models;

use Yii;
use yii\helpers\Html;
use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use yii\helpers\ArrayHelper;
use app\modules\filemanager\models\Uploads;

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
 * @property int|null $qty จำนวน
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property string|null $unit_items
 * @property string|null $ma_items รายการบำรุงรักษา
 * @property int|null $active
 */
class ServiceItem extends \yii\db\ActiveRecord
{
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
            [['name'], 'required'],
            [['qty', 'active'], 'integer'],
            [['data_json', 'unit_items', 'ma_items'], 'safe'],
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
            'qty' => 'Qty',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'unit_items' => 'Unit Items',
            'ma_items' => 'Ma Items',
            'active' => 'Active',
        ];
    }


    public function ShowImg()
    {
        $model = Uploads::find()->where(['ref' => $this->ref])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }
    }

    public function Avatar(){
        return '<div class="d-flex">
        '.Html::img($this->ShowImg(),['class' => 'avatar']).'
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                        '.$this->title.'
                                    </h6>
                                    <p class="text-primary mb-0 fs-13">'. $this->ViewTypeName()['title'].'</p>
                                </div>
                            </div>';
    }

    //แสดงรูปแบบประเภท
    public function ViewTypeName(){
            $model =  self::find()->where(['name' => $this->name])->one();
            if($model->name == 'product_item'){
                return [
                    'title' =>  isset($this->productType->title) ? $this->productType->title : 'ไม่ได้ระบุ',
                    'code' => (isset($model->data_json['unit']) ? $model->data_json['unit'] : '-')
                ];
            }else if($model->name == 'asset_item'){
                return [
                    'title' =>  $model->title,
                    'code' => $model->code
                ];
            }else{
                return [
                    'title' =>  '',
                    'code' => ''
                ];
            }
               

    }

    public function AvatarXl(){
                return '<div class="d-flex">
                        '.Html::img($this->ShowImg(),['class' => 'avatar']).'
                            <div class="avatar-detail">
                                <h5 class="mb-15" data-bs-toggle="tooltip" data-bs-placement="top">'.$this->title.'</h5>
                                    <p class="text-primary mb-0 fs-6">'.$this->productType->title.' <code>('.(isset($this->data_json['unit']) ? $this->data_json['unit'] : '-').')</code></p>
                            </div>
        </div>';
    }

}
