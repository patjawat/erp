<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class RiskControl extends ActiveRecord { public static function tableName(): string { return '{{%iac_risk_control}}'; } }
