<?php

namespace app\modules\am\models;

use Yii;
use app\modules\filemanager\components\FileManagerHelper;
use yii\helpers\ArrayHelper;
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
    public $ma;
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
            [['data_json', 'updated_at', 'created_at','date_start','date_end','ma'], 'safe'],
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

    public function Upload($ref, $name)
    {
        return FileManagerHelper::FileUpload($ref, $name);
    }

    public function beforeSave($insert)
    {

        try {
            $items = [
                "items" => $this->ma
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $items);
        } catch (\Throwable $th) {
            //throw $th;
        }    
        return parent::beforeSave($insert);
    }
}
