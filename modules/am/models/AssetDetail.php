<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\usermanager\models\User;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "asset_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property int|null $user_id
 * @property int|null $emp_id
 * @property string|null $name
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class AssetDetail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $ma; // การบำรุงรักษา
    public $accessories_item; //ครุภัณฑ์ภายใน
    public static function tableName()
    {
        return 'asset_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'emp_id', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'updated_at', 'created_at','date_start','date_end','ma','accessories_item'], 'safe'],
            [['ref', 'code', 'name'], 'string', 'max' => 255],
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
            'user_id' => 'User ID',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }


        public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

       public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }



    public function Upload()
    {   $ref = $this->ref;
        $name = $this->name;
        return FileManagerHelper::FileUpload($ref, $name);
    }
    public function listImages()
    {   $ref = $this->ref;
        return FileManagerHelper::listViewImages($ref);
    }

public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // 1. แปลงวันที่หลัก
            if ($this->date_start && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->date_start)) {
                $this->date_start = AppHelper::DateToDb($this->date_start);
            }
            if ($this->date_end && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->date_end)) {
                $this->date_end = AppHelper::DateToDb($this->date_end);
            }

            // 2. จัดการ data_json (ป้องกันข้อมูลเดิมหาย)
            if (is_array($this->data_json)) {
                $data = $this->data_json;
                foreach ($data as $key => $value) {
                    // ตรวจสอบว่ามีรูปแบบ 00/00/0000 และเป็นฟิลด์วันที่
                    if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                        $data[$key] = AppHelper::DateToDb($value);
                    }
                }
                $this->data_json = $data;
            }
            return true;
        }
        return false;
    }

      //Relationships
      public function getAsset()
      {
          return $this->hasOne(Asset::class, ['code' => 'code']);
      }

              public function getAssetItem()
              {
                  return $this->hasOne(Categorise::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
              }
}
