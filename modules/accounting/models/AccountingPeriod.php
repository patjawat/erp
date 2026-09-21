<?php
namespace app\modules\accounting\models;

use yii\db\ActiveRecord;

class AccountingPeriod extends ActiveRecord
{
    public const TYPE_MONTH = 'month';
    public const STATUS_OPEN = 'open';
    public const STATUS_LOCKED = 'locked';

    public static function tableName(){return '{{%accounting_periods}}';}
    public function rules(){return [[['fiscal_year','period_no','period_type','start_date','end_date','status'],'required'],[['fiscal_year','period_no','closed_by'],'integer'],[['start_date','end_date'],'date','format'=>'php:Y-m-d'],[['period_type','status'],'string','max'=>20],[['name'],'string','max'=>100]];}
}
