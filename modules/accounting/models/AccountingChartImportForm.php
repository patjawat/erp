<?php

namespace app\modules\accounting\models;

use yii\base\Model;

class AccountingChartImportForm extends Model
{
    public $file;
    public $fiscal_year;
    public $version_code = 'V1';
    public $title;
    public $sheet;
    public $scope = AccountingChartVersion::SCOPE_STANDARD;

    public function init()
    {
        parent::init();
        $this->fiscal_year = $this->fiscal_year ?: 2569;
        $this->title = $this->title ?: 'ผังบัญชีมาตรฐาน ปี ' . $this->fiscal_year;
    }

    public function rules()
    {
        return [
            [['file', 'fiscal_year', 'version_code', 'title', 'scope'], 'required'],
            [['fiscal_year'], 'integer', 'min' => 2500, 'max' => 2700],
            [['version_code', 'title', 'sheet'], 'trim'],
            [['version_code'], 'match', 'pattern' => '/^[A-Za-z0-9._-]+$/', 'message' => 'ใช้ได้เฉพาะตัวอักษรอังกฤษ ตัวเลข จุด ขีดกลาง และขีดล่าง'],
            [['version_code'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 255],
            [['sheet'], 'string', 'max' => 100],
            [['scope'], 'in', 'range' => array_keys(AccountingChartVersion::scopeOptions())],
            [['file'], 'file', 'extensions' => ['xlsx', 'xls'], 'mimeTypes' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'application/zip',
            ], 'maxSize' => 10 * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'ไฟล์ผังบัญชี',
            'fiscal_year' => 'ปีงบประมาณ',
            'version_code' => 'รหัสเวอร์ชัน',
            'title' => 'ชื่อผังบัญชี',
            'sheet' => 'ชื่อแท็บ',
            'scope' => 'ประเภทผังบัญชี',
        ];
    }
}
