<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadCsvForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $csvFile;

    public function rules()
    {
        return [
            // [['csvFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
            [['csvFile'], 'file', 'skipOnEmpty' => false],
        ];
    }
}
