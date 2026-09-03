<?php

namespace app\modules\finance\models;

use yii\base\Model;

/**
 * ฟอร์มเลือกไฟล์ทะเบียนเงินยืมเพื่อนำเข้า
 *
 * ปีงบประมาณกับชื่อแท็บแยกจากกัน เพราะไฟล์ที่ใช้จริงตั้งชื่อแท็บว่า "2569"
 * แต่โรงพยาบาลอื่นอาจตั้งชื่อต่างออกไป และบางครั้งต้องนำเข้าข้อมูลปีเก่าจากไฟล์เดียวกัน
 */
class FinanceLoanImportForm extends Model
{
    public $file;
    public $fiscal_year;
    public $sheet;

    public function init()
    {
        parent::init();
        if ($this->fiscal_year === null || $this->fiscal_year === '') {
            $this->fiscal_year = FinanceLoan::currentFiscalYear();
        }
    }

    public function rules()
    {
        return [
            [['file', 'fiscal_year'], 'required'],
            [['fiscal_year'], 'integer', 'min' => 2500, 'max' => 2700],
            [['sheet'], 'trim'],
            [['sheet'], 'string', 'max' => 100],
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
            'file' => 'ไฟล์ทะเบียนเงินยืม',
            'fiscal_year' => 'ปีงบประมาณ',
            'sheet' => 'ชื่อแท็บในไฟล์',
        ];
    }
}
