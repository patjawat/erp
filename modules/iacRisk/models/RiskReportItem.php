<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class RiskReportItem extends ActiveRecord{public static function tableName(): string{return '{{%iac_risk_report_item}}';}public function getReport(){return $this->hasOne(RiskReport::class,['id'=>'risk_report_id']);}}
