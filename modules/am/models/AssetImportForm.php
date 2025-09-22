<?php
namespace app\modules\am\models;

use Yii;

class AssetImportForm extends \yii\base\Model
{
    public $asset_type_id;
    public $asset_category_id;
    public $csvFile;

    public function rules()
    {
        return [
            [['asset_type_id'], 'required'],
            [['csvFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
        ];
    }

      public function attributeLabels()
    {
        return [
            'asset_type_id' => 'ประเภท',
        ];
    }
}

