<?php

namespace app\modules\helpdesk\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\am\models\Asset;

/**
 * This is the model class for table "helpdesk".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $name ชื่อการเก็บข้อมูล
 * @property string|null $title รายการ
 * @property string|null $data_json การเก็บข้อมูลชนิด JSON
 * @property string|null $ma_items การบำรุงรักษา
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Helpdesk extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

     public $asset_name;
     public $asset_type_name;
    public static function tableName()
    {
        return 'helpdesk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end', 'data_json', 'ma_items', 'created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'code', 'name', 'title'], 'string', 'max' => 255],
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
            'code' => 'Code',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'name' => 'Name',
            'title' => 'Title',
            'data_json' => 'Data Json',
            'ma_items' => 'Ma Items',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
{
    return [
        [
            'class' => TimestampBehavior::className(),
            'createdAtAttribute' => 'created_at',
            'updatedAtAttribute' => 'updated_at',
            'value' => new Expression('NOW()'),
            
        ],
        [
            'class' => BlameableBehavior::className(),
            'createdByAttribute' => 'created_by',
            'updatedByAttribute' => 'updated_by',
        ],
    ];
}

public function Upload($name)
{
    return FileManagerHelper::FileUpload($this->ref, $name);
}

public function afterFind()
{
    try {
        $this->asset_name = isset($this->asset->data_json['asset_name']) ? $this->asset->data_json['asset_name'] : null;
        $this->asset_type_name = isset($this->asset->data_json['asset_type_text']) ? $this->asset->data_json['asset_type_text'] : null;
    } catch (\Throwable $th) {

    }

    // $this->asset_name = isset($this->data_json['name']) ? $this->data_json['name'] : '-';

    parent::afterFind();
}


// relation
    //Relationships
    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['code' => 'code']);
    }

}
