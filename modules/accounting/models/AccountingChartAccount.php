<?php

namespace app\modules\accounting\models;

use Yii;
use yii\db\ActiveRecord;

class AccountingChartAccount extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%accounting_chart_account}}';
    }

    public function rules()
    {
        return [
            [['version_id', 'code', 'name', 'category'], 'required'],
            [['version_id', 'created_by', 'updated_by'], 'integer'],
            [['is_active'], 'boolean'],
            [['code'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 500],
            [['category'], 'in', 'range' => ['1', '2', '3', '4', '5']],
            [['code'], 'unique', 'targetAttribute' => ['version_id', 'code']],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if ($insert) {
            $this->ref = $this->ref ?: substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getVersion()
    {
        return $this->hasOne(AccountingChartVersion::class, ['id' => 'version_id']);
    }
}
