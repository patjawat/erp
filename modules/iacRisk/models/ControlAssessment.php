<?php
namespace app\modules\iacRisk\models;
use yii\db\ActiveRecord;
class ControlAssessment extends ActiveRecord { public static function tableName(): string { return '{{%iac_control_assessment}}'; } }
