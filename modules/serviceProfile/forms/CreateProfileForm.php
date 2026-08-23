<?php

namespace app\modules\serviceProfile\forms;

use yii\base\Model;

class CreateProfileForm extends Model
{
    public $owner_id;
    public $fiscal_year;
    public $author_ids = [];
    public $coordinator_id;
    public $copy_latest = 1;

    public function rules()
    {
        return [
            [['owner_id', 'fiscal_year', 'coordinator_id'], 'required'],
            [['owner_id', 'fiscal_year', 'coordinator_id'], 'integer'],
            [['author_ids'], 'each', 'rule' => ['integer']],
            [['copy_latest'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return ['owner_id' => 'หน่วยงาน / ทีมประสาน', 'fiscal_year' => 'ปีงบประมาณ', 'author_ids' => 'รายชื่อคณะทำงาน', 'coordinator_id' => 'ผู้ประสานหลัก', 'copy_latest' => 'คัดลอกข้อมูลจากฉบับปัจจุบัน'];
    }
}
