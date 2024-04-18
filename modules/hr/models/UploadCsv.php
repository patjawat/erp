<?php

namespace app\modules\hr\models;

use yii\base\Model;


class UploadCsv extends Model
{
    public $file;
    public $file_name;
    public $content;
    public $name;
    public $ref;

    public function rules()
    {
        return [
            [['file'], 'required'],
            [['file'], 'file'],
        ];
    }

    
}
