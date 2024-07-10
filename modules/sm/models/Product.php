<?php

namespace app\modules\sm\models;

use app\components\AppHelper;
use yii\helpers\Html;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "asset".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $asset_group แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_item
 * @property string|null $code ครุภัณฑ์
 * @property string|null $fsn_number หมายเลขครุภัณฑ์
 * @property int|null $qty จำนวน
 * @property string|null $receive_date วันที่รับเข้า
 * @property float|null $price ราคา
 * @property int|null $purchase
 * @property int|null $department
 * @property string|null $repair ประวัติการซ่อม
 * @property string|null $owner
 * @property int|null $life อายุการใช้งาน
 * @property string|null $device_items อุปกรณ์ภายใน
 * @property int|null $on_year
 * @property int|null $dep_id ประจำอยู่หน่วยงาน
 * @property int|null $depre_type ประเภทค่าเสื่อมราคา
 * @property int|null $budget_year งบประมาณ
 * @property string|null $asset_status สถานะทรัพย์สิน
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categorise';
    }

    public $q_category;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data_json', 'q_category', 'unit_items'], 'safe'],
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

    public function getProductType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'product_type']);
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
                                    <p class="text-primary mb-0 fs-13">'.$this->productType->title.' <code>('.(isset($this->data_json['unit']) ? $this->data_json['unit'] : '-').')</code></p>
                                </div>
                            </div>';
    }
                            public function AvatarXl(){
                                return '<div class="d-flex">
                                '.Html::img($this->ShowImg(),['class' => 'avatar']).'
                                                        <div class="avatar-detail">
                                                            <h5 class="mb-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                                                '.$this->title.'
                                                            </h5>
                                                            <p class="text-primary mb-0 fs-6">'.$this->productType->title.' <code>('.(isset($this->data_json['unit']) ? $this->data_json['unit'] : '-').')</code></p>
                                                        </div>
                                                    </div>';
    }
    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'product_type'])->all(), 'code', 'title');
    }

    public function ListUnit()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'unit'])->all(), 'title', 'title');
    }

    public function ListProductUnit()
    {
        return Categorise::find()->where(['category_id' => $this->id, 'name' => 'product_unit'])->all();
    }
}