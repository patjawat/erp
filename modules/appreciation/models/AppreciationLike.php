<?php

namespace app\modules\appreciation\models;

use app\modules\hr\models\Employees;
use Yii;

/**
 * @property int $id
 * @property int $appreciation_id
 * @property int $emp_id
 * @property string $created_at
 */
class AppreciationLike extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%appreciation_like}}';
    }

    public function rules()
    {
        return [
            [['appreciation_id', 'emp_id'], 'required'],
            [['appreciation_id', 'emp_id'], 'integer'],
            [['created_at'], 'safe'],
            [['appreciation_id', 'emp_id'], 'unique', 'targetAttribute' => ['appreciation_id', 'emp_id']],
            [['appreciation_id'], 'exist', 'targetClass' => Appreciation::class, 'targetAttribute' => 'id'],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getAppreciation()
    {
        return $this->hasOne(Appreciation::class, ['id' => 'appreciation_id']);
    }

    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert) && $insert && empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }
}
